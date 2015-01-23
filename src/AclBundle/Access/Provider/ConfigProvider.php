<?php

namespace AclBundle\Access\Provider;

use AclBundle\Resource\ProviderInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ConfigProvider implements ProviderInterface
{
    private $context;
    private $accesses;

    public function __construct(SecurityContextInterface $context, array $accesses = [])
    {
        $this->context = $context;
        $this->accesses = $accesses;
    }

    public function resources()
    {
        $resources = [];
        if (!$token = $this->context->getToken()) {
            return [];
        }

        if (!$token instanceof TokenInterface) {
            return [];
        }

        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return [];
        }

        if (!array_key_exists($user->getUsername(), $this->accesses)) {
            return [];
        }

        return $this->accesses[$user->getUsername()];
    }
}
