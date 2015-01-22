<?php

namespace AclBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use AclBundle\Resource\Builder;

class AclResourceCacheWarmer extends CacheWarmer
{
    private $builder;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    public function warmUp($cacheDir)
    {
        $this->builder instanceof WarmableInterface and $this->builder->warmUp($cacheDir);
    }

    public function isOptional()
    {
        return true;
    }
}
