<?php
namespace Core\Validator\User;

use Core\Validator\SMValidator;

class EmailExists extends SMValidator
{
	const EXISTS = 'exists';
	
	protected $messageTemplates = array(
		self::EXISTS => "'%value%' already exists"
	);
	
	/**
	 * Validates if exists an emaiol. If exists returns false, else true
	 * @param int $documentNumber
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 * @return bool 
	 */
	public function isValid( $email )
	{
		$userId		= $this->hasOption("userId") ? $this->getOption("userId") : false;
		
		if ($userId) {
			$user = $this->getService()->findById($userId);
		}
		
		$exists = $this->getService()->existsDocumentNumber(
			$email, 
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