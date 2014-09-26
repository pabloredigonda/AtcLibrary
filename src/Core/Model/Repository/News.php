<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;
use Core\Util\Constants;
/**
 * News
 */

class News extends AbstractRepository {

    public function getNewsByStaff($staff, $profiles, $countries) {
        $date = new \Datetime();
        $timezone = new \DateTimeZone($staff->getOffice()->getTimezone());
        $date->setTimezone($timezone);
        
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('n')
           ->from($this->getClassName(), 'n')
           ->leftJoin('n.profile', 'p')
           ->leftJoin('n.country', 'c')
           ->where($qb->expr()->andX(
               $qb->expr()->orX(
                   $qb->expr()->eq('n.module', ':module'),
                   $qb->expr()->eq('n.module', ':empty')
               ),
               $qb->expr()->lte('n.fromDate', ':date'),
               $qb->expr()->orX(
                   $qb->expr()->gte('n.toDate', ':date'),
                   $qb->expr()->isNull('n.toDate')
               ),
               $qb->expr()->orX(
                   $qb->expr()->in('p.id', ':profiles'),
                   $qb->expr()->isNull('p.id')
               ),
               $qb->expr()->orX(
                   $qb->expr()->in('c.id', ':countries'),
                   $qb->expr()->isNull('c.id')
               )
             ))
           ->orderBy('n.fromDate', 'DESC')
           ->addOrderBy('n.toDate', 'DESC');
        
        $qb->setParameter('module', Constants::OFFICE_MODULE);
        $qb->setParameter('empty', '');
        $qb->setParameter('date', $date);
        $qb->setParameter('profiles', $profiles);
        $qb->setParameter('countries', $countries);
        
        return $qb->getQuery()->getResult();
    }

    public function getNewsByUser($user, $timezone) {
        $date = new \Datetime();
        $date->setTimezone($timezone);
        
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('n')
           ->from($this->getClassName(), 'n')
           ->leftJoin('n.country', 'c')
           ->where($qb->expr()->andX(
               $qb->expr()->orX(
                   $qb->expr()->eq('n.module', ':module'),
                   $qb->expr()->eq('n.module', ':empty')
               ),
               $qb->expr()->lte('n.fromDate', ':date'),
               $qb->expr()->orX(
                   $qb->expr()->gte('n.toDate', ':date'),
                   $qb->expr()->isNull('n.toDate')
               ),
               $qb->expr()->orX(
                   $qb->expr()->in('c.id', ':countries'),
                   $qb->expr()->isNull('c.id')
               )
           ))
           ->orderBy('n.fromDate', 'DESC')
           ->addOrderBy('n.toDate', 'DESC');
    
        $qb->setParameter('module', Constants::PATIENT_MODULE);
        $qb->setParameter('empty', '');
        $qb->setParameter('date', $date);
        $qb->setParameter('countries', array($user->getCountry()));
        
        return $qb->getQuery()->getResult();
    }
    
    public function findNewsArray() {
        $qb = $this->createQueryBuilder('o');
        // TODO: filter ?
        return $qb->getQuery()->getResult();
    }
    
    
}

?>