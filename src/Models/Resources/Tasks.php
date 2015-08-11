<?php

namespace Models\Resources;

use Coquelux\PHP;
use Models;
use MongoDate;

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
        }
        
        $this->persist($task, $params);
    }

    public function markAsSent($task)
    {
        $task = $this->get($task->getAssanaId());
        $task->setSent(true); 
        $this->conn->persist($task);
        $this->conn->flush();
    }

    private function persist($task, $params)
    {
        $task->setAssanaId($params['id']);
        $task->setTask($params['name']);
        $task->setAssignee($params['user']);
        $task->setDueDate($params['dueDate']);
        $task->setCompleted($params['completed'] == 1);
        $task->setSent(false); 
        $this->conn->persist($task);
        $this->conn->flush();
        return $task;
    }

    public function getUnsent()
    {
        $today = new MongoDate();
        $first = new MongoDate(\strtotime('2015-08-01 00:00:00'));
        $query = $this->conn->createQueryBuilder('Models\Documents\Tasks')
            ->field('sent')->equals(false)
            ->field('dueDate')->lte($today)
            ->field('dueDate')->gte($first)
            ->field('completed')->equals(true)
            ->sort('dueDate', 'asc');
        return $query->getQuery()->execute();
    }

    public function get($id)
    {
        settype($id, 'string');
        $query = $this->conn
            ->getRepository('Models\Documents\Tasks')
            ->findOneBy(['assanaId' => $id]);
        return $query;
    }
}
