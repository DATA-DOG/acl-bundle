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
        $loader->load('resource_transformers.yml');
        $loader->load('access_providers.yml');

        // add tags to resource providers
        foreach ($config['resource']['providers'] as $name => $enabled) {
            if (!$enabled) {
                continue;
            }

            switch ($name) {
            case 'annotation':
                $loader->load('annotations.yml');
                break;
            }
            $container->getDefinition('acl.resource.provider.'.$name)->addTag('acl.resource.provider');
        }

        // resource transformers, remove ones which are disabled
        foreach ($config['resource']['transformers'] as $name => $enabled) {
            if (!$enabled) {
                $container->removeDefinition('acl.resource.transformer.'.$name);
            }
        }

        // access resource providers
        foreach ($config['access']['providers'] as $name => $enabled) {
            $enabled && $container->getDefinition('acl.access.provider.'.$name)->addTag('acl.access.provider');
        }

        if (count($config['access']['policies'])) {
            // if some username related resources are configured in bundle config, register it in provider
            $container->getDefinition('acl.access.provider.config')
                ->addTag('acl.access.provider')
                ->addArgument($config['access']['policies']);
        }

        // resource builder
        $rb = new Definition($container->getParameter('acl.resource.builder.class'));
        // options
        $rb->addArgument([
            $config['default_allowed'], // if allowed to any resource by default
            $this->getAlias().ucfirst($container->getParameter('kernel.environment')) . 'Resources', // cache prefix
            $container->getParameter('kernel.cache_dir'),
            $container->getParameter('kernel.debug'), // debug
        ]);
        $container->setDefinition('acl.resource.builder', $rb);
    }
}
