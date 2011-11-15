<?php
class Application_Form_Login extends Zend_Form
{
    public function init()
    {
        // Username
        $elements[] = $this->createElement('text', 'email')
                           ->addValidator('stringLength', false, array(8, 35))
                           ->addValidator('EmailAddress')
                           ->setRequired(true);

        // Password
        $elements[] = $this->createElement('text', 'password')
                           ->addValidator('stringLength', false, array(6, 35))
                           ->setRequired(true);

        // Add elements to form:
        $this->addElements($elements);
    }
}
