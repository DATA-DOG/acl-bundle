<?php

namespace AclBundle\Resource;

abstract class Tree
{
    protected $resources = [];
    protected $defaultAllowed;

    public function all()
    {
        return $this->resources;
    }

    public function map(array $accesses)
    {
        foreach ($accesses as $resource) {
            $head = &$this->resources;
            $points = explode('.', $resource); // access points
            while ($point = array_shift($points)) {
                if (!array_key_exists($point, $head)) {
                    throw new \RuntimeException("The access to resource \"{$resource}\" cannot be mapped, since this resource was not registered.");
                }

                // if last access point - means map to all remaining access points
                if (!count($points)) {
                    $this->mapRemainingAccessPoints($head);
                    continue;
                }

                // move down the head
                $head = &$head[$point];
            }
        }
        return $this;
    }

    private function mapRemainingAccessPoints(&$points)
    {
        if (is_array($points)) {
            foreach ($points as &$point) {
                $this->mapRemainingAccessPoints($point);
            }
        } else {
            $points = !$this->defaultAllowed;
        }
    }
}
