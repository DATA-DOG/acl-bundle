<?php

namespace AclBundle\Resource\Provider;

use AclBundle\Resource\ProviderInterface;
use AclBundle\Resource\Provider\Annotation\ACL;
use AclBundle\Util;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Doctrine\Common\Annotations\Reader;

class AnnotationProvider implements ProviderInterface
{
    private $reader;
    private $router;

    public function __construct(Reader $reader, Router $router)
    {
        $this->reader = $reader;
        $this->router = $router;
    }

    public function resources()
    {
        $resources = [];
        $controllers = [];
        foreach ($this->router->getRouteCollection()->all() as $name => $route) {
            if (strpos($name, '_') === 0) {
                continue; // skip private routes
            }

            $defaults = $route->getDefaults();
            if (!isset($defaults['_controller'])) {
                continue; // some weird routes without controller
            }

            list($ctrl, , $action) = explode(':', $defaults['_controller']);
            if (!class_exists($ctrl)) {
                // @TODO: must be service
                throw new \Exception("Could not parse controller class name: {$ctrl}");
            }
            $controllers[$ctrl][] = $action;
        }
        foreach ($controllers as $className => $actions) {
            $controller = new \ReflectionClass($className);
            foreach ($actions as $action) {
                $resources = array_merge($resources, $this->parse($controller, $action));
            }
        }
        return $resources;
    }

    private function parse(\ReflectionClass $controller, $action)
    {
        $resources = [];
        $annotations = $this->reader->getMethodAnnotations($controller->getMethod($action));
        foreach ($annotations as $annotation) {
            if (!$annotation instanceof ACL) {
                continue;
            }

            if (null !== $annotation->value && $annotation->value != "") {
                $resources[] = $annotation->value;
                continue;
            }

            $resources[] = implode('.', [
                Util::classToResource($controller->getName()),
                Util::underscore(preg_replace('/Action$/', '', $action)),
            ]);
        }
        return $resources;
    }
}
