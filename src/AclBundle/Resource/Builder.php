<?php

namespace AclBundle\Resource;

use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

class Builder implements WarmableInterface
{
    private $resources = [];
    private $providers = [];

    private $defaultAllowed = false;
    private $cacheDir;
    private $cachePrefix;

    public function __construct(array $options)
    {
        list($this->defaultAllowed, $this->cachePrefix) = $options;
    }

    public function registerProvider(ProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->cacheDir = $cacheDir;
        $all = $this->getResources();
    }

    public function getResources()
    {
        return $this->resources;
    }

    protected function cacheClass()
    {
        $parts = explode('\\', get_called_class());
        return $this->cachePrefix . end($parts);
    }
}
