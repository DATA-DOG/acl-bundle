<?php

namespace AclBundle\Resource;

class DefaultTree extends Tree
{
    public function __construct(array $resources)
    {
        $this->resources = $resources;
    }
}
