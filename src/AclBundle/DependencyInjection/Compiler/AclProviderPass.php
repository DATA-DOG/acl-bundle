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
        foreach ($c->findTaggedServiceIds('acl.access.provider') as $id => $attributes) {
            $builder->addMethodCall('provider', [new Reference($id)]);
        }
    }
}
