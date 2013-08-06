<?php

namespace Application\Controller;

use Application\Form\Login;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AccountController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function loginAction()
    {
    	$authService = $this->getServiceLocator()->get('AuthService');
    	
    	// Authenticated users are automatically redirected to the homepage
    	if ( $authService->hasIdentity() ) {
            return $this->redirect()->toRoute('home');
        }
        
        //$authService->clearIdentity();
        
        $form = new Login();
        
    	// If the form has been posted...
        if ( $this->request->isPost() ) {
            $form->setData($this->request->getPost());
            // Validate the form
            if ( $form->isValid() ) {
                // Prepare authentication adapter
                $authAdapter = $authService->getAdapter();
                $authAdapter->setIdentity($form->get('username')->getValue())
                    ->setCredential($form->get('password')->getValue());
                 // Authenticate the user
                $result = $authAdapter->authenticate();
 
                if ($result->isValid())
                {
                	// store the identity as an object where only the username and
				    // real_name have been returned
				    $storage = $authService->getStorage();
				    $storage->write($authAdapter->getResultRowObject(array(
				        'username',
				        'display_name',
				    )));
				
				    // store the identity as an object where the password column has
				    // been omitted
				    $storage->write($authAdapter->getResultRowObject(
				        null,
				        'password'
				    ));
				    
				    return $this->redirect()->toRoute('home');
                }
            }
        }
    	
    	$viewmodel = new ViewModel(array(
    		'form' => $form
    	));
    	$viewmodel->setTerminal(true);
    	
    	return $viewmodel;
    }
    
	public function logoutAction()
    {
    	$authService = $this->getServiceLocator()->get('AuthService');
    	//$authService->getStorage()->forgetMe();
    	$authService->clearIdentity();
         
        $this->flashmessenger()->addMessage("You've been logged out");
        return $this->redirect()->toRoute('home');
    }
}
