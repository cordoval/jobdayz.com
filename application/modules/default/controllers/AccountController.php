<?php
class AccountController extends Isw_Controller_Action
{
    protected $_session;
    protected $_position;
    protected $_user;
    protected $_event;
    protected $_offer;
    protected $_namespace;
    public function init()
    {
        $this->_namespace = 'AUTH';
        $allow = array('activar', 'crear');
        $request = $this->getRequest();
        
        $this->_session = new Zend_Session_Namespace($this->_namespace);
        
        if( !in_array($request->getActionName(), $allow ) ) {
            if( !$this->_session->logein ){
                $this->_redirect('/autentificar/');
            }
        }
        
        $this->_position = new Application_Model_Position();
        $this->_user = new Application_Model_User();
        $this->_event = new Application_Model_Event();
        $this->_offer = new Application_Model_Offer();
    }

    public function indexAction()
    {
        return $this->_helper->redirector('perfil');
    }
    
    public function crearAction()
    {
        $response->status = 0;
        $this->disableLayoutAndRender();
         
        if( !$this->isAjax() AND !$this->isPost()){
            return $this->renderScript('/error/noauth.phtml');
        }
        
        $request = $this->getRequest();
        $email   = $request->getPost('emailUp');
        
        if ( empty($email) ){
            $response->message = 'Ingrese un email valido.';
            return $this->responseJson($response);
        }

        if( !$this->_user->checkUser( $email ) ){
            $user = array(
                'email' => $email
            );

            $id = $this->_user->save($user);
            if( !$id ){
                $response->message = 'Ocurrio un problema, no se logro registrar. Intentelo despues';
            } else {
                $this->_session->id = $id;
                $this->_session->logein = true;// logueado
                $response->redirect = BASE_URL . 'account/perfil';
                $response->id = $id;
                $response->status = 1;
            }
        } else {
            $response->message = 'Email  ya se encuentra registrado, inicie sesion por favor.';
        }

        $this->responseJson($response);
    }
    
    public function perfilAction()
    {
        // Recover User info data of Session
        $user = $this->_user->findBy( $this->_session->id );
        $this->_session->completeRecord = $user->active;
        
        $this->view->user = $user;
        $this->view->completeRecord = $user->active;
        $this->view->positionList = $this->_position->getAll();
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }
    
    public function publicacionesAction()
    {
        $this->view->publicacionesList = $this->_event->getAllByUser( $this->_session->id );
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $this->render('perfil');
    }
    
    public function ofertasAction()
    {
        $this->view->ofertasList = $this->_offer->getAllByUser( $this->_session->id );
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $this->render('perfil');
    }
    
    public function guardarAction()
    {
        $response->status = 0;
 
        if( !$this->isPost() ){
            return $this->renderScript('/error/noauth.phtml');
        }
        
        $request = $this->getRequest();
        $id      = $request->getPost('id');

        $user    = $request->getPost();
        if( !$user['active'] ){
            $user['keyactive'] = $this->_generarKey();
        } else {
            if( strlen($user['password']) == 0 ){
                unset($user['password']);
            }
        }

        $response->status = $this->_user->save($user, $id);

        if( $response->status ){
            $add = '';
            if( !$user['active'] ){
                $this->view->key = $user['keyactive'];
                $account->content = $this->view->render('account/notificacion.phtml');
                $account->email   = $user['email'];
                $account->name    = $user['contact'];
                // _sendMailActivation, deberia estar en un jobs
                $this->_sendMailActivation($account);
                $add = 'Se ha enviado un email al correo registrado para activar esta cuenta.';
            }

            $message = "Datos de usuario actualizados correctamente.\n".$add;
        } else {
            $message = 'No se logro registrar, vuelva a intentarlo porfavor.';
        }
        //gotoSimple($action, $controller = null, $module = null, array $params = array());
        $this->_helper->flashMessenger->addMessage($message);
        $this->_helper->redirector('perfil');
    }
    
    public function activarAction()
    {
        if( !$this->_hasParam('key') ){
            $this->view->error = "El key de activacion es invalido.";
        }
        
        $key = $this->_getParam('key');
        
        if( !$this->_user->active($key) ){
            $this->view->error = "El key de activacion es invalido.";
        }
    }
    
    public function logoutAction()
    {
        Zend_Session::destroy();    
        $this->_redirect("/");
    }
    
    protected function _generarKey()
    {
        $hoy = date('dYmshi');
        return base64_encode( md5( $hoy ) );
    }
    
    protected function _sendMailActivation($account)
    {
        $server = $this->loadIni('mail', 'notificador');

		$config = array(
		    'ssl'      => $server->protocol,
		    'port'     => $server->port,
		    'auth'     => $server->auth,
		    'username' => $server->username,
		    'password' => $server->password
		);
        try{
		    $transport = new Zend_Mail_Transport_Smtp($server->smtp, $config);
            $mail = new Zend_Mail('UTF-8');
            $mail->setBodyHtml($account->content);
            $mail->setFrom($server->from, $server->fromName);
		    $mail->setReplyTo($server->from, $server->fromName);
            $mail->addTo($account->email, $account->name);
            $mail->setSubject($server->subject . ' - Activar Cuenta.');
            $mail->send($transport);
        } catch(Exception $e){
            echo $e->getMessage();
        }
    }
}
