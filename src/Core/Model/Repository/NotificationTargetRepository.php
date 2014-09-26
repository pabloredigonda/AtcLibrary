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

/**
 * Class NotificationTargetRepository
 *
 * @category General
 * @package  Core\Model\Repository
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
 */
class NotificationTargetRepository extends EntityRepository
{
    /**
     * findByNotificationStatus
     *
     * @param Int $nid    Notification id
     * @param Int $target Notification target
     *
     * @return mixed
     */
    public function findByNotificationStatus($nid, $target)
    {
        $criteria = new Criteria();
        $criteria->andWhere(Criteria::expr()->eq('notification', $nid));
        $criteria->andWhere(Criteria::expr()->eq('target_id', $target->getId()));
        $criteria->andWhere(
            Criteria::expr()->eq(
                'target_entity',
                get_class($target)
            )
        );
        $res = $this->matching($criteria);
        
        if (count($res[0])) {
            return $res[0];
        }
        return false;
    }

    /**
     * findByNotificationTargetAndStatus
     *
     * @param Object $target Notification target object
     * @param Int    $status Notification status
     * @param Int    $limit  Limit
     * @param String $type   Notification type
     *
     * @return mixed
     */
    public function findByNotificationTargetAndStatus(
        $target,
        $status = null,
        $limit  = null,
        $type   = null
    ) {
        $criteria = new Criteria();
        $criteria->andWhere(Criteria::expr()->eq('target_id', $target->getId()));
        $criteria->andWhere(
            Criteria::expr()->eq('target_entity', get_class($target) )
        );
        if ($status !== null) {
            $criteria->andWhere(Criteria::expr()->eq('status', (int)$status));
        }

        if ($type) {
            $criteria->andWhere(Criteria::expr()->eq('type', $type));
        }
        
        if ($limit) {
            $criteria->setMaxResults($limit);
        }

        $criteria->orderBy(
            array(
                'id' => Criteria::DESC
            )
        );
        return $this->matching($criteria);
    }

    /**
     * findByNotificationTarget
     *
     * @param Object $target Notification target object
     *
     * @return mixed
     */
    public function findByNotificationTarget($target)
    {
        $criteria = new Criteria();
        $criteria->andWhere(Criteria::expr()->eq('target_id', $target->getId()));
        $criteria->andWhere(
            Criteria::expr()->eq(
                'target_entity',
                get_class($target)
            )
        );
        return $this->matching($criteria);
    }

    /**
     * findByNotificationAndUser
     *
     * @param Int    $nid    Notification id
     * @param Object $target Target object
     *
     * @return mixed
     */
    public function findByNotificationAndUser($nid, $target)
    {
        $criteria = new Criteria();
        $criteria->andWhere(Criteria::expr()->eq('notification', $nid));
        $criteria->andWhere(Criteria::expr()->eq('target_id', $target->getId()));
        $criteria->andWhere(
            Criteria::expr()->eq(
                'target_entity',
                get_class($target)
            )
        );
        $res = $this->matching($criteria);
        
        if (count($res[0])) {
            return $res[0];
        }
    }

    /**
     * updateNotificationStatus
     *
     * @param Object $n      Notification object
     * @param mixed  $user   User
     * @param int    $status Notification status
     *
     * @return mixed
     */
    public function updateNotificationStatus($n, $user, $status)
    {
        $entityManager = $this->getEntityManager();
        // TODO Fix this
        if ($target = $this->findByNotificationAndUser($n, $user)) {
            $target->setStatus($status);

            $entityManager->persist($target);
            $entityManager->flush();
        }
    }

    /**
     * Retrieves a notification target given a notification's origin,
     * target and status.
     *
     * @param Object $target Notification target
     * @param String $origin Filter by origin
     * @param Array  $status NotificationTarget::STATUS_* $status
     * @param Int    $days   Days to filter
     * @param Bool   $debug  Debug query
     *
     * @return array
     */
    public function findByNotificationByTargetAndOrigin(
        $target,
        $origin,
        $status,
        $days = null,
        $debug = false
    ) {
        $date = new \DateTime();
        $days = (int)$days;

        // Back compt.
        if (!is_array($status)) {
            $status = array(
                $status
            );
        } 
        
        // Avoid special chars problem in Postgres 8.4
        $origin_class = str_replace("\\", "\\\\", $origin);
        $target_class = str_replace('DoctrineORMModule\Proxy\__CG__\\', '', get_class($target));
        $target_class = str_replace("\\", "\\\\", $target_class);
        
        // Uncomment this if you use Postgres > 8.4
        //$origin_class = $origin;
        //$target_class = get_class($target);

        $sql = "
            SELECT nt.*
            FROM notification_target AS nt
              INNER JOIN notification n ON n.id = nt.notification_id
            WHERE n.origin = '" . $origin_class . "'
                  AND nt.target_entity = '" . $target_class . "'
                  AND nt.target_id = " . $target->getId() . "
                  AND nt.status IN('" . join("','", $status) . "')
        "; 
        if ($days) {
            $sql .= " AND DATE_PART('day', n.create_date - '" . $date->format('Y-m-d H:i:s') . "'::timestamp) <= " . $days;
        }
        echo $sql . PHP_EOL;
        
        $conn = $this->getEntityManager()->getConnection();
        $result = $conn->executeQuery($sql, array(), array(), null)->fetchAll();
        return $result;
    }
}
