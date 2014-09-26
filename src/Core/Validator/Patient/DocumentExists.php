<?php
namespace Core\Validator\Patient;

use Core\Validator\SMValidator;

class DocumentExists extends SMValidator
{
	const EXISTS = 'exists';
	
	protected $messageTemplates = array(
		self::EXISTS => "'%value%' already exists"
	);
	
	/**
	 * Validates if exists a document. If exists returns false, else true
	 * @param int $documentNumber
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 * @return bool 
	 */
	public function isValid( $documentNumber )
	{
		$patientId		= $this->hasOption("patientId") ? $this->getOption("patientId") : false;
		$officeId		= $this->hasOption("officeId") ? $this->getOption("officeId") : false;
		$office			= $this->hasOption("office") ? $this->getOption("office") : false;
		$documentType	= $this->hasOption("documentType") ? $this->getOption("documentType") : false;
		
		if ($patientId) {
			$patient = $this->getService()->findById($patientId);
			if ($patient) {
				
				if(!$office){
					$office = $patient->getOffice();
				}
				
				if(!$documentType){
					$documentType = $patient->getDocumentType();
				}
			}
		}
		
		$exists = $this->getService()->existsDocumentNumber(
			$documentNumber, 
			$documentType, 
			$patientId, 
			$office
		);
		
		if( $exists ){
			$this->error(self::EXISTS);
			return false;
		}
		
		return true; 
	}
}

?>