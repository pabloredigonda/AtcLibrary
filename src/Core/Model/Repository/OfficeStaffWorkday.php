<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;

/**
 * OfficeStaffWorkday
 */

class OfficeStaffWorkday extends AbstractRepository {
    
    public function findByOfficeAndProfile($office, $status, $profile) {

        $qb = $this->createQueryBuilder('osw')->distinct();
        $qb->leftJoin('osw.staff', 's')
           ->innerJoin('s.profile', 'p')
    	   ->andWhere($qb->expr()->eq('s.office', ':office'))
    	   ->andWhere($qb->expr()->eq('s.status', ':status'))
           ->andWhere($qb->expr()->eq('p.id', ':profile'));

        $qb->setParameter('office', $office);
        $qb->setParameter('status', $status);
        $qb->setParameter('profile', $profile);
    	return $qb->getQuery()->getResult();
    }
}

?>