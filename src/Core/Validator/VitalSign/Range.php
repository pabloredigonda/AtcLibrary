<?php
namespace Core\Validator\VitalSign;

use Core\Validator\SMValidator;

class Range extends SMValidator
{
	const NOT_BETWEEN        = 'notBetween';
	const INVALID_VITAL_SIGN = 'invalidVitalSign';
	
	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $messageTemplates = array(
	    self::NOT_BETWEEN        => "The input is not between '%min%' and '%max%', inclusively",
	    self::INVALID_VITAL_SIGN => "the vital sign is not valid"
	);
	
	/**
	 * Additional variables available for validation failure messages
	 *
	 * @var array
	*/
	protected $messageVariables = array(
	    'min' => array('options' => 'min'),
	    'max' => array('options' => 'max'),
	);
	
	/**
	 * Options for the between validator
	 *
	 * @var array
	*/
	protected $options = array(
	    'min'       => 0,
	    'max'       => PHP_INT_MAX,
	);
	
	
	/**
	 * Validates if a Patient is enabled to send a message to a OfficeStaff
	 * @param int $patientId
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 * @return bool 
	 */
	public function isValid( $value )
	{
	    $vitalSign = $this->getService()->findById($this->getOption("vitalSign"));
	    
	    if(!$vitalSign){
	        $this->error(self::INVALID_VITAL_SIGN);
	        return false;
	    }
	    
	    $this->options['min'] = $vitalSign->getMin();
	    $this->options['max'] = $vitalSign->getMax();
		
		if( $value < $vitalSign->getMin() || $value > $vitalSign->getMax()){
			$this->error(self::NOT_BETWEEN);
			return false;
		}
		
		return true; 
	}
	
}

?>