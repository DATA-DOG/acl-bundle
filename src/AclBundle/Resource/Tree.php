<?php

namespace AclBundle\Resource;

abstract class Tree
{
    protected $resources = [];

    public function all()
    {
        return $this->resources;
    }

    public function policy($resource, $allow)
    {
        $head = &$this->resources;
        $points = explode('.', $resource); // access points
        while ($point = array_shift($points)) {
            if (!array_key_exists($point, $head)) {
                throw new \RuntimeException("The policy \"{$resource}\" cannot be mapped, since this resource was not registered before, check resource providers.");
            }

            // move down the head
            $head = &$head[$point];

            // if last access point - means map to all remaining access points
            if (!count($points)) {
                $this->mapRemainingAccessPoints($head, $allow);
                continue;
            }
        }
        return $this;
    }

    private function mapRemainingAccessPoints(&$points, $allow)
    {
        if (is_array($points)) {
            foreach ($points as &$point) {
                $this->mapRemainingAccessPoints($point, $allow);
            }
        } else {
            $points = $allow;
        }
    }
}
