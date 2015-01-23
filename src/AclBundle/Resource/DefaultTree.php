<?php

namespace AclBundle\Resource;

class DefaultTree extends Tree
{
    public function __construct(array $resources, $defaultAllowed)
    {
        $this->resources = $resources;
        $this->defaultAllowed = $defaultAllowed;
    }
}
