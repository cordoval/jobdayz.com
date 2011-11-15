<?php
class Application_Model_Position extends Zend_Db_Table_Abstract
{
	protected $_name = 'position';
	protected $_primary = 'id';

    public function getAll()
    {
        return $this->fetchAll($this->select()->order('name  asc'));
    } 
}
