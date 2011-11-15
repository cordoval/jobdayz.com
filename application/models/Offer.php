<?php
class Application_Model_Offer extends Zend_Db_Table_Abstract
{
	protected $_name = 'offer';
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
    
    public function getAllByUser($id)
    {
        return $this->fetchAll($this->select()->where("userId = ?", $id)->order('publish  asc'));
    } 
    
    public function borrar($id)
    {
        return $this->delete(array("id = ?" => $id));
    }
    

    public function findBy($id)
    {
        $id = (int) $id;
        $row = $this->find( $id )->current();
        return $row;
    }

    public function getSupplyList()
    {
        $select = $this->select()
                       ->order(array('publish desc'));
        return $this->fetchAll($select);
    } 
}
