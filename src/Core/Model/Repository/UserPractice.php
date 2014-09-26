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
class UserPractice extends AbstractRepository
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
        $qb = $this->createQueryBuilder('up');
        $qb->innerJoin('up.status', 's');
        $qb->leftJoin('up.patient', 'p');
        $qb->andWhere($qb->expr()->orX(
       		$qb->expr()->eq('up.user', ':user'),
       		$qb->expr()->eq('p.user', ':user')
		));
        $qb->andWhere($qb->expr()->eq('s.watch', ':watch'));
        $qb->setParameter('user', $user);
        $qb->setParameter('watch', false);
        
        return $qb->getQuery()->getResult();
    }
    
    public function findPendingByUserArray($user) {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.patient', 'p')
           ->innerJoin('o.status', 's')
           ->andWhere($qb->expr()->isNotNull('o.patientVisitPractice'))
           ->andWhere($qb->expr()->eq('p.user', ':user'))
           ->andWhere($qb->expr()->eq('s.watch', ':watch'));
    
        $qb->setParameter('user', $user);
        $qb->setParameter('watch', true);
        return $qb->getQuery()->getResult();
    }
    
    public function findUserPracticesByUser($user) {
        $fields = array(
            'up.date',
            'up.practice',
            'up.office',
            'up.officeEmail',
            'up.officeAddress',
            'CONCAT(up.officePhonePrefix, \' \', up.officePhoneNumber) AS officePhone',
        );
        
        $qb = $this->createQueryBuilder('up');
        $qb->select($fields)
            ->leftJoin('up.patient', 'p')
            ->andWhere($qb->expr()->orX(
            	$qb->expr()->eq('up.user', ':user'),
            	$qb->expr()->eq('p.user', ':user')
            ))
            ->setParameter('user', $user)
        	->orderBy('up.date', 'ASC');
        
        return $qb->getQuery()->getResult();
    }
    
}