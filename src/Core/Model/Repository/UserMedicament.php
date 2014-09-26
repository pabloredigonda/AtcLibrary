<?php
/**
 * General
 *
 * PHP version 5.4
 *
 * @category General
 * @package  General
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @version  SVN:<>
 * @link     https://docs.saludmovil.net
 */

namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;

/**
 * Core\Model\Repository
 *
 * @category General
 * @package  Core\Model\Repository
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://docs.saludmovil.net
 */
class UserMedicament extends AbstractRepository
{
    /**
     * findAllByUser
     *
     * @param \Core\Model\Users $user Users object
     *
     * @return mixed
     */
    public function findAllByUser($user)
    {
//         $criteria = new Criteria();
//         $criteria->andWhere($criteria->expr()->eq('user', $user));
//         return $this->matching($criteria);
        
        $qb = $this->createQueryBuilder('up');
        $qb->innerJoin('up.patient', 'p');
        $qb->andWhere($qb->expr()->eq('p.user', ':user'));
        $qb->setParameter('user', $user);
        
        return $qb->getQuery()->getResult();
    }
}