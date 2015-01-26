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
                ->arrayNode('resource')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('providers')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('config')->defaultTrue()->end()
                                ->booleanNode('annotation')->defaultTrue()->end()
                            ->end()
                        ->end()
                        ->arrayNode('transformers')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('doctrine')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('access')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('providers')
                            ->addDefaultsIfNotSet()
                            ->children()
                            ->end()
                        ->end()
                        ->arrayNode('policies')
                            ->useAttributeAsKey('username')
                            ->prototype('array')
                                ->prototype('array')
                                    ->children()
                                        ->booleanNode('allow')->defaultTrue()->end()
                                        ->scalarNode('resource')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
