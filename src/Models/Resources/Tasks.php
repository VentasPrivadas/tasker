<?php

namespace Models\Resources;

use Coquelux\PHP;
use Models;

class Tasks
{

    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function save($params)
    {
        $task = $this->get($params['id']);
        if ( ! $task) {
            $task = new Models\Documents\Tasks();
            $this->persist($task, $params);
        }
    }

    public function markAsSent($task)
    {
        $task = $this->get($task['id']);
        if ( ! $task) {
            echo "NO LO ENCUENTRA";
            var_dump($task);
            exit;
        }
        error_log('Trying ... ' . $task->getAssanaId());
        $task->setSent(); 
        $this->conn->persist($task);
        $this->conn->flush();
    }

    private function persist($task, $params)
    {
        $task->setAssanaId($params['id']);
        $task->setTask($params['task']);
        $task->setAssignee($params['assignee']);
        $task->setDueDate($params['due_date']);
        
        $this->conn->persist($task);
        $this->conn->flush();
        return $task;
    }

    public function getUnsent()
    {
        $query = $this->conn->createQueryBuilder('Models\Documents\Tasks')
            ->field('sent')->exists(false)
            ->sort('dueDate', 'asc');
        return $query->getQuery()->execute();
    }

    public function get($id)
    {
        $query = $this->conn
            ->getRepository('Models\Documents\Tasks')
            ->findOneBy(['assanaId' => $id]);
        return $query;
    }
}
