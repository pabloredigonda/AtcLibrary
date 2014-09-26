<?php
namespace Core\Model\Repository;

/**
 * OfficeProvider
 */

class OfficeProvider extends AbstractRepository {
    /* return properties from any find* method */
    protected $properties = array('id', 'phonePrefix', 'phoneNumber', 'addressStreet', 'addressCity', 'firstName', 'lastName', 'email', 'website', 'addressStreet', 'addressCity');

    public function findOfficeProviderArray($office) {
        $items = $this->findByQueryBuilder(array('office' => $office, 'deleted' => false), $this->properties);
        return $items;
    }

    public function findOfficeStaffWhere($where, $properties = null) {
        $properties = $properties ? $properties : $this->properties;
        return $this->findByQueryBuilder($where, $properties);
    }
    
    public function findBySpecialtyAndOffice($specialty, $office){
        
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.specialty', 's')
        ->andWhere($qb->expr()->eq('o.office', ':office'))
        ->andWhere($qb->expr()->eq('s.id', ':specialty'));
        $qb->setParameter('office', $office);
        $qb->setParameter('specialty', $specialty);
        
        return $qb->getQuery()->getResult();
    }
    
    public function findByPracticeAndOffice($practice, $office){
    
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.practice', 's')
        ->andWhere($qb->expr()->eq('o.office', ':office'))
        ->andWhere($qb->expr()->eq('s.id', ':practice'));
        $qb->setParameter('office', $office);
        $qb->setParameter('practice', $practice);
    
        return $qb->getQuery()->getResult();
    }
    
    public function findProvidersById($ids, $office) {
        $fields = array(
            'p.firstName',
            'p.email',
            'p.website',
            'CONCAT(p.phonePrefix, \' \', p.phoneNumber) AS phone',
            'CONCAT(p.faxPrefix, \' \', p.faxNumber) AS fax',
            'p.addressStreet',
            'p.addressStreetNumber',
            'p.addressCity',
            'scs.name AS scsName',
            'sc.name AS scName',
        );
    
        $qb = $this->createQueryBuilder('p');
        $qb->select($fields)
           ->leftJoin('Core\Model\SystemCountry', 'sc', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.addressCountry = sc.id')
           ->leftJoin('Core\Model\SystemCountryState', 'scs', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.addressState = scs.id')
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