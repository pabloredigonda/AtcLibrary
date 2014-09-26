<?php
/**
 * Core\Model\Repository
 *
 * PHP version 5.4
 *
 * @category General
 * @package  Core\Model\Repository
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @version  GIT:<>
 * @link     https://github.com/desyncr
 */
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class NotificationRepository
 *
 * @category General
 * @package  Core\Model\Repository
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
 */
class NotificationRepository extends EntityRepository
{
    /**
     * findAllByUserAndStatus
     *
     * @param mixed $user   User
     * @param mixed $status Status
     *
     * @return mixed
     */
    public function findAllByUserAndStatus($user, $status)
    {
        return $this->_findAllNotification(null, $user, $status);
    }

    /**
     * findAllByUser
     *
     * @param mixed $user User
     *
     * @return mixed
     */
    public function findAllByUser($user)
    {
        return $this->_findAllNotification(null, $user);
    }

    /**
     * findAllById
     *
     * @param Integer $nid ID
     *
     * @return mixed
     */
    public function findAllById($nid)
    {
        return $this->_findAllNotification($nid);
    }

    /**
     * findAllByIdAndUser
     *
     * @param Integer $nid  ID
     * @param mixed   $user User
     *
     * @return mixed
     */
    public function findAllByIdAndUser($nid, $user)
    {
        return $this->_findAllNotification($nid, $user);
    }

    /**
     * findAll
     *
     * @param \DateTime $date Bottom range
     *
     * @return mixed
     */
    public function findAllNotifications(\DateTime $date)
    {
        $criteria = new Criteria();
        $criteria->andWhere(Criteria::expr()->gt('createDate', $date));
        $criteria->orderBy(array('id' => Criteria::DESC));
        return $this->matching($criteria);
    }

    /**
     * Retrieves notifications by origin and optionally a dateTime bottom range.
     *
     * @param string    $origin FQCN for origin class (ie: Core\Notification\PlatformNotification)
     * @param \DateTime $range  DateTime object for bottom ranger (retrieves only newer notifications)
     *
     * @return array
     */
    public function findAllNotificationsByOrigin($origin, \DateTime $range)
    {
        $criteria = new Criteria();
        $criteria->andWhere(Criteria::expr()->gt('createDate', $range));
        $criteria->andWhere(Criteria::expr()->eq('origin', $origin));
        $criteria->orderBy(array('id' => Criteria::DESC));
        return $this->matching($criteria);
    }

    /**
     * findAllNotification
     *
     * @param integer $nid    ID
     * @param mixed   $user   User
     * @param mixed   $status Status
     *
     * @return mixed
     */
    private function _findAllNotification(
        $nid = null,
        $user = null,
        $status = null
    ) {

        $qb = $this->createQueryBuilder('n');
        $qb->leftJoin(
            'Core\Model\NotificationTarget',
            'ns',
            Join::WITH,
            'n.id = ns.notification'
        );

        if ($nid) {
            $qb->andWhere($qb->expr()->eq('n.id', ':nid'));
            $qb->setParameter('nid', $nid);
        }

        if ($user) {
            $qb->andWhere($qb->expr()->eq('ns.target', ':user'));
            $qb->setParameter('user', $user->getId());
        }

        if ($status) {
            $qb->andWhere($qb->expr()->eq('ns.status', ':status'));
            $qb->setParameter('status', $status);
        }
        $qb->orderBy('n.createdDate', 'DESC');
        return $qb->getQuery()->getResult();

    }

    /**
     * getPendingNotifications
     *
     * @param String $interval Interval in the form of '1 minutes' or '1 hour'
     *
     * @return mixed
     */
    public function getPendingNotifications($interval)
    {
        $sql = "SELECT * FROM notification WHERE scheduled < now() AND scheduled > (now() - interval '".$interval."') AND status = 0;";
        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }
}
