<?php

namespace Controllers;

use Coquelux\Config;
use Coquelux\Response;
use Coquelux\Request;
use MongoDate;

class Reports 
{

    private $assana;
    private $twig;
    private $ses;
    private $task;
    private $github;

    public function __construct($assana, $github, $twig, $ses, $task)
    {
        $this->task = $task;
        $this->assana = $assana;
        $this->twig = $twig;
        $this->ses = $ses;
        $this->github = $github;
    }

    public function getTasks()
    {
        $users = $this->assana->getAllUsers();
        $taskAssana = $this->assana->getTasks(Config::get()->asana->projectId);

        foreach ($taskAssana as $t) {
            $t['dueDate'] = new MongoDate(\strtotime($t['due_on']));
            $t['user'] = isset($users[$t['assignee']['id']]) 
                ? $users[$t['assignee']['id']] 
                : "Not Assigned";

            $this->task->save($t);
        }

        return json_encode(['tasks' => count($taskAssana)]);
    }

    public function get()
    {
        $tasks = $this->task->getUnsent();

        $response = [];
        foreach ($tasks as $t) {
            $response[] = $t->toArray();
            $this->task->markAsSent($t);
        }

        if ( ! count($response)) {
            return json_encode(["error" => "Nothing to do"]);
        }

        $body = $this->twig->render(
            'changelog.twig', [
                'current' => $response, 
            ]
        );
        $today = new \DateTime('now');
        $today = $today->format('Y-m-d');

        $msg = [];
        $msg['Source'] = Config::get()->mail->source;
        $msg['Destination']['ToAddresses'][] = Config::get()->mail->destination;

        $msg['Message']['Subject']['Data'] = "Release $today :: Changelog";
        $msg['Message']['Subject']['Charset'] = "UTF-8";

        $msg['Message']['Body']['Text']['Data'] = $body;
        $msg['Message']['Body']['Text']['Charset'] = "UTF-8";

        $msg['Message']['Body']['Html']['Data'] = $body;
        $msg['Message']['Body']['Html']['Charset'] = "UTF-8";

        try{
            $result = $this->ses->sendEmail($msg);
            return json_encode($response);
        } catch (Exception $e) {
            return json_decode($e->getMessage());
        }
    }
}
