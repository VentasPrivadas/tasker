<?php

namespace Controllers;

use Coquelux\Response;
use Coquelux\Request;

class Tasks
{

    private $asana;
    private $http;
    private $commitHistory;

    public function __construct($asana, $github, $commitHistory)
    {
        $this->asana = $asana;
        $this->commitHistory = $commitHistory;
        $this->github = $github;
    }

    public function deploy(Request $params)
    {
        $repository = $params->get('repository');
        $environment = $params->get('environment');

        $commits = $this->github->getCommits($repository);

        foreach ($commits as $c) {
            if (substr($c['commit']['message'], 0, 18) == 'Merge pull request') {
                $message = explode("#", $c['commit']['message']);
                
                if (isset($message[2]) && $this->commitHistory->shouldComment($repository, $environment, $message[2])) {
                    $this->asana->commentOnTask((int) $message[2], 'Deployed on ' . $environment);
                    $this->commitHistory->setCommit($message[2]);
                }
            }
        }
        return new Response();
    }

    public function get()
    {
       $options = ['due_on' => 'today','completed' => 'true', 'opt_fields'=> 'name, assignee, archived, created_at, assignee_status, completed, due_on'];
       $result = json_decode($this->asana->getProjectTasks(
           Config::get()->asana->projectId, $options), true
       );

       $released = [];
       foreach ($result['data'] as $t) {
           if (empty($t['due_on'])) {
               continue;
           }
           $released[$t['due_on']][] = $t['name'];
       }

       ksort($released);
       return json_encode($released);
    }
}
