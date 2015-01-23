<?php

namespace AclBundle\Resource;

interface ProviderInterface
{
    /**
     * Get a list of available ACL resources
     *
     * @return array - ['resource.string.action', ...]
     */
    function resources();
}
