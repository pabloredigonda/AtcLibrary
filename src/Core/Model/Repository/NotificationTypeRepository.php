<?php
namespace Core\Model\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class NotificationTypeRepository
 * @package Core\Model\NotificationType
 */
class NotificationTypeRepository extends AbstractRepository
{
    public function findEnableSetting()
    {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.group', 'g', \Doctrine\ORM\Query\Expr\Join::WITH, 'g.enableSetting > 0');
        
        return $qb->getQuery()->getResult();
    }
    
    public function isEnabledSetting( $notificationId )
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('count(o.id)');
        $qb->innerJoin('o.group', 'g', \Doctrine\ORM\Query\Expr\Join::WITH, 'g.enableSetting > 0');
        $qb->where($qb->expr()->eq('o.id', ':id'));
        $qb->setParameter('id', $notificationId);
    
        return (bool) $qb->getQuery()->getSingleScalarResult();
    }

    public function countEnabled()
    {
    	$qb = $this->createQueryBuilder('o');
        $qb->select('count(o.id)');
        $qb->innerJoin('o.group', 'g', \Doctrine\ORM\Query\Expr\Join::WITH, 'g.enableSetting > 0');

        return $qb->getQuery()->getSingleScalarResult();
    }
}
