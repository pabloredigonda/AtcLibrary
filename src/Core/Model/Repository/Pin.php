<?php
namespace Core\Model\Repository;

use Core\Model\SystemPinStatus;
use Doctrine\ORM\Query\ResultSetMapping;
/**
 * Pin
 */

class Pin extends AbstractRepository
{ 
	public function getDefault( $country )
	{
		$qb = $this->createQueryBuilder('o');
		
		$qb->innerJoin('o.countries', 'c')
			->where($qb->expr()->eq('o.status', ':status'))
			->andWhere($qb->expr()->eq('o.default', ':default'))
// TODO uncomment this when default pin by country is needed
// 			->andWhere($qb->expr()->eq('c.id', ':country'))
			->setParameter('status', SystemPinStatus::ACTIVE)
			->setParameter('default', true)
// TODO uncomment this when default pin by country is needed
// 			->setParameter('country', $country)
			->setMaxResults(1);
		
		return $qb->getQuery()->getOneOrNullResult();
	}
	
	public function getFullInfo( $groupId, $pinIds )
	{
	    $whereKey = $groupId ? 'pin_group_id' : 'pin_id';
	    
	    if($groupId){
	        $whereKey = " p.pin_group_id = ? ";
	        $whereVal = $groupId;
	    }else{
	        $whereKey = " p.pin_id IN(?) ";
	        $whereVal = $pinIds;
	    }
	    
	    $sql = "
            SELECT
    	    p.pin_id,
    	    p.pin_group_id,
    	    p.status,
    	    op.pin_taken_date,
    	    o.name AS office_name,
    	    (u.first_name || ' ' || u.last_name ) as user_name
    	
    	    FROM pin p
    	    LEFT JOIN office_pin op ON op.pin_id = p.pin_id
    	    LEFT JOIN office o ON op.office_id = o.office_id
    	    LEFT JOIN users u ON op.user_id = u.user_id
    	    WHERE {$whereKey}";
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('pin_id', 'id');
	    $rsm->addScalarResult('pin_group_id', 'groupId');
	    $rsm->addScalarResult('status', 'status');
	    $rsm->addScalarResult('pin_taken_date', 'takenDate');
	    $rsm->addScalarResult('office_name', 'officeName');
	    $rsm->addScalarResult('user_name', 'userName');
	     
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter(1, $whereVal);
	     
	    return $query->getArrayResult();
	}
	
	public function updateStatus( $pinIds, $status)
	{
	    $conn = $this->getEntityManager()->getConnection();
	    $stmt = $conn->prepare("UPDATE pin SET status = ? WHERE pin_id = ?");
	    
	    foreach ( $pinIds as $pinId){
	        $stmt->bindValue(1, $status, \PDO::PARAM_STR);
	        $stmt->bindValue(2, $pinId, \PDO::PARAM_STR);
	        $stmt->execute();
	    }
	}
}

?>