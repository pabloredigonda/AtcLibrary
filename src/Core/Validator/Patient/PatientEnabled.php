<?php
namespace Core\Validator\Patient;

use Core\Validator\SMValidator;
use Core\Model\Patient;

class PatientEnabled extends SMValidator
{
	const NOT_ENABLED = 'not_enabled';
	
	protected $messageTemplates = array(
		self::NOT_ENABLED => ""
	);
	
	/**
	 * Validates if a Patient is enabled to send a message to a OfficeStaff
	 * @param int $patientId
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 * @return bool 
	 */
	public function isValid( $recipientId )
	{
		$patientId = $this->hasOption("patient") ? $this->getOption("patient") : false;
		$staffId = $this->hasOption("staff") ? $this->getOption("staff") : false;
		$enabled = $this->getService()->isEnabledForMessage($patientId, $staffId);
		
		if( !$enabled ){
			$this->error(self::NOT_ENABLED);
			return false;
		}
		
		return true; 
	}

	public function __construct($options) {
	    parent::__construct($options);
	    $this->setMessage($this->getTranslator()->translate('El destinatario no está habilitado para recibir mensajes'));
	}
}

?>