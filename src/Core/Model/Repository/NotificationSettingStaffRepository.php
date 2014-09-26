<?php
namespace Core\Model\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class NotificationSettingStaffRepository
 * @package Core\Model\NotificationSettingStaff
 */
class NotificationSettingStaffRepository extends EntityRepository
{
    
	public function deleteByStaff($staff_id) {
		$conn = $this->getEntityManager()->getConnection();
		$sql  = "DELETE FROM notification_setting_staff WHERE staff_id = '" . $staff_id . "'";
		$conn->executeUpdate($sql);
	}

	public function isEnabled($staff, $notificationType)
	{
		$qb = $this->createQueryBuilder('o');
		$qb->select('count(o.id)')
		->andWhere($qb->expr()->eq('o.staff', ':staff'))
		->andWhere($qb->expr()->eq('o.notificationType', ':notificationType'));
		$qb->setParameter('staff', $staff);
		$qb->setParameter('notificationType', $notificationType);
	
		return $qb->getQuery()->getSingleScalarResult() ? false : true;
	}
	
}
