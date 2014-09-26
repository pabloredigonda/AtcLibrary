<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * OfficeStaffPatient
 */

class OfficeStaffPatient extends AbstractRepository {

    public function matchStaffIdsAndPatient($staff_ids, $patient) {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->in('officeStaff', $staff_ids))
                 ->andWhere($criteria->expr()->eq('patient', $patient));
        return $this->matching($criteria)->count() > 0 ? true : false;
    }
    
    public function matchStaffAndUser($staff, $user) {
        $qb = $this->createQueryBuilder('o');
        $qb->select('count(o.id)')
           ->innerJoin('o.patient', 'p')
           ->andWhere($qb->expr()->eq('o.officeStaff', ':staff'))
           ->andWhere($qb->expr()->eq('p.user', ':user'));
        
        $qb->setParameter('staff', $staff->getId());
        $qb->setParameter('user', $user->getId());

        return $qb->getQuery()->getSingleScalarResult() ? true : false;
    }
    
    public function countMessageEnabled($userId, $staffId) {
    
        $sql  = ' SELECT count(*) as total ';
        $sql .= '   FROM office_staff_patient osp ';
        $sql .= '  INNER JOIN patient p ON osp.patient_id = p.patient_id ';
        $sql .= '  WHERE p.user_id = :user_id ';
        $sql .= '    AND osp.staff_id = :staff_id ';
        $sql .= '    AND osp.message_enabled = true ';
    
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total', 'total');
    
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('user_id', $userId);
        $query->setParameter('staff_id', $staffId);
        return $query->getSingleScalarResult();
    }
    
    public function getPictures($patient_userId, $staff_userId) {
        
        $qb = $this->createQueryBuilder('osp');
        $qb->select('os.picture as staff_picture')
           ->addSelect('p.picture as patient_picture')
           ->innerJoin('osp.patient', 'p')
           ->innerJoin('osp.officeStaff', 'os')
           ->andWhere($qb->expr()->eq('p.user', ':patient_user'))
           ->andWhere($qb->expr()->eq('os.user', ':staff_user'));
        $qb->setParameter('patient_user', $patient_userId);
        $qb->setParameter('staff_user', $staff_userId); 
        $res = $qb->getQuery()->getResult();
        return $res;
    }
    
}