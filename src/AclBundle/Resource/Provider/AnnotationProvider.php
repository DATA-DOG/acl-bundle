<?php

namespace AclBundle\Resource\Provider;

use AclBundle\Resource\ProviderInterface;
use AclBundle\Resource\Provider\Annotation\ACL;
use AclBundle\Util;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;

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

            $resources[] = implode('.', [
                Util::classToResource($controller->getName()),
                Util::underscore(preg_replace('/Action$/', '', $action)),
            ]);
        }
        return $resources;
    }

    /* public function onKernelController(FilterControllerEvent $event) */
    /* { */
    /*     if (!is_array($controller = $event->getController())) { */
    /*         return; */
    /*     } */
    /*     $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]); */
    /*     $object = new \ReflectionClass($className); */
    /*     $method = $object->getMethod($controller[1]); */

    /*     $classConfigurations  = $this->getConfigurations($this->reader->getClassAnnotations($object)); */
    /*     $methodConfigurations = $this->getConfigurations($this->reader->getMethodAnnotations($method)); */
    /*     $configurations = array(); */
    /*     foreach (array_merge(array_keys($classConfigurations), array_keys($methodConfigurations)) as $key) { */
    /*         if (!array_key_exists($key, $classConfigurations)) { */
    /*             $configurations[$key] = $methodConfigurations[$key]; */
    /*         } elseif (!array_key_exists($key, $methodConfigurations)) { */
    /*             $configurations[$key] = $classConfigurations[$key]; */
    /*         } else { */
    /*             if (is_array($classConfigurations[$key])) { */
    /*                 if (!is_array($methodConfigurations[$key])) { */
    /*                     throw new \UnexpectedValueException('Configurations should both be an array or both not be an array'); */
    /*                 } */
    /*                 $configurations[$key] = array_merge($classConfigurations[$key], $methodConfigurations[$key]); */
    /*             } else { */
    /*                 // method configuration overrides class configuration */
    /*                 $configurations[$key] = $methodConfigurations[$key]; */
    /*             } */
    /*         } */
    /*     } */
    /*     $request = $event->getRequest(); */
    /*     foreach ($configurations as $key => $attributes) { */
    /*         $request->attributes->set($key, $attributes); */
    /*     } */
    /* } */
}
