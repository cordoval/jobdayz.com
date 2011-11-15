<?php
class OfertaController extends Isw_Controller_Action
{
    protected $_offer;
    protected $_category;
    protected $_user;
    protected $_session;
    public function init()
    {
        $this->_session = new Zend_Session_Namespace('AUTH');
          
        $this->_offer = new Application_Model_Offer();
        $this->_category = new Application_Model_Category();
        $this->_user = new Application_Model_User();
    }
    
    public function indexAction()
    {
        $this->_redirect('/');
    }

    public function crearAction()
    {
        $this->_isSessionValid();

        $this->view->user = $this->_user->findBy( $this->_session->id );
        $this->view->categoryList = $this->_category->getAll();
    }

	public function borrarAction()
	{
	    $this->disableLayoutAndRender();
	    if( !$this->_hasParam('id') ){
            return $this->renderScript('/error/noauth.phtml');
	    }
	    $response->status = 0;
	    $id = $this->_getParam('id');
	    $response->status = $this->_offer->borrar( $id );
        $this->responseJson( $response );
	}
	
	public function editarAction()
	{
	    if( !$this->_hasParam('id') ){
            return $this->renderScript('/error/noauth.phtml');
	    }
	    $id = $this->_getParam('id');
        $this->view->categoryList = $this->_category->getAll();
	    $this->view->oferta = $this->_offer->findBy( $id );	    
	}

    public function detalleAction()
    {
        $request = $this->getRequest();
        if( !$this->_hasParam('id') OR !is_numeric( $request->getParam('id') ) ){
            return $this->_redirect('/');
        }
        
        $id = (int) $request->getParam('id');

        $offer = $this->_offer->findBy($id);
        if( !$offer ){
            return $this->_redirect('/');
        }
        $this->view->offer = $offer;
    }

    public function publicarAction()
    {
        $this->_isSessionValid();
        // Create with configuration
        $url = 'http://www.jobdayz.com/oferta/detalle/offerid/';
        if( $this->getRequest()->isPost() ){
            $id = null;
            $offer = $this->getRequest()->getPost();
            if( $this->_hasParam('id') ){
                $id = $this->_getParam('id');
            }
            
            $id = $this->_offer->save($offer, $id);
            if ($id){
                $url = $url . $id;
                $this->view->respuesta = 'Oferta publicada correctamente. ';
                $message = $offer['title'] . "\n" . $url;
                $this->_publishTwitter($message);

                $this->view->url = $url;
            } else {
                $this->view->respuesta = 'No se logro publicar su oferta. Vuelva a intentarlo';
            }
        } else {
            $this->view->respuesta = 'No Es post';
        }
    }

    protected function _publishTwitter($message)
    {
        $twitter = Zend_Registry::get('SN_TWITTER');
        $twitter->statusUpdate( utf8_encode($message) );
    }
    
    protected function _isSessionValid()
    {
        if( !isset($this->_session) ){
            return $this->_redirect('/autentificar/');
        }
        
        if( !isset($this->_session->logein) OR !$this->_session->logein){
            return $this->_redirect('/autentificar/');
        }
        
        if( !isset($this->_session->completeRecord)){
            return $this->_redirect('/autentificar/');
        } else {
            if( !$this->_session->completeRecord ){
                $this->_helper->flashMessenger->addMessage('Debe completar el registro de usuario.');
                return $this->_redirect('/account/perfil');
            }
        }
    }
}
