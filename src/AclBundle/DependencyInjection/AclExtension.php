<?php

namespace AclBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AclExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('resource_providers.yml');

        // add tags to resource providers
        foreach ($config['resource_providers'] as $name => $enabled) {
            $enabled && $container->getDefinition('acl.resource.provider.'.$name)->addTag('acl.resource.provider');
        }

        // resource builder
        $rb = new Definition($container->getParameter('acl.resource.builder.class'));
        // options
        $rb->addArgument([
            $config['default_allowed'], // if allowed to any resource by default
            $this->getAlias().ucfirst($container->getParameter('kernel.environment')), // cache prefix
            $container->getParameter('kernel.debug'), // debug
        ]);
        $container->setDefinition('acl.resource.builder', $rb);
    }
}
