<?php
class EventoController extends Isw_Controller_Action
{
    protected $ini;
    protected $_event;
    protected $_user;
    protected $_session;
    
    public function init()
    {
        $this->_session = new Zend_Session_Namespace('AUTH');
        
        $this->ini = $this->loadIni('configuration', 'configuration');
        $this->_event = new Application_Model_Event();
        $this->_user = new Application_Model_User();
    }

	public function indexAction()
	{
        $this->_redirect('/');
	}
	
	public function publicarAction()
	{
        $this->_isSessionValid();

        // Create with configuration
        $request = $this->getRequest();
        
        #print_r($request->getPost());exit;
        
        if( !$request->isPost() ){
            return $this->render('/error/noauth.phtml');
        }

        $id = null;
        $event = $request->getPost();
        
        if( $this->_hasParam('id') ){
            $id = $this->_getParam('id');
        }
        
        $id = $this->_event->save( $event, $id );

        if ($id){
            $url = $this->ini->eventUrl . $id;
            $this->view->respuesta = 'Evento publicado correctamente. ';

            $this->view->title = $event['title'];
            $this->view->url = $url;
            $message = $this->view->render('/evento/twitt.phtml');
            
            $this->_publishTwitter($message);
        } else {
            $this->view->respuesta = 'No se logro publicar el evento. Vuelva a intentarlo';
        }
	}
	
	public function borrarAction()
	{
	    $this->disableLayoutAndRender();
	    if( !$this->_hasParam('id') ){
            return $this->renderScript('/error/noauth.phtml');
	    }
	    $response->status = 0;
	    $id = $this->_getParam('id');
	    $response->status = $this->_event->borrar( $id );
        $this->responseJson( $response );
	}
	
	public function editarAction()
	{
	    if( !$this->_hasParam('id') ){
            return $this->renderScript('/error/noauth.phtml');
	    }
	    $id = $this->_getParam('id');
	    $this->view->evento = $this->_event->findBy( $id );	    
	}
	
	public function crearAction()
	{
        $this->_isSessionValid();
        $this->view->user = $this->_user->findBy( $this->_session->id );
	}

    public function detalleAction()
    {
        $request = $this->getRequest();
        if( !$this->_hasParam('id') ){
            return $this->renderScript('/error/noauth.phtml');
        }
        $id = $request->getParam('id');

        $event = $this->_event->findBy($id);
        if( !$event ){
            return $this->_redirect('/');
        }
        $this->view->event = $event;
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
