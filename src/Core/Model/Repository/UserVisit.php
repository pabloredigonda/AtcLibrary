<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;

/**
 * UserVisit
 */

class UserVisit extends AbstractRepository {
    
    public function findPendingByUserArray($user) {
        $today = new \Datetime();
    
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.patient', 'p')
           ->innerJoin('o.status', 's')
           ->andWhere($qb->expr()->eq('p.user', ':user'))
           ->andWhere($qb->expr()->eq('s.watch', ':watch'))
           ->andWhere($qb->expr()->isNotNull('o.date'))
           ->andWhere($qb->expr()->gte('o.date', ':date'));
    
        $qb->setParameter('user', $user);
        $qb->setParameter('watch', true);
        $qb->setParameter('date', $today);
        return $qb->getQuery()->getResult();
    
    }
    
} 