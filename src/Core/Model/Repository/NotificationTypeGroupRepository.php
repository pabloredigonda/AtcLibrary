<?php
namespace Core\Model\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class NotificationTypeGroupRepository
 * @package Core\Model\NotificationTypeGroup
 */
class NotificationTypeGroupRepository extends AbstractRepository
{

	public function findCustomizable()
	{
		$qb = $this->createQueryBuilder('o');
		$qb->andWhere($qb->expr()->neq('o.enableSetting', 0));
		
		return $qb->getQuery()->getResult();
	}
    
}
