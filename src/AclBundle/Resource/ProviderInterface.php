<?php

namespace AclBundle\Resource;

interface ProviderInterface
{
    /**
     * Get a list of available ACL resources
     *
     * @return array - map ['resource.string' => ['edit', 'view']]
     */
    function resources();
}
