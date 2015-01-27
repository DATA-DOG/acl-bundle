<?php

namespace AclBundle\Profiler;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AclBundle\Access\DecisionManager;

class AclDataCollector extends DataCollector
{
    private $policyProviders;
    private $resourceProviders;
    private $security;

    public function __construct(DecisionManager $acl, SecurityContextInterface $security)
    {
        $this->security = $security;

        $prop = new \ReflectionProperty('AclBundle\Access\DecisionManager', 'providers');
        $prop->setAccessible(true);
        $this->policyProviders = $prop->getValue($acl);

        $prop = new \ReflectionProperty('AclBundle\Access\DecisionManager', 'resourceBuilder');
        $prop->setAccessible(true);
        $builder = $prop->getValue($acl);

        $prop = new \ReflectionProperty('AclBundle\Resource\Builder', 'providers');
        $prop->setAccessible(true);
        $this->resourceProviders = $prop->getValue($builder);
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $resources = [];
        foreach ($this->resourceProviders as $provider) {
            $resources = array_merge($resources, $provider->resources());
        }
        asort($resources);

        $policies = [];
        foreach ($this->policyProviders as $provider) {
            $policies = array_merge($policies, $provider->policies());
        }
        ksort($policies);

        $resources = array_flip($resources);
        foreach ($resources as $resource => &$policy) {
            $policy = ['granted' => false, 'policy' => null];
            foreach ($policies as $r => $granted) {
                if (strpos($resource, $r) === 0) {
                    $policy['granted'] = $granted;
                    $policy['policy'] = $r . ':' . ($granted ? 'grant' : 'deny');
                }
            }
        }

        $username = 'anon.';
        if ($token = $this->security->getToken()) {
            $username = $token->getUsername();
        }
        $this->data = compact('resources', 'policies', 'username');
    }

    public function getResources()
    {
        return $this->data['resources'];
    }

    public function getUsername()
    {
        return $this->data['username'];
    }

    public function getPolicies()
    {
        return $this->data['policies'];
    }

    public function getName()
    {
        return 'acl';
    }
}
