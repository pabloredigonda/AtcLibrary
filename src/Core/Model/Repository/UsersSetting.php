<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;

/**
 * Users setting
 */

class UsersSetting extends AbstractRepository {

    public function findByUser($user) {
        
        return $this->findOneBy(array('user' => $user));
    }
}

?>