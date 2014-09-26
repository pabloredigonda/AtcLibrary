<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;
/**
 * UserProfessional
 */

class UserProfessional extends AbstractRepository {

    public function findByUser($user) {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('user', $user))
                 ->orderBy(array("lastName" => Criteria::ASC, "firstName" => Criteria::ASC, "id" => Criteria::ASC));
        return $this->matching($criteria);
    }
    
    public function findUserProfessionalArray($user) {
        return $this->findByQueryBuilder(array('user' => $user));
    }

    /**
     * findAllByUser
     *
     * @param \Core\Model\Users $user Users object
     *
     * @return mixed
     */
    public function findAllByUser($user)
    {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('user', $user));
        return $this->matching($criteria);
    }
}

?>