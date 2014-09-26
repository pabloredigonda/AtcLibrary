<?php
namespace Core\Model\Repository;

/**
 * UserAppointment
 */

class UserAppointment extends AbstractRepository {

    public function findPendingByUserArray($user) {
        $qb = $this->createQueryBuilder('o');
        $qb->andWhere($qb->expr()->eq('o.user', ':user'));
    
        $qb->setParameter('user', $user);
        return $qb->getQuery()->getResult();
    }
    
}