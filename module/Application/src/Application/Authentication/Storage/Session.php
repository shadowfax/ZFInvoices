<?php

namespace Application\Authentication\Storage;

use Zend\Session\SessionManager;

use Zend\Db\Adapter\AdapterInterface;

use Zend\Session\SaveHandler\DbTableGatewayOptions;

use Zend\Session\Config\SessionConfig;
use Zend\Session\Container as SessionContainer;
use Zend\Session\ManagerInterface as SessionManagerInterface;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Json\Json;
use Zend\Authentication\Storage\StorageInterface;

class Session implements StorageInterface
{

	/**
     * Default session namespace
     */
    const NAMESPACE_DEFAULT = 'Zend_Auth';

    /**
     * Default session object member name
     */
    const MEMBER_DEFAULT = 'storage';
    
    /**
     * Object to proxy $_SESSION storage
     *
     * @var SessionContainer
     */
    protected $session;
    
    /**
     * Session namespace
     *
     * @var mixed
     */
    protected $namespace = self::NAMESPACE_DEFAULT;

    /**
     * Session object member
     *
     * @var mixed
     */
    protected $member = self::MEMBER_DEFAULT;
    
    /**
     * Service locator
     * 
     * @var ServiceLocatorInterface
     */
    protected $adapter;
	
/**
     * Sets session storage options and initializes session namespace object
     *
     * @param  mixed $namespace
     * @param  mixed $member
     * @param  SessionManagerInterface $manager
     */
    public function __construct($namespace = null, $member = null)
    {
        if ($namespace !== null) {
            $this->namespace = $namespace;
        }
        if ($member !== null) {
            $this->member = $member;
        }
        $this->session   = new SessionContainer($this->namespace);
    }
   
    
	/**
     * Returns the session namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Returns the name of the session object member
     *
     * @return string
     */
    public function getMember()
    {
        return $this->member;
    }
    
    public function setAdapter(AdapterInterface $adapter)
    {
    	if (!is_null($this->adapter)) {
    	
    	}
    	
    	$this->adapter = $adapter;
    	
    	$tableGateway = new TableGateway('sessions', $this->adapter);
 
    	$options = new DbTableGatewayOptions();
        $saveHandler = new DbTableGateway($tableGateway, $options);
         
        //open session
        $sessionConfig = new SessionConfig();
        
        // pass the saveHandler to the sessionManager and start the session
        //$sessionManager = new SessionManager( $sessionConfig , NULL, $saveHandler );
        //$sessionManager->start();
        //\Zend\Session\Container::setDefaultManager($sessionManager);
        
        $saveHandler->open($sessionConfig->getOption('save_path'), $this->namespace);
     
       //set save handler with configured session 
       $this->session->getManager()->setSaveHandler($saveHandler);
    }
	
    private function objectToArray($object) {
		if (is_object($object)) {
			// Gets the properties of the given object
			// with get_object_vars function
			$object = get_object_vars($object);
		}
 
		if (is_array($object)) {
			/*
			* Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return array_map(array($this, 'objectToArray'), $object);
		}
		else {
			// Return array
			return $object;
		}
	}
	protected function getSessionId()
    {
        return $this->session->getManager()->getId();
    }
    
	/**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @return bool
     */
    public function isEmpty()
    {
        return !isset($this->session->{$this->member});
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @return mixed
     */
    public function read()
    {
        return $this->session->{$this->member};
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
        $this->session->{$this->member} = $contents;
        
        
    	//if (is_array($contents) && !empty($contents)) {
    	if (is_array($contents)) {
    		$contents = Json::encode($contents);
    	} elseif (is_object($contents)) {
    		$contents = $this->objectToArray($contents);
    		$contents = Json::encode($contents);
    	}
    	$this->session->getManager()->getSaveHandler()->write($this->getSessionId(), $contents);
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @return void
     */
    public function clear()
    {
    	$this->session->getManager()->getSaveHandler()->destroy($this->getSessionId());
        unset($this->session->{$this->member});
    }
}