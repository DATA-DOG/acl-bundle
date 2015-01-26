<?php

namespace AclBundle\Resource\Provider\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class ACL
{
    public $resource;
}
