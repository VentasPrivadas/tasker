<?php

namespace Models;


class Assana
{

    private $assana;

    public function __construct($assana)
    {
        $this->assana = $assana;
    }

    public function getAllUsers()
    {
        $usersFromAsana = json_decode($this->assana->getUsers(), true);
        $users = [];
        foreach ($usersFromAsana['data'] as $u) {
            $users[$u['id']] = $u['name'];
        } 
        return $users;
    }

    public function getTasks($projectId)
    {
        $options = ['due_on' => 'today','completed' => 'true', 'opt_fields'=> 'name, assignee, archived, created_at, assignee_status, completed, due_on'];
        $result = json_decode($this->assana->getProjectTasks(
            $projectId, $options), true
        );

        return $result['data'];
    }

}
