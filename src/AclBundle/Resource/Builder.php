<?php

namespace AclBundle\Resource;

use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Config\ConfigCache;
use AclBundle\Exception\InvalidResourceException;

class Builder implements WarmableInterface
{
    private $tree;
    private $providers = [];

    private $defaultAllowed = false;
    private $cacheDir;
    private $cachePrefix;
    private $debug;

    public function __construct(array $options)
    {
        list(
            $this->defaultAllowed,
            $this->cachePrefix,
            $this->cacheDir,
            $this->debug
        ) = $options;
    }

    public function provider(ProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        // overrides cache dir
        $this->cacheDir = $cacheDir;
        $this->tree();
    }

    public function tree()
    {
        if (null !== $this->tree) {
            return $this->tree;
        }

        if (null === $this->cachePrefix) {
            return $this->tree = new DefaultTree($this->load());
        }

        $class = $this->cacheClass();
        $cache = new ConfigCache($this->cacheDir.'/'.$class.'.php', $this->debug);

        if (!$cache->isFresh()) {
            $content = $this->buildResourceTree($this->load());
            $cache->write($content);
        }
        require_once $cache;
        return $this->tree = new $class();
    }

    protected function load()
    {
        $resources = [];
        foreach ($this->providers as $provider) {
            $resources = array_merge($resources, $provider->resources());
        }
        $this->validate($resources);
        asort($resources);

        // generate a resource map
        $tree = [];
        foreach ($resources as $resource) {
            $next = &$tree;
            $parts = explode('.', $resource);
            while ($part = array_shift($parts)) {
                // if last part - action
                if (!count($parts)) {
                    $next[$part] = $this->defaultAllowed;
                    continue;
                }

                if (!array_key_exists($part, $next)) {
                    $next[$part] = [];
                }
                $next = &$next[$part];
            }
        }
        return $tree;
    }

    public function validate(array $resources)
    {
        foreach ($resources as $resource) {
            if (preg_match('/[^a-z0-9_\.]/', $resource)) {
                throw new InvalidResourceException("ACL resource \"{$resource}\" can have only lowercase ASCII characters, dots and underscores");
            }

            if (preg_match('/\.$/', $resource)) {
                throw new InvalidResourceException("ACL resource \"{$resource}\" cannot end with a dot");
            }

            if (preg_match('/^\./', $resource)) {
                throw new InvalidResourceException("ACL resource \"{$resource}\" cannot start with a dot");
            }
        }
    }

    protected function cacheClass()
    {
        $parts = explode('\\', get_called_class());
        return $this->cachePrefix . end($parts);
    }

    /**
     * Takes all available application ACL resources and
     * builds an ACL tree cache class body
     *
     * @param array $resources
     * @param bool $defaultAllowed
     * @return string
     */
    protected function buildResourceTree(array $resources)
    {
        $resources = var_export($resources, true);
        return <<<EOF
<?php

use AclBundle\Resource\Tree;

/**
 * This class has been auto-generated by the AclBundle.
 */
class {$this->cacheClass()} extends Tree
{
    protected \$resources = {$resources};
}
EOF;
    }
}
