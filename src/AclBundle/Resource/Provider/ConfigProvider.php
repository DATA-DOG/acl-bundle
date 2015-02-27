<?php

namespace AclBundle\Resource\Provider;

use AclBundle\Resource\ProviderInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigProvider implements ProviderInterface
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function resources()
    {
        // look in kernel directory
        $try = [$this->kernel->getRootDir() . '/Resources/config/acl_resources.yml'];
        // look in all bundles
        foreach ($this->kernel->getBundles() as $bundle) {
            $try[] = $bundle->getPath() . '/Resources/config/acl_resources.yml';
        }

        $resources = [];
        foreach ($try as $file) {
            if (!file_exists($file)) {
                continue;
            }

            $config = Yaml::parse(file_get_contents($file));

            if (!array_key_exists('resources', (array)$config)) {
                throw new \RuntimeException("The acl file: {$file} is not valid acl resource file.");
            }

            $resources = array_merge($resources, (array)$config['resources']);
        }

        return $resources;
    }
}
