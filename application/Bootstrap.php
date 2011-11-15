<?php
/**
 * Inicia las configuraciones por defecto de la aplicación.
 * @author    Cabada Gutierrez, Miguel Angel
 * @name      Bootstrap
 * @package   Application
 * @copyright InnovasysWeb 2011
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	/**
	 * Inicializa el estanda w3c de los documentos html
	 * @name    _initDoctype
	 * @access  protected
	 * @return  Zend_View
	 */
	protected function _initDoctype()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('XHTML1_STRICT');

		return $view;
	}

	/**
	 *
	 * Carga los datos de configuracion del application.ini
	 * @name   _initConfig
	 * @access protected
	 * @return Zend_Config $config
	 */
	protected function _initConfig()
	{
		$config = new Zend_Config($this->getOptions(), true);
		// Registry all config
		Zend_Registry::set('CNF_ALL', $config);

        // Registry baseUrl
        define('BASE_URL', $config->baseUrl);

		// Registry mail config
		//Zend_Registry::set('CNF_MAIL',  $config->resources->mail);

		// Registry db config
		Zend_Registry::set('CNF_DB',  $config->resources->db);

		return $config;
	}
	
    /**
     * Inicializa la conexcion al servidor de datos
     * @name    _initDb
     * @access  protected
     * @return  Zend_Db $db
     */
    protected function _initDb()
    {
        // Get Config
        if( !Zend_Registry::isRegistered('CNF_DB') ){
            return false;
        }

        $cnf = Zend_Registry::get('CNF_DB');

        $db = Zend_Db::factory($cnf->adapter, array(
            'host'      => $cnf->host,
            'username'  => $cnf->username,
            'password'  => $cnf->password,
            'dbname'    => $cnf->dbname
        ));
        
        Zend_Db_Table::setDefaultAdapter($db);
        // Default mode result
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        Zend_Registry::set('SERVICE_DB', $db);

        return $db;
    }

	/**
	 * Inicializa la conexcion al servidor de correo, para notificaciones de sistema(Errores, advertencias)
	 * @name   _initMail
	 * @access protected
	 * @return Zend_Mail_Transport_Smtp
	 */
	protected function _initMail()
	{
		// Get Config
		if( !Zend_Registry::isRegistered('CNF_MAIL') ){
			return false;
		}
		$cnf = Zend_Registry::get('CNF_MAIL');
		$config = array(
		    'ssl'      => $cnf->protocol,
		    'port'     => $cnf->port,
		    'auth'     => $cnf->auth,
		    'username' => $cnf->username,
		    'password' => $cnf->password
		);

		$transport = new Zend_Mail_Transport_Smtp($cnf->smtp, $config);

		Zend_Registry::set('SERVICE_MAIL', $transport);

		return $transport;
	}
	
    protected function _initSocialNetwork()
    {
        $twitterIni = APPLICATION_CONFIG . DIRECTORY_SEPARATOR . 'social.ini';
        // Registry social network config

        // Registry Twitter
        $twitter = new Zend_Config_Ini( $twitterIni, 'twitter');
        Zend_Registry::set('CNF_TWITTER',  $twitter);

        $oToken = new Zend_Oauth_Token_Access();
        $oToken->setToken($twitter->accessToken)->setTokenSecret($twitter->accessTokenSecret);

        $oTwitter = new Zend_Service_Twitter(array(
            'accessToken' => $oToken,
            'consumerKey' => $twitter->consumerKey,
            'consumerSecret' => $twitter->consumerSecret
        ));
        
        Zend_Registry::set('SN_TWITTER', $oTwitter);

        // Registry Facebook
    }
}
