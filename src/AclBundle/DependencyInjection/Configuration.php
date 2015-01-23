<?php

namespace AclBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('acl');

        $rootNode
            ->children()
                ->booleanNode('default_allowed')->defaultFalse()->end()
                ->arrayNode('resource_providers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('bundle_configuration')->defaultTrue()->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}
