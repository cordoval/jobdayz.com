<?php
/**
 * Controla los errores producidos en la aplicacion, generando un informe y enviandolo por correo
 * de acuerdo la configuracion en application.ini
 * @name  ErrorController
 *
 */
class ErrorController extends Zend_Controller_Action
{
	/**
	 * @return  void
	 */
	public function errorAction() // @TODO los mensajes deberian estar declarados en un .ini
	{
		//$event->type = 'information';

		$errors = $this->_getParam('error_handler');
		switch ($errors->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				// 404 error -- controller or action not found
				$this->getResponse()->setHttpResponseCode(404);
				$this->view->message = 'Pagina no encontrada';
				// $event->type = 'warning';
				break;
			default:
				// application error
				$this->getResponse()->setHttpResponseCode(500);
				$this->view->message = 'No se logro procesar la solicitud.';
				// $event->type = 'error';
				break;
		}
		 
		// Log exception, if logger available
		$log = $this->getLog();
		if ($log) {
			$log->crit($this->view->message, $errors->exception);
		}

		// Conditionally display exceptions
		if ($this->getInvokeArg('displayExceptions') == true) {
			$this->view->exception = $errors->exception;
		}

        /*
		// Send mail notification
		if ( Zend_Registry::isRegistered('NOTIFY_EXCEPTION')) {
			if( Zend_Registry::get('NOTIFY_EXCEPTION') ){
				$message = '<h4>Ocurrio algo inesperado:</h4><p> '. $this->view->message .' </p>' .
			   '<h4>Información de lo sucedido:</h4><pre>' . $errors->exception->getMessage() . '</pre>' .
			   '<h4>Trace de petición:</h4><pre>' . $errors->exception->getTraceAsString() . '</pre>' .
			   '<h4>Parametros de Solicitud:</h4><pre>' . $errors->exception->getParams() . '</pre>';
				$event->subject  = $this->view->message;
				$event->message  = $message;
				$this->notifyEvent($event);
			}
		}
        */
		// Assign request
		$this->view->request   = $errors->request;
	}

	public function getLog()
	{
		$bootstrap = $this->getInvokeArg('bootstrap');
		if (!$bootstrap->hasPluginResource('Log')) {
			return false;
		}
		$log = $bootstrap->getResource('Log');
		return $log;
	}
}
