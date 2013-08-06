<?php

namespace Application\Authentication;
 
use Zend\Mvc\Router\RouteMatch;
 
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
 
class AuthenticationListener implements ListenerAggregateInterface
{
    protected $listeners = array();
 
    public function attach(EventManagerInterface $events, $priority = 1000)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'), $priority);
    }
 
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
 
    public function onDispatch(MvcEvent $e)
    {
        $authService = $e->getApplication()->getServiceManager()->get('AuthService');
         
        $matches = $e->getRouteMatch();
        if (!$matches instanceof RouteMatch) {
            // Don't do anything if there is not a match
            return;
        }
         
        $controller = $matches->getParam('controller');
        $action     = $matches->getParam('action');
         
        if ($controller == 'Application\Controller\Account' && $action == 'login') {
            // If we are at the login, don't do anything
            return;
        }
         
        if ($authService->hasIdentity()) {
            // If there is an active session, don't do anything
            return;
        }

        // Si lo anterior no se da, debemos iniciar sesion
        $matches->setParam('controller', 'Application\Controller\Account');
        $matches->setParam('action', 'login');
    }
}