<?php

namespace Application\Form;
 
use Zend\Form\Form;
 
class Login extends Form
{
    public function __construct ($name = null, $options = array())
    {
        parent::__construct('loginform');
         
        $this->setAttribute('method', 'post');
         
        // Username
        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'username',
            'options' => array(
                'label' => 'Username',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                )
            ),
             
        ));
         
        // Password
        $this->add(array(
            'type' => 'Zend\Form\Element\Password',
            'name' => 'password',
            'options' => array(
                'label' => 'Password',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                )
            ),
        ));
         
        // CSRF protection
        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'csrf',
        ));
         
        // Submit button
        $this->add(array(
            'type' => 'Zend\Form\Element\Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Sign In'
            )
        ));
    }
}