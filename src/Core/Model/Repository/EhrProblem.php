<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;

/**
 * EhrProblem
 */

class EhrProblem extends AbstractRepository {

    public function findChronicByPatient($patient) {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.ehr', 'e')
           ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('o.status', ':status_chronic'),
                $qb->expr()->eq('o.status', ':status_resolved')))
           ->andWhere($qb->expr()->eq('e.patient', ':patient'))
           ->orderBy('o.date', 'DESC')
           ->addOrderBy('o.id', 'DESC');
        $qb->setParameter('status_chronic', \Core\Model\SystemProblemStatus::CHRONIC_ACTIVE);
        $qb->setParameter('status_resolved', \Core\Model\SystemProblemStatus::RESOLVED);
        $qb->setParameter('patient', $patient);
        
        /*
        $qb = $this->createQueryBuilder('o');
        $qb->select('DISTINCT o.problem')
           ->innerJoin('o.ehr', 'e')
           ->andWhere($qb->expr()->eq('o.status', ':status'))
           ->andWhere($qb->expr()->eq('e.patient', ':patient'));
        $qb->setParameter('status',  \Core\Model\SystemProblemStatus::CHRONIC_ACTIVE);
        $qb->setParameter('patient', $patient);
        */
        
        return $qb->getQuery()->getResult();
    }
}

?>