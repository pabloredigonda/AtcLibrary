<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;
/**
 * EhrComment
 */

class EhrComment extends AbstractRepository {

    public function findByPatientAndStaff($patient, $staff) {
        $qb = $this->createQueryBuilder('c');
        $qb->innerJoin('c.ehr', 'e')
           ->andWhere($qb->expr()->eq('e.patient', ':patient'))
           ->andWhere($qb->expr()->eq('e.staff', ':staff'))
           ->andWhere($qb->expr()->eq('e.draft', ':draft'));
        $qb->setParameter('patient', $patient);
        $qb->setParameter('staff', $staff);
        $qb->setParameter('draft', false);
    
        return $qb->getQuery()->getResult();
    }
    
    public function findByEhrs($ehr_ids) {
        $qb = $this->createQueryBuilder('c');
        $qb->andWhere($qb->expr()->in('c.ehr', ':ehr_ids'))
           ->andWhere($qb->expr()->neq('c.comment', ':empty'));
        $qb->setParameter('ehr_ids', $ehr_ids); 
        $qb->setParameter('empty', '');
        return $qb->getQuery()->getResult();
    }
    
}

?>