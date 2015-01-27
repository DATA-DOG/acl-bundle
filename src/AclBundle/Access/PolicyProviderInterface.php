<?php

namespace AclBundle\Access;

interface PolicyProviderInterface
{
    /**
     * Return resource access policies
     * Key is a resource, value is boolean denied or granted
     *
     * @return array - ['resource.action' => true]
     */
    function policies();
}
