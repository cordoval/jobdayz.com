<?php
class IndexController extends Isw_Controller_Action
{
    protected $_offer;
    protected $_event;
    public function init()
    {
        $this->_offer = new Application_Model_Offer();
        $this->_event = new Application_Model_Event();
    }

	public function indexAction()
	{
        $this->view->eventList = $this->_event->getSupplyList();
        $this->view->offerList = $this->_offer->getSupplyList();
	}
}
