<?php
class Application_Form_Registro extends Zend_Form
{
    public function init()
    {                           
        // CompanyId
        $elements[] = $this->createElement('hidden', 'companyId')
                           ->addValidator('digits', array('greaterThan', false, array(1))               
                           ->setRequired(true);

        // PositionId
        $elements[] = $this->createElement('hidden', 'positionId')
                           ->addValidator('digits', array('greaterThan', false, array(1))               
                           ->setRequired(true);
                           
        // EmailAddress        
        $elements[] = $this->createElement('text', 'email')
                           ->addValidator('stringLength', false, array(8, 20))
                           ->addValidator('EmailAddress')
                           ->setRequired(true);
                           
        // Password
        $elements[] = $this->createElement('text', 'password')
                           ->addValidator('alnum')
                           ->addValidator('stringLength', false, array(8, 20))
                           ->setRequired(true);
                           
        // Contact
        $elements[] = $this->createElement('text', 'password')
                           ->addValidator('alnum')
                           ->addValidator('stringLength', false, array(8, 20))
                           ->setRequired(true);
                           
        // Phone
        $elements[] = $this->createElement('text', 'phone')
                           ->addValidator('stringLength', false, array(8, 20))
                           ->setRequired(true);

        // Add elements to form:
        $this->addElements($elements);
    }
}
