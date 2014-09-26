<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;
/**
 * Electronic Health Record
 */

class Ehr extends AbstractRepository {
    
    public function getDraft($staff_id, $patient_id) {
        
        $em = $this->getEntityManager();
        $staff_id = $em->getReference('Core\Model\OfficeStaff', $staff_id);
        $patient_id = $em->getReference('Core\Model\Patient', $patient_id);
        
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('staff', $staff_id))
                 ->andWhere($criteria->expr()->eq('patient', $patient_id))
                 ->andWhere($criteria->expr()->eq('draft', true));
        if ($result = $this->matching($criteria)){
            return $result[0];
        }
        return null;
    }
    
    public function findByPatient($patient) {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('patient', $patient))
                 ->andWhere($criteria->expr()->eq('draft', false))
                 ->orderBy(array('date' => Criteria::DESC, 'id' => Criteria::DESC));
        return $this->matching($criteria);
    }
    
    public function findByUser($user) {
    	$qb = $this->createQueryBuilder('o');
    	$qb->innerJoin('o.patient', 'p')
    	   ->andWhere($qb->expr()->eq('p.user', ':user'))
    	   ->andWhere($qb->expr()->eq('o.draft', ':draft'))
    	   ->orderBy('o.date', 'DESC')
    	   ->addOrderBy('o.id', 'DESC');

    	$qb->setParameter('user', $user);
    	$qb->setParameter('draft', false);
    	
    	return $qb->getQuery()->getResult();
    }
    
    public function isImportant($ehr) {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.problems', 'p')
           ->innerJoin('p.tags', 't')
           ->andWhere($qb->expr()->eq('o.id', ':id'))
           ->andWhere($qb->expr()->eq('t.tag', ':tag'));
        $qb->setParameter('id', $ehr);
        $qb->setParameter('tag', 'important');
    
        return $qb->getQuery()->getResult() ? true : false;
    }
    
}

?>