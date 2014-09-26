<?php

namespace Core\Service;

use Core\Model\Office;
use Doctrine\Common\Collections\Criteria;
use Core\Validator\Pin as PinValidator;

class OfficeService extends AbstractService implements Service {

	public function getClassName() {
		return get_class(new Office());		
	}	
	
	/* Deprecated */
	public function findByUserAndOffice($user, $office) {
		return $this->findByUser($user);
	}

	public function findByUser($user) {
		$criteria = new Criteria();
		$criteria->andWhere($criteria->expr()->eq('user', $user))
		         ->setMaxResults(1);
		return $this->queryByCriteria($criteria);
	}
	
	
	public function save($office, $options = null)
	{
		parent::save($office, $options);

		$user = isset($options['user']) ? $options['user'] : null;
		
		//Each office has a pin that enables or disables some features
		if(!$office->getOfficePin()){
			return $this->setDefaultPin($office, $user);
		}
		return $office;
	}
	
	public function setPin($office, $pinId, $user)
	{
		//Set default pin
		if(empty($pinId)){
			return $this->setDefaultPin($office, $user);
		}
		
		$officePin = $office->getOfficePin();
		//Office already has the pin
		if($officePin && $officePin->getPin()->getId() == $pinId ){
			return;
		}
		
		$pinService = $this->getServiceManager()->get('Core\Service\PinService');
		$officePinService = $this->getServiceManager()->get('Core\Service\OfficePinService');
			
		$pin = $pinService->findById( $pinId );
		$oldOfficePin = $officePinService->findByPinAndOffice($pin, $office);
		
		if($oldOfficePin){
			
			return $this->_setOfficePinAndSave($office, $oldOfficePin);
		}
		
		$officePin = $this->_createOfficePin($office, $pin, $user);
		return $this->_setOfficePinAndSave($office, $officePin);
	}
	
	public function setDefaultPin( $office, $user)
	{
		$pinService = $this->getServiceManager()->get('Core\Service\PinService');
		$officePinService = $this->getServiceManager()->get('Core\Service\OfficePinService');
			
		$pin = $pinService->getDefault( $office->getAddressCountry()->getId() );
		$officePin = $officePinService->findByPinAndOffice($pin, $office);
			
		if(!$officePin){
		    $officePin = $this->_createOfficePin($office, $pin, $user);
		}
		
		return $this->_setOfficePinAndSave($office, $officePin);
	}
	
	public function validatePin( $office, $user )
	{
		$validator = new PinValidator(array(
				'sm' => $this->getServiceManager(),
				'office' => $office,
				'service' => $this
		));
		
		$pinId = $office->getOfficePin() ? $office->getOfficePin()->getPin()->getId() : null; 
		
		if( !$pinId || !$validator->isValid( $pinId ) ){
			$this->setDefaultPin($office, $user);
		}
	}
	
	private function _createOfficePin( $office, $pin, $user )
	{
	    $officePinService = $this->getServiceManager()->get('Core\Service\OfficePinService');
	    	
        $officePin = new \Core\Model\OfficePin();
        $officePin->setPin($pin);
        $officePin->setOffice($office);
        //Both pin and office pin uses the same status entity
        //default pin is always active
        $officePin->setStatus($pin->getStatus());
        $officePin->setTakenDate(new \DateTime());
//         $officePin->setUser($user);
        $officePin->setUser($this->getEntityManager()->getReference('Core\Model\Users', $user->getId()));
        $officePinService->save($officePin);
	    	
	    return $officePin;
	}
	
	private function _setOfficePinAndSave( $office, $officePin )
	{
	    $office->setOfficePin($officePin);
	    return $this->save($office);;
	}
	
    public function findNotCurrentOffices($user, $office) {
        $status = $this->getServiceManager()->get('Core\Service\SystemUsersStatusService')->findById('active');
        return $this->getRepository()->findNotCurrentOffices($user, $office, $status);
    }
	
}