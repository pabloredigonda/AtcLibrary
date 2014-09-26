<?php

namespace Core\Exception;

use Core\Model\Users;

class BlockedUserException extends SmException {
	
    protected $user;
    
    public function __construct($message = null, Users $user) {
    	$this->user = $user;
    	$this->message = $message;
    }
	
	public function getUser()
    {
        return $this->user;
    }
    
}

?>