<?php
namespace Core\Helper;

use Zend\Math\Rand;
use Zend\Crypt\BlockCipher;

class HashHelper {
    
    public static function create() {
        $salt = Rand::getBytes(1024, true);
        return md5($salt);
    }
    
    public static function password( $password ) {
        return md5($password);
    }
    
    public static function encryptPatientId( $patientId ) {
        return self::_getEncryptor()->encrypt( (string) $patientId );
    }
    
    public static function decryptPatientId( $hash ) {
        return self::_getEncryptor()->decrypt( $hash );
    }
    
    protected static function _getEncryptor( ) {
        $blockCipher = BlockCipher::factory(
            'mcrypt', 
             array(
                'algo' => 'blowfish',
                 'mode' => 'cfb',
                 'hash' => 'sha512')
        );
        
        $blockCipher->setKey('email_unsubscribe_key');
        return $blockCipher;
    }
	
}

?>