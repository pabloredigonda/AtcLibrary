<?php
namespace Core\Validator;

use Core\Validator\SMValidator;
use Core\Helper\HashHelper;

class ChangePassword extends SMValidator
{
	const INVALID_CURRENT_PASSWORD = 'currentPassword';
	const NEW_PASSWORD_NOT_MATCH = 'newPassword';
	
	protected $messageTemplates = array();
	
	public function __construct($options) {
	    $translator = $this->getTranslator();
	     
	    $this->messageTemplates[self::INVALID_CURRENT_PASSWORD] = $translator->translate("La contraseña actual no es válida");
	    $this->messageTemplates[self::NEW_PASSWORD_NOT_MATCH] = $translator->translate("La nueva contraseña no coincide con la confirmación");
	     
	    parent::__construct($options);
	}
	
	/**
	 * Validates if the current password match with the user's password
	 * Validates if new password AND new password2 matches
	 * @param strind $patientId
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 * @return bool 
	 */
	public function isValid( $newPassword )
	{
	    $user = $this->getOption("user");
		$currentPassword = $this->getOption("currentPassword");
		$newPassword2 = $this->getOption("newPassword2");
		
		//Current password validation
		if( $user->getPassword() != HashHelper::password($currentPassword) ){
			$this->error(self::INVALID_CURRENT_PASSWORD);
			return false;
		}
		
		//Current password validation
		if( $newPassword2 != $newPassword ){
		    $this->error(self::NEW_PASSWORD_NOT_MATCH);
		    return false;
		}
		
		return true; 
	}
}

