<?php
namespace Core\Validator\User;

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
		$userId		= $this->hasOption("userId") ? $this->getOption("userId") : false;
		$documentType	= $this->hasOption("documentType") ? $this->getOption("documentType") : false;
		
		if ($userId) {
			$user = $this->getService()->findById($userId);
			if ($user) {
				
				if(!$documentType){
					$documentType = $user->getDocumentType();
				}
			}
		}
		
		$exists = $this->getService()->existsDocumentNumber(
			$documentNumber, 
			$documentType, 
			$userId
		);
		
		if( $exists ){
			$this->error(self::EXISTS);
			return false;
		}
		
		return true; 
	}
}

?>