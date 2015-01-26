<?php

namespace AclBundle\EventListener;

use AclBundle\Access\DecisionManager;
use AclBundle\Util;
use AclBundle\Exception\ResourceNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ControllerListener implements EventSubscriberInterface
{
    private $dm;

    public function __construct(DecisionManager $dm)
    {
        $this->dm = $dm;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        list ($ctrl, $action) = $controller;
        $resource = Util::classToResource($ctrl);
        $action = Util::underscore(preg_replace('/Action$/', '', $action));

        try {
            if (!$allowed = $this->dm->isAllowed($action, $resource)) {
                throw new AccessDeniedHttpException("User is not allowed to \"{$action}\" resource: \"{$resource}\"");
            }
        } catch (ResourceNotFoundException $e) {
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
