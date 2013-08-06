<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Application\Authentication\Storage\Session;

use Application\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;

use Zend\Authentication\AuthenticationService;

use Application\Authentication\AuthenticationListener;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        // Authentication required
        $authenticationListener = new AuthenticationListener();
        $authenticationListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getServiceConfig()
    {
        return array(
        /*
        	'controllers' => array(
	        	'invokables' => array(
	        		'Application\Controller\Account' => 'Application\Controller\AccountController',
	        	),
        	),
        	*/
        	'factories' => array(
        		'AuthService' => function ($sm) {
        			$authServiceManager = new AuthenticationService();
        			
        			// Get the database adapter
	                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
	                 
	                // Setup authentication adapter
	                $authAdapter = new CredentialTreatmentAdapter($dbAdapter);
	                $authAdapter->setTableName('users')
	                            ->setIdentityColumn('username')
	                            ->setCredentialColumn('password');
	                // Set our authentication adapter in our authentication service
	                $authServiceManager->setAdapter($authAdapter);
	                
	                // Session storage
	                $session = new Session();
	                $session->setAdapter($dbAdapter);
	                $authServiceManager->setStorage($session);
	                
	                return $authServiceManager;
        		}
        	)
        );
    }
}
