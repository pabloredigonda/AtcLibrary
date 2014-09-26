<?php
namespace Core\Validator;

use Core\Validator\SMValidator;
use Core\Model\SystemPinStatus;

class Pin extends SMValidator
{
	const PIN_INVALID = 'pin_invalid';
	const PIN_INACTIVE = 'pin_inactive';
	const DATE_EARLY = 'date_early';
	const DATE_EXCEEDED = 'date_exceeded';
	const DAYS_EXCEEDED = 'days_exceeded';
	const USES_EXCEEDED = 'uses_exceeded';
	const INVALID_COUNTRY = 'invalid_country';
	const INVALID_SPECIALTY = 'invalid_specialty';
	
	protected $messageTemplates = array();
	
	public function __construct($options) {
    	$translator = $this->getTranslator();
    	
    	$this->messageTemplates[self::PIN_INVALID] = $translator->translate("El pin no es válido");
    	$this->messageTemplates[self::PIN_INACTIVE] = $translator->translate("El pin no está activo");
    	$this->messageTemplates[self::DATE_EARLY] = $translator->translate("El pin todavía no esta disponible");
    	$this->messageTemplates[self::DATE_EXCEEDED] = $translator->translate("El pin ha caducado");
    	$this->messageTemplates[self::DAYS_EXCEEDED] = $translator->translate("El pin ha caducado");
    	$this->messageTemplates[self::USES_EXCEEDED] = $translator->translate("El pin ha superado la cantidad máxima de usos permitidos");
    	$this->messageTemplates[self::INVALID_COUNTRY] = $translator->translate("El pais del consultorio no pertenece a los paises del pin");
    	$this->messageTemplates[self::INVALID_SPECIALTY] = $translator->translate("El consultorio no tiene una de las especialidades requeridas por el pin");
    	
    	parent::__construct($options);
	}
	
	/**
	 * Validates if a Patient is enabled to send a message to a OfficeStaff
	 * @param int $patientId
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 * @return bool 
	 */
	public function isValid( $pinId )
	{
		$sm = $this->getOption("sm");
		$office = $this->getOption("office");
		$pinService = $sm->get('Core\Service\PinService');
		$officePinService = $sm->get('Core\Service\OfficePinService');
		$specialtyService = $sm->get('Core\Service\SpecialtyService');
		
		$pin		= $pinService->findById($pinId);
		
		//validates pin exists
		if(!$pin){
			return $this->_error(self::PIN_INVALID);
		}
		
		//validates pin status
		if($pin->getStatus()->getStatus() != SystemPinStatus::ACTIVE ){
			return $this->_error(self::PIN_INACTIVE);
		}
		
		$now = new \DateTime();
		
		//validate start date
		if($pin->getStartDate() && $pin->getStartDate() > $now ){
		    return $this->_error(self::DATE_EARLY);
		}
		
		//validate end date
		if($pin->getEndDate() && $pin->getEndDate() < $now ){
		    return $this->_error(self::DATE_EXCEEDED);
		}
		
		//validate days
		if($pin->getMaxDays()){
			
			$officePin = $officePinService->findByPinAndOffice($pin, $office);
			
			if($officePin){
				
				$endDate = clone $officePin->getTakenDate();
				
				$maxDays = $pin->getMaxDays();
				$endDate->add( new \DateInterval('P'.$maxDays.'D') );
				
				if( $now > $endDate ){
				    return $this->_error(self::DAYS_EXCEEDED);
				}
			}
		}
		
		//validates uses
		if( $pin->getMaxOffices() ){
			
			$numUses = $officePinService->countPinUses($pin, $office);
			if( $numUses >= $pin->getMaxOffices() ){
			    return $this->_error(self::USES_EXCEEDED);
			}
		}
		
		//validate country
		$countries = $pin->getCountries();
		if(!$countries->isEmpty() && !$countries->contains( $office->getAddressCountry() )){
			return $this->_error(self::INVALID_COUNTRY);
		}
		
		//@TODO validate specialty
		$specialties = $pin->getSpecialties();
		
		if(!$specialties->isEmpty()){
			
			$officeSpecialties = $specialtyService->findByOffice($office);
			$hasSpecialty = false;
			
			foreach ($officeSpecialties as $specialty){
				if( $specialties->contains( $specialty ) ){
					$hasSpecialty = true;
					break;
				}
			}
			
			if(!$hasSpecialty){
				return $this->_error(self::INVALID_SPECIALTY);
			}
		}
		
		return true; 
	}
	
	private function _error( $code )
	{
		$this->error($code);
		return false;
	}
}

?>