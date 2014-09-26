<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Class OfficeRepository
 * @package Core\Model\Repository
 */
class OfficeRepository extends EntityRepository
{
    /**
     * Returns the offices that are in a specific hour of the day
     * (ie: 00 o'clock)
     *
     * Usage mainly for cronjobs to run over offices at specific hours of the day
     *
     * @param $hour In format 00 - 24
     * @return Array result
     */
    public function getOfficeByCurrentTime($hour)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT *
            FROM office o
              INNER JOIN system_timezone st ON st.timezone_id = o.timezone
            WHERE extract(hour from (now() + (st.timezone_offset || ' hour')::interval)) = '" . $hour . "';
        ";
        return $conn->fetchAll($sql);
    }

    /**
     * getUsersByOffices
     *
     * @param Array $offices Offices ids
     *
     * @return array
     */
    public function getUsersByOffices($offices)
    {
        $sql = "
        SELECT u.user_id as id, * FROM users u
        INNER JOIN patient p ON p.user_id = u.user_id
        INNER JOIN office o ON o.office_id = p.office_id
        WHERE o.office_id IN ('" . join("', '", $offices) . "')
        ";

        return $this->getEntityManager()->getConnection()->fetchAll($sql);

//        $rsm = new ResultSetMapping();
//        $rsm->addEntityResult('Core\Model\Users', 's');
//        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
//
//        return $query->getArrayResult();
    }
    
    public function findNotCurrentOffices($user, $current_office, $status) {
    	$qb = $this->createQueryBuilder('o');
    	$qb->innerJoin('o.staff', 's')
    	   ->andWhere($qb->expr()->neq('o.id', ':office'))
    	   ->andWhere($qb->expr()->eq('s.user', ':user'))
    	   ->andWhere($qb->expr()->eq('s.status', ':status'))
    	   ->orderBy('o.name', 'ASC');
    	 
    	$qb->setParameter('user', $user);
    	$qb->setParameter('office', $current_office);
    	$qb->setParameter('status', $status);
    	 
    	return $qb->getQuery()->getResult();
    }
    
}
