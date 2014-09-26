<?php
namespace Core\Model\Repository;

/**
 * OfficePayer
 */

class OfficePayer extends AbstractRepository {
    
    protected $properties = array('id', 'plan', 'insuranceOther', 'price');

    public function findOfficePayerWhere($where, $properties = null) {
        $properties = $properties ? $properties : $this->properties;
        return $this->findByQueryBuilder($where, $properties);
    }

    public function findPayersById($ids, $office) {
        $fields = array(
            'i.name AS iName',
            'p.plan',
            'p.price',
        );
    
        $qb = $this->createQueryBuilder('p');
        $qb->select($fields)
        ->leftJoin('Core\Model\Insurance', 'i', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.insurance = i.id')
        ->andWhere($qb->expr()->eq('p.office', ':office'))
        ->andWhere($qb->expr()->eq('p.deleted', ':deleted'));
    
        $qb->setParameter('office', $office);
        $qb->setParameter('deleted', false);
    
        if(count($ids)) {
            $qb->andWhere($qb->expr()->in('p.id', $ids));
        }
    
        return $qb->getQuery()->getResult();
    }
    
} 