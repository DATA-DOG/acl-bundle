<?php

namespace AclBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AclProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $c)
    {
        $builder = $c->getDefinition('acl.resource.builder');
        foreach ($c->findTaggedServiceIds('acl.resource.provider') as $id => $attributes) {
            $builder->addMethodCall('registerProvider', [new Reference($id)]);
        }
    }
}
