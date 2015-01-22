<?php

namespace AclBundle\Resource;

abstract class Tree
{
    protected $resources = [];

    public function all()
    {
        return $this->resources;
    }
}
