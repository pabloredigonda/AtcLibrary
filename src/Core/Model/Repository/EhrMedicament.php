<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;

/**
 * EhrMedicament
 */
class EhrMedicament extends AbstractRepository {

    public function findCurrentMedicament($patient) {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.ehr', 'e')
           ->andWhere($qb->expr()->eq('e.patient', ':patient'))
           ->andWhere($qb->expr()->eq('e.draft', ':draft'))
           ->andWhere($qb->expr()->orX(               
               $qb->expr()->eq('o.systemPeriodicity', ':systemPeriodicity'),
               $qb->expr()->gte('o.endDate', ':date')
           ));
        $qb->setParameter('systemPeriodicity', \Core\Model\SystemMedicamentPeriodicity::CHRONIC);
        $qb->setParameter('patient', $patient);
        $qb->setParameter('draft', false);
        $qb->setParameter('date', new \Datetime);
        
        return $qb->getQuery()->getResult();
    }
    
    public function findPastMedicament($patient) {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.ehr', 'e')
           ->andWhere(
               $qb->expr()->neq('o.systemPeriodicity', ':systemPeriodicity'),
               $qb->expr()->eq('e.patient', ':patient'),
               $qb->expr()->eq('e.draft', ':draft')
           )
           ->andWhere($qb->expr()->orX(
               $qb->expr()->lt('o.endDate', ':date'),
               $qb->expr()->isNull('o.endDate')
           ));
        $qb->setParameter('systemPeriodicity', \Core\Model\SystemMedicamentPeriodicity::CHRONIC);
        $qb->setParameter('patient', $patient);
        $qb->setParameter('draft', false);
        $qb->setParameter('date', new \Datetime);
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the recipes by staff_id
     *
     * @param Integer $staff_id Staff ID (optional)
     *
     * @return Array
     */
    public function getMedicamentsByStaffId($staff_id = null)
    {
        $sql = "SELECT * FROM ehr_medicament ehrm
                INNER JOIN ehr ehr ON ehr.ehr_id = ehrm.ehr_id
        ";

        if ($staff_id) {
            $sql .= "
                WHERE ehr.staff_id = " . $staff_id . "
            ";
        }
        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }
}