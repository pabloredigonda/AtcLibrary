<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;
/**
 * EhrVitalSign
 */

class EhrVitalSign extends AbstractRepository {
    
    public function findByPatient($patient) {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.ehr', 'e')
           ->andWhere($qb->expr()->eq('e.patient', ':patient'));
        $qb->orderBy('o.date', 'ASC');
        $qb->setParameter('patient', $patient);
    
        return $qb->getQuery()->getResult();
    }
    
}

?>