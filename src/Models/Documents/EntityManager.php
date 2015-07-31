<?php

namespace Models\Documents;

use Doctrine\Common\Collections\ArrayCollection;

class EntityManager
{
    public function toArray()
    {
        $properties = get_object_vars($this);
        $params = new ArrayCollection($properties);
        return $params->toArray();
    }

    protected function getTimestamp($datetime)
    {
        $date = new \DateTime($datetime);
        return $date->getTimestamp();
    }

    public function sanitize()
    {
        foreach ($this->toArray() as $key => $val) {
            if (empty($val)) {
                unset($this->$key);
            }
            if (is_array($val) && ! count($val)) {
                unset($this->$key);
            }
        }
    }

    public function __call($name, $arguments)
    {
        $prefix = substr($name, 0, 3);
        if ($prefix !== 'set' && $prefix !== 'get') {
            throw new \BadMethodCallException("Undefined method " . $name);
        }
        $property = lcfirst(str_replace($prefix, "", $name));

        if ($prefix == 'set') {
            $this->$property = $arguments[0];
            return true;
        }

        return $this->$property;
    }

}
