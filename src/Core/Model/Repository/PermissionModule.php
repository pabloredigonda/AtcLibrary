<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\ArrayCollection;
/**
 * PermissionModule
 */
class PermissionModule extends AbstractRepository
{

    public function findByPinId( $pinId )
    {
        $qb = $this->createQueryBuilder('m');
        $qb->innerJoin('Core\Model\PinModule', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'm.id = p.module');
        $qb->andWhere($qb->expr()->eq('p.pin', ':pin'));
        
        $qb->setParameter('pin', $pinId);

        return new ArrayCollection($qb->getQuery()->getResult());
    }
}