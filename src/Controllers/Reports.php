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

    public function __construct($asana, $github, $twig, $ses, $task)
    {
        $this->task = $task;
        $this->asana = $asana;
        $this->twig = $twig;
        $this->ses = $ses;
        $this->github = $github;
    }

    public function get()
    {

        $tasks = $this->assana->getTasks(Config::get()->asana->projectId);
        $users = $this->assana->getAllUsers();

        $data = [];

        $today = new \DateTime('now');
        $today = $today->format('Y-m-d');
        $data['today'][$today] = [];

        $data = ['current' => [], 'latest' => []];
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

            $task = ['task' => $t['name'], 'assignee' => $user, 'due_date' => $dueDate, 'id' => $t['id'] ];
            $this->task->save($task);
        }

        $tasks = $this->task->getUnsent();

        foreach ($tasks as $t) {
            $this->task->markAsSent($t);
        }
        return json_encode([]);

        ksort($data['latest']);
        $latest = array_keys($data['latest']);
        $above = array_pop($latest);



        if (empty($data['latest'])) {
            $data['current'] = $data['latest'];
            $data['latest'] = [];
        }
        $body = $this->twig->render(
            'changelog.twig', [
                'current' => $data['current'][$today], 
                'latest' => $data['latest'][$above], 
                'today' => $today, 
                'above' => $above
            ]
        );
        return json_encode($body);

        $msg = [];
        $msg['Source'] = "changelog@ventas-privadas.com";
        $msg['Destination']['ToAddresses'][] = "changelog@ventas-privadas.com";

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
