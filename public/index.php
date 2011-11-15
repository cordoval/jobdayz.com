<?php
;error_reporting(E_ALL|E_STRICT); 
;ini_set('error_reporting', E_ALL);

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define path to application library
defined('APPLICATION_LIBRARY')
|| define('APPLICATION_LIBRARY', realpath(dirname(__FILE__) . '/../library'));

// Define path to application configs
defined('APPLICATION_CONFIG')
|| define('APPLICATION_CONFIG', realpath(dirname(__FILE__) . '/../application/configs'));

// Define path to application models
defined('APPLICATION_MODEL')
|| define('APPLICATION_MODEL', realpath(dirname(__FILE__) . '/../application/models'));

// Define path to application models
defined('APPLICATION_FORMS')
|| define('APPLICATION_FORMS', realpath(dirname(__FILE__) . '/../application/forms'));

// Define path to application models
defined('APPLICATION_MODULE')
|| define('APPLICATION_MODULE', realpath(dirname(__FILE__) . '/../application/modules'));

// Define path to application resources
defined('APPLICATION_RESOURCE')
|| define('APPLICATION_RESOURCE', realpath(dirname(__FILE__) . '/../public/_resource'));

define('APPLICATION_ENV', 'production');
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Include paths

set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            realpath(APPLICATION_PATH . '/../library'),
            realpath(APPLICATION_PATH . '/models'),
            get_include_path(),
        )
    )
);

// Zend_Application
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
	APPLICATION_PATH . '/configs/application.ini'
);
try{
    // Start Session
    Zend_Session::start();
    // Run Application
    $application->bootstrap()
			    ->run();
} catch (Exception $e){
    #echo $e->getMessage();
}
