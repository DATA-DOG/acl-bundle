<?php

namespace AclBundle\Access;

use AclBundle\Resource\Builder as ResourceBuilder;
use AclBundle\Resource\Transformer\Transformator;
use AclBundle\Exception;

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

    private $trans;

    public function __construct(ResourceBuilder $builder, Transformator $trans)
    {
        $this->resourceBuilder = $builder;
        $this->trans = $trans;
    }

    public function provider(PolicyProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    public function isGranted($action, $resource)
    {
        $actions = $this->actions($resource);
        if (!array_key_exists($action, $actions)) {
            $name = is_object($resource) ? get_class($resource) : $resource;
            throw new \RuntimeException("The resource \"{$name}\" has no action \"{$action}\" registered.");
        }
        return $actions[$action];
    }

    public function tree()
    {
        if (null != $this->resourceTree) {
            return $this->resourceTree;
        }

        return $this->resourceTree = $this->buildTree();
    }

    public function buildTree()
    {
        $resourceTree = clone $this->resourceBuilder->tree();

        // map all policies
        foreach ($this->providers as $provider) {
            foreach ($provider->policies() as $resource => $policy) {
                $resourceTree->policy($resource, $policy);
            }
        }

        return $resourceTree;
    }

    public function actions($resource)
    {
        if (is_object($resource)) {
            $points = explode('.', $this->trans->transform($resource));
        } elseif (is_string($resource)) {
            $points = explode('.', $resource);
        } else {
            throw new \InvalidArgumentException("Expected resource type to be object or string");
        }

        $result = $this->tree()->all();
        $source = implode('.', $points);
        while ($point = array_shift($points)) {
            if (!array_key_exists($point, $result)) {
                throw new Exception\ResourceNotFoundException($source);
            }
            $result = $result[$point];
        }
        return $result;
    }
}
