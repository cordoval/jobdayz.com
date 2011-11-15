<?php
abstract class Isw_Controller_Action extends Zend_Controller_Action
{
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);

        // Set Layout with default modules
        $this->setLayout();
    }

    /**
     *
     * Add css file on view
     * @name  addCss
     * @param array | string $cssList
     * @param string $path
     * @return void
     */
    public function addCss($cssList, $path = '')
    {
        if(is_string($cssList)){
            $cssList[] = $cssList;
        }
        foreach ($cssList as $css){
            $url = $path . "$css.css";
            $this->view->headLink()->appendStylesheet($url);
        }
    }

    /**
     *
     * Add javascript file on view
     * @name  addScript
     * @param array | string $scriptList
     * @param string $path
     * @return void
     */
    public function addScript($scriptList, $path = '')
    {
        if(is_string($scriptList)){
            $scriptList[] = $scriptList;
        }
        foreach ($scriptList as $js){
            $url = $path . "$js.js";
            $this->view->headScript()->appendFile($url);
        }
    }

    /**
     * Loads specified config.ini from "configs" directory
     *
     * @param   string  $file  Ini File
     * @param   string  $section Get section from ini file
     * @return  object Class loaded
     */
    public function loadIni($file, $section)
    {
        $path = APPLICATION_LIBRARY . DIRECTORY_SEPARATOR . 'Zend'. DIRECTORY_SEPARATOR. 'Config'. DIRECTORY_SEPARATOR. 'Ini.php';
        require_once $path;

        $pathfile = APPLICATION_CONFIG . DIRECTORY_SEPARATOR . "$file.ini";
        return new Zend_Config_Ini($pathfile, $section);
    }

    /**
     * Loads specified model from module's "models" directory
     *
     * @param   string  $class  Model classname
     * @param   string  $module Module name. By default, current module is assumed
     * @throws  Zend_Exception  In case model class not found exception would be thrown
     * @return  object Class loaded
     */
    public function loadModel($class, $module = null)
    {
        if(!strpos($class, '_')){
            $class = $class . "_$class";
        }

        $filename = DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $class) . ".php";

    	if( $module !== null){
	        $dir = $this->getFrontController()
                        ->getModuleDirectory($module) . DIRECTORY_SEPARATOR . 'models';
    	} else {
	        $dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'models';
    	}

        $filename = $dir . DIRECTORY_SEPARATOR . $filename;

        require_once $filename;

        return new $class;
    }
    /**
     * Loads specified form from module's "forms" directory
     * @param  string  $class
     * @param  string  $module Module name. By default, current form is assumed
     * @return object Class loaded
     */
    public function loadForm($class, $module = null)
    {
    	if($module !== null){
	        $formDir = $this->getFrontController()
                            ->getModuleDirectory($module) . DIRECTORY_SEPARATOR . 'forms';
    	} else {
	        $formDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'forms';
    	}

        Zend_Loader::loadClass($class, $formDir);
    	return new $class;
    }

    /**
     * Render new view from module's "forms" directory
     * @param  string  $view
     * @param  array   $vars
     * @param  string  $module
     * @return view render
     */
    public function renderView($viewFile, $vars = null,  $module = null)
    {
    	if($module !== null){
	        $viewDir = $this->getFrontController()->getModuleDirectory($module)
	                       . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
    	} else {
    		$viewDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'views';
    	}

		$view = new Zend_View();
		$view->setBasePath($viewDir);
		if( null != $vars){
	    	$view->assign ( (is_object($vars) ? (array) $vars : $vars) );
		}

		return $view->render($viewFile);
    }

    /**
     * Loads service class from services
     * @param  string  $class
     * @param  string  $module
     * @return view render
     */
    public function loadService($class, $module = null)
    {
    	if($module !== null){
	        $serviceDir = $this->getFrontController()->getModuleDirectory($module)
	                       . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR;
    	} else {
	        $serviceDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'services';
    	}

        Zend_Loader::loadClass($class, $serviceDir);
    	return new $class;
    }

    /**
     * Loads Dao class from services
     * @param  string  $class
     * @param  string  $module
     * @return view render
     */
    public function loadDao($class, $module = null)
    {
    	if($module !== null){
	        $daoDir = $this->getFrontController()->getModuleDirectory($module)
	                       . DIRECTORY_SEPARATOR . 'daos';
    	} else {
			$daoDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'daos';
    	}

		$class = "$class" . 'Dao';

        Zend_Loader::loadClass($class, $daoDir);
    	return new $class;
    }

    /**
     * Disable render Layout of view
     * @return void
     */
    public function disableLayout()
    {
    	$this->_helper->layout()->disableLayout();
    }

    /**
     * Disable render view
     * @return void
     */
    public function noRender()
    {
    	$this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Disable Layout and render view
     * @return void
     */
    public function disableLayoutAndRender()
    {
    	$this->disableLayout();
    	$this->noRender();
    }

    /**
     * Return of request is a post
     * @return boolean
     */
    public function isPost()
    {
    	return $this->getRequest()->isPost();
    }

    /**
     * Return of request is a ajax
     * @return boolean
     */
    public function isAjax()
    {
    	return $this->getRequest()->isXmlHttpRequest();
    }

    /**
     * Assign response type JSON
     * @return void
     */
    public function responseJson($data)
    {
    	$this->disableLayoutAndRender();
    	$this->_helper->json($data);
    }

    /**
     * Return specified key of post, or all post
     * @param  string  $key
     * @return object | array
     */
    public function getPost($key = null)
    {
    	if( is_null($key) ){
        	return $this->getRequest()->getPost();    		
    	}
        return $this->getRequest()->getPost($key);
    }

    /**
     * Return specified value key
     * @param string $key
     * @return object
     */
    public function getParam($key)
    {
    	return $this->_request->getParam($key, false);
    }

    /**
     * Return list value from HTTP_GET
     * @return object
     */
    public function getParams()
    {
    	return $this->_request->getParams();
    }

    /**
     * Return specified value key from cookie
     * @param string $key
     * @return object
     */
    public function getCookie($key)
    {
    	return $this->getRequest()->getCookie($key);
    }

    /**
     * Response request with error 404
     * @return  void
     */
    public function response404()
    {
    	$this->getResponse()
             ->setHttpResponseCode(404)
             ->appendBody('Not Found...');
    }

    /**
     * Response request with error exception (500)
     * @param  string  $message
     */
    public function responseError($message)
    {
    	throw new Exception();
     	$this->getResponse()
    		 ->setHttpResponseCode(500)
    		 ->appendBody('Ocurred error on application.');
    }

    public function notifyEvent($event, $transport = null)
    {
		if( !Zend_Registry::isRegistered('SERVICE_MAIL') ){
		    // @todo Lanzar Exception
		    return false;
		}

		$transport       = Zend_Registry::get('SERVICE_MAIL');

		$config          = Zend_Registry::get('CNF_MAIL');

		$event->from     = (isset($event->from) ? $event->from : $config->from);
		$event->fromName = (isset($event->fromName) ? $event->fromName : $config->fromName);

		$event->to       = (isset($event->to) ? $event->to : $config->to);
		$event->toName   = (isset($event->toName) ? $event->toName : $config->toName);

		$event->subject  = (isset($event->subject) ? $event->subject : $config->subject);

		$event->message  = (isset($event->message) ? $event->message : $config->subject);

		if(isset($event->type)){
		    $type           = $event->type;
	            $type           = $config->type->$type;
		    $event->subject = ucfirst($type) . ' - ' . $event->subject;
		}

		$mail = new Zend_Mail('UTF-8');
		$mail->setBodyHtml($event->message);
		$mail->setFrom($event->from, $event->fromName);
		$mail->setReplyTo($event->from, $event->fromName);
		$mail->addTo($event->to, $event->toName);
		$mail->setSubject($event->subject);
		$mail->send($transport);
    }

    /**
     * Layout module assigns the requesting
     * @access  public
     * @name    setLayout
     * @return  void
     */
    public function setLayout($layout = '')
    {
        $moduleName = ($layout == '' ? $this->_request->getModuleName() : $layout);

        $this->_helper->layout->setLayout($moduleName);
    }
}
