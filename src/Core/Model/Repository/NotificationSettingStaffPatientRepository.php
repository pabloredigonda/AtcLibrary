<?php
namespace Core\Model\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class NotificationSettingStaffPatientRepository
 * @package Core\Model\NotificationSettingStaffPatient
 */
class NotificationSettingStaffPatientRepository extends AbstractRepository
{   
	public function findDisabled($staff, $patient)
	{
        $qb = $this->createQueryBuilder('o');
        $qb->andWhere($qb->expr()->eq('o.staff', ':staff'))
           ->andWhere($qb->expr()->eq('o.patient', ':patient'))
           ->andWhere($qb->expr()->eq('o.enabled', ':enabled'));
        $qb->setParameter('staff', $staff);
        $qb->setParameter('patient', $patient);
        $qb->setParameter('enabled', false);
        
        return $qb->getQuery()->getResult();
	}

	public function findByStaffAndPatient($staff, $patient)
	{
		$qb = $this->createQueryBuilder('o');
		$qb->andWhere($qb->expr()->eq('o.staff', ':staff'))
		   ->andWhere($qb->expr()->eq('o.patient', ':patient'));
		$qb->setParameter('staff', $staff);
		$qb->setParameter('patient', $patient);
	
		return $qb->getQuery()->getResult();
	}
	
	public function deleteByStaffAndPatient($staff_id, $patient_id)
	{
		$conn = $this->getEntityManager()->getConnection();
		$sql  = "DELETE FROM notification_setting_staff_patient WHERE staff_id = '" . $staff_id . "' AND patient_id = '" . $patient_id . "'";
		$conn->executeUpdate($sql);
	}

	public function checkStaffAndPatient($staff, $patient, $notificationType)
	{
		$qb = $this->createQueryBuilder('o');
		$qb->andWhere($qb->expr()->eq('o.staff', ':staff'))
		   ->andWhere($qb->expr()->eq('o.patient', ':patient'))
		   ->andWhere($qb->expr()->eq('o.notificationType', ':notificationType'));
		$qb->setParameter('staff', $staff);
		$qb->setParameter('patient', $patient);
		$qb->setParameter('notificationType', $notificationType);
		
		return $qb->getQuery()->getResult();
	}
	
}
