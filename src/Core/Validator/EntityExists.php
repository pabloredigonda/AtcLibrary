<?php
namespace Core\Validator;

use Core\Validator\SMValidator;

class EntityExists extends SMValidator
{
    const NOT_EXISTS = 'not_exists';
    
    protected $messageTemplates = array(
        self::NOT_EXISTS => "Entity with id '%value%' does not exist"
    );
	
	
	/**
	 * Validate if an entity exists
	 * @param entityId
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 */
    public function isValid( $entityId )
	{
        $entity = $this->getService()->findById($entityId);
        if ($entity) {
            return true;		  
		}
		
		$this->error(self::NOT_EXISTS);
		return false;
	}	
}

