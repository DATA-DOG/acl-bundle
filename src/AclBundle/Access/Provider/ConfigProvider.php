<?php

namespace AclBundle\Access\Provider;

use AclBundle\Access\PolicyProviderInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ConfigProvider implements PolicyProviderInterface
{
    private $context;
    private $policies;

    public function __construct(SecurityContextInterface $context, array $policies = [])
    {
        $this->context = $context;
        $this->policies = $policies;
    }

    public function policies()
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

        if (!array_key_exists($user->getUsername(), $this->policies)) {
            return [];
        }

        return $this->policies[$user->getUsername()];
    }
}
