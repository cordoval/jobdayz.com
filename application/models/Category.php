<?php
class Application_Model_Category extends Zend_Db_Table_Abstract
{
	protected $_name = 'category';
	protected $_primary = 'id';

    public function getAll()
    {
        return $this->fetchAll($this->select()->order('name  asc'));
    } 
}
