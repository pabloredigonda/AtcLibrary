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

/**
 * Core\Model\Repository
 *
 * @category General
 * @package  Core\Model\Repository
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://docs.saludmovil.net
 */
class UserPrescription extends AbstractRepository
{
    /**
     * findAllByUser
     *
     * @param \Core\Model\Users $user   Users object
     * @param Array             $status Array string statuses
     *
     * @return mixed
     */
    public function findAllByUser($user, $status = null)
    {
        $qb = $this->createQueryBuilder('up');
        $qb->leftJoin('up.patient', 'p');
        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('up.user', ':user'),
            $qb->expr()->eq('p.user', ':user')
        ));
        
        $qb->setParameter('user', $user);
        
        if ($status) {
            $qb->andWhere($qb->expr()->in('up.status', ':status'));
            $qb->setParameter('status', $status);
        }
        return $qb->getQuery()->getResult();
    }
    
    public function findPendingByUserArray($user) {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.patient', 'p')
           ->innerJoin('o.status', 's')
           ->andWhere($qb->expr()->isNotNull('o.patientVisitPrescription'))
           ->andWhere($qb->expr()->eq('p.user', ':user'))
           ->andWhere($qb->expr()->eq('s.watch', ':watch'));
    
        $qb->setParameter('user', $user);
        $qb->setParameter('watch', true);
        return $qb->getQuery()->getResult();
    }
    
    public function findUserPrescriptionsByUser($user, $status) {
        $fields = array(
            'up.medicament',
            'up.posology',
            'up.startDate',
            'up.endDate',
        );
        
        $qb = $this->createQueryBuilder('up');
        $qb->select($fields)
            ->innerJoin('up.patient', 'p')
            ->andWhere('p.user = :user')
            ->andWhere($qb->expr()->in('up.status', $status))
            ->setParameter('user', $user);
        
        $qb->setParameter('user', $user);
        return $qb->getQuery()->getResult();
    }
   
}