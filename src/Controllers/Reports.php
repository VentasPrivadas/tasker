<?php

namespace Controllers;

use Coquelux\Config;
use Coquelux\Response;
use Coquelux\Request;

class Reports 
{
    const STAGING_TAG_ID = 38379326236997;
    const PRODUCTION_TAG_ID = 38379326236999;

    private $asana;
    private $http;
    private $twig;
    private $ses;

    public function __construct($asana, $github, $twig, $ses)
    {
        $this->asana = $asana;
        $this->twig = $twig;
        $this->ses = $ses;
        $this->github = $github;
    }

    public function get()
    {

        $options = ['due_on' => 'today','completed' => 'true', 'opt_fields'=> 'name, assignee, archived, created_at, assignee_status, completed, due_on'];
        $result = json_decode($this->asana->getProjectTasks(
            Config::get()->asana->projectId, $options), true
        );

        $usersFromAsana = json_decode($this->asana->getUsers(), true);
        $users = [];
        foreach ($usersFromAsana['data'] as $u) {
            $users[$u['id']] = $u['name'];
        }

        $data = [];

        $today = new \DateTime('now');
        $today = $today->format('Y-m-d');

        $data = ['current' => [], 'last' => []];
        foreach ($result['data'] as $t) {
            $dueDate = new \DateTime($t['due_on']);
            $dueDate = $dueDate->format('Y-m-d');
            if (empty($t['due_on'])) {
                continue;
            }
            $list = 'latest';
            if ($dueDate == $today) {
                $list = 'current';
            }
            $user = isset($users[$t['assignee']['id']]) 
                ? $users[$t['assignee']['id']] 
                : "Not Assigned";

            $data[$list][$dueDate][] = ['task' => $t['name'], 'assignee' => $user, 'due_date' => $dueDate ];
        }

        ksort($data['latest']);
        $latest = array_keys($data['latest']);
        $above = array_pop($latest);

        $body = $this->twig->render(
            'changelog.twig', [
                'current' => $data['current'][$today], 
                'latest' => $data['latest'][$above], 
                'today' => $today, 
                'above' => $above
            ]
        );

return $body;
        $msg = [];
        $msg['Source'] = "pablo@ventas-privadas.com";
        $msg['Destination']['ToAddresses'][] = "pablo@ventas-privadas.com";

        $msg['Message']['Subject']['Data'] = "Release $today :: Changelog";
        $msg['Message']['Subject']['Charset'] = "UTF-8";

        $msg['Message']['Body']['Text']['Data'] = $body;
        $msg['Message']['Body']['Text']['Charset'] = "UTF-8";

        $msg['Message']['Body']['Html']['Data'] = $body;
        $msg['Message']['Body']['Html']['Charset'] = "UTF-8";

        try{
            $result = $this->ses->sendEmail($msg);
            return json_encode($result);
        } catch (Exception $e) {
            return json_decode($e->getMessage());
        }
    }
}