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

    /** @ODM\Int */
    protected $assanaId;

    /** @ODM\String */
    protected $assignee;

    /** @ODM\String */
    protected $task;

    public function setSent()
    {
        $this->sent = true;
    }
}
