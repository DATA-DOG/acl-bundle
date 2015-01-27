<?php

namespace AclBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AclProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $c)
    {
        // tagged resource providers
        $builder = $c->getDefinition('acl.resource.builder');
        foreach ($c->findTaggedServiceIds('acl.resource.provider') as $id => $attributes) {
            $builder->addMethodCall('provider', [new Reference($id)]);
        }

        // tagged access resource providers
        $decisionManager = $c->getDefinition('acl.access.decision_manager');
        foreach ($c->findTaggedServiceIds('acl.policy.provider') as $id => $attributes) {
            $decisionManager->addMethodCall('provider', [new Reference($id)]);
        }

        // resource transformations
        $transformator = $c->getDefinition('acl.resource.transformator');
        $refs = [];
        foreach ($c->findTaggedServiceIds('acl.resource.transformer') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $refs[$id] = $priority;
        }
        arsort($refs); // inverse sort, lowest priority is last
        foreach ($refs as $id => $p) {
            $transformator->addArgument(new Reference($id));
        }
    }
}
