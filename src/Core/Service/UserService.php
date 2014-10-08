<?php

namespace Core\Service;
use Core\Model\User;
use Core\Helper\HashHelper;

class UserService extends AbstractService implements Service {
	
	public function getClassName() {
		return get_class(new User());
	}
	
	public function findByEmail($email) {
		return $this->findBy('email', $email);
	}
	
	public function existsDocumentNumber($documentNumber, $documentType = null, $userId = null) {
	    
	    $conditions = array('documentNumber' => $documentNumber);
	    if ($documentType) {
	        $conditions['documentType'] = $documentType;
	    }
	    $user = $this->findBy($conditions);
	    if ($user && $user->getId() != $userId) {
	        return true;
	    }
	    return false;
	}
	
	public function existsEmail($email, $userId = null) {
		$user = $this->findBy('email', $email);
		
		if ($user && $user->getId() != $userId) {
			return true;
		}
		return false;
	}
	
	public function findByRecoveryId($recoveryId) {
		return $this->findBy('passwordRecoveryKey', $recoveryId);
	}
	
	public function generateRecoveryKey($email, $module) {
	    
		if ($user = $this->findBy('email', $email)) {
	
			$key = base64_encode($module . '.' . HashHelper::create());
			
			$user->setPasswordRecoveryKey($key);
			$this->save($user);
			return $key;
		}
		return false;
	}
	
	public function recoverPassword($recovery, $password) {
		if ($user = $this->findByRecoveryId($recovery)) {
				
			$password = md5($password);
			$user->setPassword($password);
				
			// poorman's key invalidation
			$key = HashHelper::create();;
			$user->setPasswordRecoveryKey($key);
				
			$this->save($user);
			return true;
		}
		return false;
	}
}

?>