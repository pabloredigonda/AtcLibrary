<?php

namespace Core\Controller\Forgot\Controller;

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Core\Controller\Forgot\Form\RecoverForm;
use Core\Dto\ResponseDTO;
use Zend\Json\Json;
use Core\Controller\SMController;
use Core\Notification\User\UserForgotPasswordNotification;
use Core\Util\Constants;
use Core\Notification\Target\UserTarget;
use Zend\Ldap\Filter\StringFilter;
use Core\Login\Form\LoginForm;

class ForgotController extends SMController {

	public function recoverAction() {
		
		$response	= $this->getResponse();
		$request	= $this->getRequest();
		
		$redirect = '';
		$arrMessages = array();
		
		if ($this->getRequest()->isPost()) {

		    $email = trim($request->getPost('email'));
		    $module = $this->getModule($request->getPost('module'));
		    
			$recoveryId = $this->getUserService()->generateRecoveryKey($email, $module);
			if ($recoveryId) {
				
				$user = $this->getUserService()->findByRecoveryId($recoveryId);

				$notification = new UserForgotPasswordNotification($user, $recoveryId);
				$notification->setModule($module);
                $this->notify($notification);
				
				$success = true;

			} else {
				$success = false;
				array_push($arrMessages, $this->translate("No existe el email especificado en la plataforma."));
			}

		} else {
			$success = false;
		}
		
		$responseDTO = new ResponseDTO($success, $redirect, $arrMessages);
		
		return $response->setContent(Json::encode($responseDTO));
	}
	
	public function changeAction() {
		
		$errors = array();
		$recovery = $this->getRequest()->getQuery('a');
		
		// Replace spaces by pluses again
		$recovery = str_replace(' ', '+', $recovery);
		
		$route = '';
		
		if ($this->getRequest()->isPost()) {
		    
		    // Validation
		    $loginForm = new LoginForm();
		    $loginForm->setData($this->getRequest()->getPost());
		    if (!$loginForm->isValid()) {
                $result = new JsonModel(array(
					'success' 		=> false,
                ));
		    }
		    	
		    // Params
		    $data = $loginForm->getData();
		    $password = $data['password'];
			
			if ($user = $this->getServiceLocator()->get('Core\Service\UserService')->findByRecoveryId($recovery)) {

				if ($this->getServiceLocator()->get('Core\Service\UserService')->recoverPassword($recovery, $password)) {
					$success = "true";
				}
				
				$key = explode('.', base64_decode($recovery));
				$module = $key[0];

				$user->setStatus($this->getSystemUsersStatusService()->findById('active'));
				$this->getUserService()->save($user);
				
				$user = $this->getServiceLocator()->get('AuthService')->authentication($user->getEmail(), $password, $module, true);
				$route = $this->getServiceLocator()->get('AuthService')->getRedirectRoute($user, $module);
				
			} else {

				$success = false;
				array_push($errors, 'Invalid recovery key or blocked user.');
			}
			
			$result = new JsonModel(array(
					'success' 		=> $success,
					'redirectTo' 	=> $route,
					'messages' 		=> $errors
			));
			return $result;
			
		} else {
			$viewData = array();

			if ($user = $this->getServiceLocator()->get('Core\Service\UserService')->findByRecoveryId($recovery)) {
			    $recoverForm = new RecoverForm($this->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
			    $recoverForm->bind($user);
			    
			    $viewData['form'] = $recoverForm;				

			} else {
			    $viewData['errors'] = 'Invalid recovery key.';
			}

			$layout = $this->layout();
			$layout->setTemplate('layout/layout');

			$view = new ViewModel($viewData);
			$view->setTemplate('office/forgot/index');
			return $view;
			
		}
	}
	
	private function getModule($module) {
	    
	    if ($module == Constants::OFFICE_MODULE) {
	        return $module;
	    }
	    return Constants::PATIENT_MODULE;
	}
	
}
?>