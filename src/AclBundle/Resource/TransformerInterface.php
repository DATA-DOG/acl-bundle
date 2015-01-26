<?php

namespace AclBundle\Resource;

interface TransformerInterface
{
    /**
     * Transforms object into a resource
     *
     * @param object $object
     * @return string
     */
    function transform($object);

    /**
     * Checks whether it can transform an $object
     *
     * @param object $object
     * @return bool
     */
    function supports($object);
}
