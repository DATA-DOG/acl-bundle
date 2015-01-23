<?php

namespace AclBundle\Access;

use AclBundle\Resource\ProviderInterface;
use AclBundle\Resource\Builder as ResourceBuilder;

class DecisionManager
{
    private $providers = [];

    /**
     * ACL resource tree
     *
     * @var Tree
     */
    private $resourceTree;

    private $resourceBuilder;

    public function __construct(ResourceBuilder $builder)
    {
        $this->resourceBuilder = $builder;
    }

    public function provider(ProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    public function isAllowed($action, $resource)
    {
        $actions = $this->actions($resource);
        if (!array_key_exists($action, $actions)) {
            $name = is_object($resource) ? get_class($resource) : $resource;
            throw new \RuntimeException("The resource \"{$name}\" has no action \"{$action}\" registered.");
        }
        return $actions[$action];
    }

    protected function tree()
    {
        if (null != $this->resourceTree) {
            return $this->resourceTree;
        }

        // denied or allowed accesses
        $accesses = [];
        foreach ($this->providers as $provider) {
            $accesses = array_merge($accesses, $provider->resources());
        }
        $this->resourceBuilder->validate($accesses);

        // map accesses to a resource tree
        $this->resourceTree = $this->resourceBuilder->tree();
        $this->resourceTree->map($accesses);

        return $this->resourceTree;
    }

    protected function actions($resource)
    {
        if (is_object($resource)) {
            $points = array_map(function($name) {
                return strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $name));
            }, explode('\\', get_class($resource)));
        } elseif (is_string($resource)) {
            $points = explode('.', $resource);
        } else {
            throw new \InvalidArgumentException("Expected resource type to be object or string");
        }

        $result = $this->tree()->all();
        $source = implode('.', $points);
        while ($point = array_shift($points)) {
            if (!array_key_exists($point, $result)) {
                throw new \RuntimeException("The resource \"{$source}\" is not registered, cannot find any actions.");
            }
            $result = $result[$point];
        }
        return $result;
    }
}
