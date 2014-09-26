<?php
namespace Core\Model\Repository;

/**
 * OfficePin
 */

class OfficePin extends AbstractRepository
{
	public function findByPinAndOffice($pin, $office)
	{
		return $this->findOneBy(array(
			'pin' => $pin,
			'office' => $office
		));
	}
	
	public function countPinUses($pin, $office)
	{
		$qb = $this->createQueryBuilder('o');
		$qb->select('count(o.id)')
			->where($qb->expr()->eq('o.pin', ':pin'))
			->setParameter('pin', $pin);
		
		if($office){
			$qb->andWhere($qb->expr()->neq('o.office', ':office'));
			$qb->setParameter('office', $office);
		}
		
		return $qb->getQuery()->getSingleScalarResult();
	}
}