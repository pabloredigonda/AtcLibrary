<?php
namespace Core\Model\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class NotificationSettingOfficePatientRepository
 * @package Core\Model\NotificationSettingOfficePatient
 */
class NotificationSettingOfficePatientRepository extends EntityRepository
{

	public function checkOfficeAndPatient($office, $patient)
	{
		$qb = $this->createQueryBuilder('o');
		$qb->select('count(o.id)')
		   ->andWhere($qb->expr()->eq('o.office', ':office'))
		   ->andWhere($qb->expr()->eq('o.patient', ':patient'));
		$qb->setParameter('office', $office);
		$qb->setParameter('patient', $patient);
		
		return $qb->getQuery()->getSingleScalarResult() ? false : true;
	}
    
}
