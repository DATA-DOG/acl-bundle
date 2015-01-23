<?php

namespace AclBundle\Resource\Provider;

use AclBundle\Resource\ProviderInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class BundleConfigurationProvider implements ProviderInterface
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function resources()
    {
        $dirs = [];
        foreach ($this->kernel->getBundles() as $bundle) {
            $dirs[] = $bundle->getPath() . '/Resources/config';
        }
        $locator = new FileLocator($dirs);
        $files = $locator->locate('acl.yml', null, false);

        $resources = [];
        foreach ($files as $file) {
            $config = Yaml::parse(file_get_contents($file));
            if (!array_key_exists('resources', $config)) {
                throw new \RuntimeException("The acl file: {$file} is not valid acl resource file.");
            }
            $resources = array_merge($config['resources']);
        }
        return $resources;
    }
}
