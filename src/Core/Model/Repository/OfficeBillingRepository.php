<?php
/**
 * Core\Model\Repository\OfficeBillingRepository
 *
 * PHP version 5.4
 *
 * @category General
 * @package  Core\Model\Repository\OfficeBillinRepositoiry
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @version  GIT:<>
 * @link     https://github.com/desyncr
 */
namespace Core\Model\Repository;

/**
 * Class OfficeBillingRepository
 *
 * @category General
 * @package  Core\Model\Repository
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
 */
class OfficeBillingRepository extends AbstractRepository
{
    /**
     * findByStaffAndDate
     *
     * @param \Core\Model\Office|integer      $office Office instance
     * @param \Core\Model\OfficeStaff|integer $staff  OfficeStaff instance
     * @param \DateTime                       $date   Date time to filter
     *
     * @return Array
     */
    public function findByStaffAndDate($office, $staff, $date)
    {
        $startDate = clone $date;
        $startDate->setTime(0, 0, 0);
        
        $endDate = clone $date;
        $endDate->setTime(23, 59, 59);
        
        $qb = $this->createQueryBuilder('ob');
        $qb->andWhere($qb->expr()->eq('ob.office', ':office'))
            ->andWhere($qb->expr()->eq('ob.staff', ':staff'))
            ->andWhere(
                $qb->expr()->between('ob.createdDate', ':startDate', ':endDate')
            );
        $qb->setParameter('office', $office);
        $qb->setParameter('staff', $staff);
        $qb->setParameter('startDate', $startDate);
        $qb->setParameter('endDate', $endDate);

        return $qb->getQuery()->getResult();
    }
    
}

