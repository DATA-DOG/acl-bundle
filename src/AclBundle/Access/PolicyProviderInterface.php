<?php

namespace AclBundle\Access;

interface PolicyProviderInterface
{
    const ALLOW = true;
    const DENY = false;

    /**
     * Return resource access policies
     * Key is a resource, value is boolean denied or allowed
     *
     * @return array - ['resource.action' => true]
     */
    function policies();
}
