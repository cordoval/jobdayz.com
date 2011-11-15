<?php
class Application_Model_User extends Zend_Db_Table_Abstract
{
	protected $_name = 'user';
	protected $_primary = 'id';
    
    public function save($data, $id = null)
    {
        if( is_null($id) ){
            $row = $this->createRow();
        } else {
            $row = $this->findBy($id);
        }
        
        $row->setFromArray($data);
        return $row->save();
    }

    public function findBy($id)
    {
        $id = (int) $id;
        $row = $this->find( $id )->current();
        return $row;
    }
    
    public function findBySocialId($id)
    {
        $select = $this->select()
                       ->where("socialId = ?", $id);
    
        $row = $this->fetchRow($select);
        if( !$row ){
            return false;
        }
        return $row;
    }
    
    public function checkUser($email)
    {
        $select = $this->select()
                       ->where("email = ?", $email);
    
        $row = $this->fetchRow($select);
        if( !$row ){
            return false;
        }
        return true;
    }
    
    public function login($email, $password)
    {
        $select = $this->select()
                       ->where("email = ?", $email)
                       ->where("password = ?", $password)
                       ->where("active = ?", 1);
    
        $row = $this->fetchRow($select);
        if( !$row ){
            return false;
        }
        return $row;
    }
    
    public function active($key)
    {
        $select = $this->select()->setIntegrityCheck(false)
                       ->where("keyactive = ?", $key)
                       ->where('active = ?', 0);
     
        $row = $this->fetchRow($select);

        if( !$row ){
            return false;
        }

        $data['active'] = 1;
        $row->setFromArray($data);

        return $row->save();
    }
}
