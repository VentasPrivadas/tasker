<?php

namespace Models\Hydrators;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Hydrator\HydratorInterface;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ODM. DO NOT EDIT THIS FILE.
 */
class ModelsDocumentsTasksHydrator implements HydratorInterface
{
    private $dm;
    private $unitOfWork;
    private $class;

    public function __construct(DocumentManager $dm, UnitOfWork $uow, ClassMetadata $class)
    {
        $this->dm = $dm;
        $this->unitOfWork = $uow;
        $this->class = $class;
    }

    public function hydrate($document, $data, array $hints = array())
    {
        $hydratedData = array();

        /** @Field(type="id") */
        if (isset($data['_id'])) {
            $value = $data['_id'];
            $return = $value instanceof \MongoId ? (string) $value : $value;
            $this->class->reflFields['id']->setValue($document, $return);
            $hydratedData['id'] = $return;
        }

        /** @Field(type="date") */
        if (isset($data['dueDate'])) {
            $value = $data['dueDate'];
            if ($value instanceof \MongoDate) { $return = new \DateTime(); $return->setTimestamp($value->sec); } elseif (is_numeric($value)) { $return = new \DateTime(); $return->setTimestamp($value); } elseif ($value instanceof \DateTime) { $return = $value; } else { $return = new \DateTime($value); }
            $this->class->reflFields['dueDate']->setValue($document, clone $return);
            $hydratedData['dueDate'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['assanaId'])) {
            $value = $data['assanaId'];
            $return = (string) $value;
            $this->class->reflFields['assanaId']->setValue($document, $return);
            $hydratedData['assanaId'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['assignee'])) {
            $value = $data['assignee'];
            $return = (string) $value;
            $this->class->reflFields['assignee']->setValue($document, $return);
            $hydratedData['assignee'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['task'])) {
            $value = $data['task'];
            $return = (string) $value;
            $this->class->reflFields['task']->setValue($document, $return);
            $hydratedData['task'] = $return;
        }

        /** @Field(type="boolean") */
        if (isset($data['completed'])) {
            $value = $data['completed'];
            $return = (bool) $value;
            $this->class->reflFields['completed']->setValue($document, $return);
            $hydratedData['completed'] = $return;
        }

        /** @Field(type="boolean") */
        if (isset($data['sent'])) {
            $value = $data['sent'];
            $return = (bool) $value;
            $this->class->reflFields['sent']->setValue($document, $return);
            $hydratedData['sent'] = $return;
        }
        return $hydratedData;
    }
}