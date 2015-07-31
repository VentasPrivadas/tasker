<?php
namespace Models\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Tasks extends EntityManager
{
    /** @ODM\Id */
    protected $id;

    /** @ODM\Date */
    protected $dueDate;

    /** @ODM\String */
    protected $assanaId;

    /** @ODM\String */
    protected $assignee;

    /** @ODM\String */
    protected $task;

    /** @ODM\Boolean */
    protected $completed;

    /** @ODM\Boolean */
    protected $sent;
}
