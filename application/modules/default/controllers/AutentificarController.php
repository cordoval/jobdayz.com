<?php
class AutentificarController extends Isw_Controller_Action
{
    protected $_namespace;
    protected $_user;

    public function init()
    {
        $this->_user = new Application_Model_User();
        $this->_namespace = 'AUTH';
    }
    
    public function indexAction() { /*show view login*/ }
    
    public function loginAction()
    {
        $response->status = 0;
        $this->disableLayoutAndRender();
        
        if ( !$this->isAjax() AND !$this->isPost()){
            return $this->renderScript('/error/noauth.phtml');
        }
        
        $request = $this->getRequest();
        
        $form = $this->_validarFormLogin( $request->getPost() );

        if( !$form->status ){
            $response->message = $form->errors;
        } else {
            $email = $form->fields['email'];
            $password = $form->fields['password'];
            $user = $this->_user->login($email, $password);
            if( !$user ){
                $response->message = "Usuario o Clave incorrectos.";
            } else {
                $response->status = 1;
                $response->redirect = '/account/perfil';
                $auth = new Zend_Session_Namespace('AUTH');
                $auth->id = $user->id;
                $auth->logein = TRUE;
                $auth->setExpirationSeconds(1000); // 5 minutos inactivo :D
                $auth->lock(); // Solo lectura la info :D
            }
        }
        
        $this->responseJson($response);
    }

    public function twitterAction()
    {
        $session = new Zend_Session_Namespace($this->_namespace);
        
        $twitter = Zend_Registry::get('CNF_TWITTER');
        $config = array(
            'callbackUrl' => $twitter->callbackUrl,
            'siteUrl'     => $twitter->siteUrl,
            'consumerKey' => $twitter->consumerKey,
            'consumerSecret' => $twitter->consumerSecret
        );
        // Instance new oauth consumer
        $consumer = new Zend_Oauth_Consumer($config);
        
        if( $this->_hasParam('oauth_token') ){
            $session->token = $consumer->getAccessToken( $_GET, unserialize($session->request_token) );

            $twitter = new Zend_Service_Twitter(array(
                'accessToken' => $session->token
            ));

            $twitterAccount = $twitter->account->verifyCredentials();

            $this->_createSession( $twitterAccount->id, $session );

            return $this->_redirect('/account/perfil/');
        }

        // fetch a request token
        $token = $consumer->getRequestToken();

        // save the token to session
        $session->request_token = serialize($token);
        
        // redirect the user
        $consumer->redirect();
    }
    
    public function facebookAction()
    {
        $session = new Zend_Session_Namespace($this->_namespace);

        $facebook = $this->loadIni('social', 'facebook');
        $appID = $facebook->appID;
        $appSecret = $facebook->appSecret;
        $urlCallback = $facebook->urlCallback;
        
                
        $fbUrl = "http://www.facebook.com/dialog/oauth?client_id=" .$appID. "&redirect_uri=" .urlencode ( $urlCallback );
        
        if( $this->_hasParam('code') AND ( strlen($this->_hasParam('code')) > 0 ) ){
            $code = $this->_request->getParam ('code');
            
            $client = new Zend_Http_Client ( $facebook->urlOauth );
            $client->setParameterGet ('client_id', $facebook->appID);
            $client->setParameterGet ('client_secret', $facebook->appSecret);
            $client->setParameterGet ('code', $code);
            $client->setParameterGet ('redirect_uri', $facebook->urlCallback);

            $result = $client->request ('GET');
            $params = array();
            parse_str ( $result->getBody (), $params);

            if( isset($params ['access_token']) AND ( strlen($params ['access_token']) > 0 ) ){
                $accessToken = $params ['access_token'];
                $session->token = serialize($accessToken);
                
                $client = new Zend_Http_Client ($facebook->urlProfile);
                $client->setParameterGet ('client_id', $facebook->appID);
                $client->setParameterGet ('access_token', $accessToken);
                $result = $client->request ('GET');
                $facebookUser = json_decode ( $result->getBody (), true);

                $this->_createSession( $facebookUser, $session );

                return $this->_redirect('/account/perfil/');
            }       
        }
        
        $this->_redirect($fbUrl);
    }
    
    protected function _createSession( $facebookUser, $session )
    {
        $id = null;
        $user = $this->_user->findBySocialId( $facebookUser['id'] );

        if( !$user ){
            $user = array(
                'socialId' => $facebookUser['id'],
                'contact' => $facebookUser['name']
            );
            $id = $this->_user->save( $user );
        } else {
            $id = $user->id;
        }

        $session->id = $id;
        $session->logein = true;
    }
    
    protected function _validarFormLogin($form)
    {
        $response->status = 0;
        $formLogin = new Application_Form_Login();
        if( $formLogin->isValid( $form ) ){
            $response->status = 1;
            $response->fields = $formLogin->getValues();
        } else {
            $response->errors = $formLogin->getMessages();
        }
        
        return $response;
    }
}
