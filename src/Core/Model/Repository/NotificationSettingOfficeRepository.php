<?php
namespace Core\Model\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class NotificationSettingOfficeRepository
 * @package Core\Model\NotificationSettingOffice
 */
class NotificationSettingOfficeRepository extends EntityRepository
{

	public function deleteByOffice($office_id) {
		$conn = $this->getEntityManager()->getConnection();
		$sql  = "DELETE FROM notification_setting_office WHERE office_id = '" . $office_id . "'";
		$conn->executeUpdate($sql);
	}
    
	public function isEnabled($office, $notificationType)
	{
		$qb = $this->createQueryBuilder('o');
        $qb->select('count(o.id)')
           ->andWhere($qb->expr()->eq('o.office', ':office'))
           ->andWhere($qb->expr()->eq('o.notificationType', ':notificationType'));
        $qb->setParameter('office', $office);
        $qb->setParameter('notificationType', $notificationType);
    
        return $qb->getQuery()->getSingleScalarResult() ? false : true;
	}
	
}
