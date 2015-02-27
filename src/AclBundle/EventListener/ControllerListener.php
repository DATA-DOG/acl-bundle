<?php

namespace AclBundle\EventListener;

use AclBundle\Access\DecisionManager;
use AclBundle\Resource\Provider\Annotation\ACL;
use AclBundle\Util;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ControllerListener implements EventSubscriberInterface
{
    private $dm;
    private $reader;

    public function __construct(DecisionManager $dm, Reader $reader)
    {
        $this->dm = $dm;
        $this->reader = $reader;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        list ($ctrl, $action) = $controller;

        $ref = new \ReflectionClass($ctrl);
        $annotations = $this->reader->getMethodAnnotations($ref->getMethod($action));

        $acl = current(array_filter($annotations, function($annotation) {
            return $annotation instanceof ACL;
        }));

        if ($acl) {
            if (strlen($acl->value)) {
                $parts = explode('.', $acl->value);

                $action = array_pop($parts);
                $resource = implode('.', $parts);
            } else {
                $resource = Util::classToResource($ctrl);
                $action = Util::underscore(preg_replace('/Action$/', '', $action));
            }
            if (!$allowed = $this->dm->isGranted($action, $resource)) {
                throw new AccessDeniedHttpException("User is not allowed to \"{$action}\" resource: \"{$resource}\"");
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
