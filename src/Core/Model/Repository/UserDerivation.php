<?php

namespace Core\Model\Repository;

class UserDerivation extends AbstractRepository
{
    
    public function findPendingByUserArray($user) {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.patient', 'p')
           ->innerJoin('o.status', 's')
           ->andWhere($qb->expr()->isNotNull('o.patientVisitDerivation'))
           ->andWhere($qb->expr()->eq('p.user', ':user'))
           ->andWhere($qb->expr()->eq('s.watch', ':watch'));
    
        $qb->setParameter('user', $user);
        $qb->setParameter('watch', true);
        return $qb->getQuery()->getResult();
    }

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

    public function findUserDerivationsByUser($user) {
        $fields = array(
            'p.date',
            'p.specialty',
            'p.derivation',
            'p.office',
            'p.officeEmail',
            'p.officeAddress',
            'CONCAT(p.officePhonePrefix, \' \', p.officePhoneNumber) AS officePhone',
        );
    
        $qb = $this->createQueryBuilder('p');
        $qb->select($fields)
           ->andWhere('p.user = :user')
           ->setParameter('user', $user);
    
        return $qb->getQuery()->getResult();
    }
    
}