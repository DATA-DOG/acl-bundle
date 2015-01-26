<?php

namespace AclBundle\Exception;

class ResourceNotFoundException extends \RuntimeException
{
    public function __construct($resource)
    {
        $this->message = "The resource \"{$resource}\" is not available in ACL tree";
    }
}
