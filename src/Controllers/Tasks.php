<?php

namespace Controllers;

use Coquelux\Response;
use Coquelux\Request;

class Tasks
{
    const STAGING_TAG_ID = 38379326236997;
    const PRODUCTION_TAG_ID = 38379326236999;

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
                    $this->asana->addTagToTask((int) $message[2], $tagId);
                    $this->commitHistory->setCommit($message[2]);
                }
            }
        }
        return new Response();
    }
}
