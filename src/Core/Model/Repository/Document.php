<?php
namespace Core\Model\Repository;

use Core\Model\Profile;
use Doctrine\Common\Collections\Criteria;

/**
 * Document repository
 */

class Document extends AbstractRepository {
    public function findByOfficeDocument($office, $status = null, $country = null) {
        $criteria = new Criteria();
        if (!$office) {
            $criteria->andWhere(Criteria::expr()->isNull("office"));
        } else {
            $criteria->andWhere(Criteria::expr()->eq("office", $office));
        }

        if ($country) {
            $criteria->andWhere(
                Criteria::expr()->in("country", is_array($country) ?: array($country))
            );
        }

        if (!$status) {
            $status = \Core\Model\Document::STATUS_ACTIVE;
        }
        $criteria->andWhere(Criteria::expr()->eq("status", $status));
        $criteria->orderBy(array('title' => Criteria::ASC));
        return $this->matching($criteria);
    }

    /**
     * getDocumentsByCountry
     *
     * @param String $country Country ID
     *
     * @return null
     */
    public function getDocumentsByCountry($country)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('d')
            ->from('\Core\Model\Document', 'd')
            ->innerJoin('d.country', 'c')
            ->where('c.id = :country')
            ->orderBy('d.title', 'ASC')
            ->setParameter('country', $country)
            ->getQuery()
            ->getResult();
    }

    /**
     * getDocumentsBySpecialty
     *
     * @param Array $specialties Specialties
     *
     * @return null
     */
    public function getDocumentsBySpecialty($specialties)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('d')
            ->from('\Core\Model\Document', 'd')
            ->innerJoin('d.specialty', 's')
            ->where(
                $qb->expr()->in('s.id', ':specialties')
            )
            ->orderBy('d.title', 'ASC')
            ->setParameter('specialties', $specialties)
            ->getQuery()
            ->getResult();
    }

    /**
     * Function
     *
     * @param mixed $name Param
     *
     * @return null
     */
    public function getDocumentsBySpecialtyAndCountry($specialty, $country)
    {
        $q = $this->getEntityManager()
            ->createQueryBuilder();

        $q->select('d')
            ->from('\Core\Model\Document', 'd');

        $q->innerJoin('d.specialty', 's');
        $q->innerJoin('d.country', 'c');

        $q->where('s.id = :specialty')
            ->andWhere('c.id = :country')
            ->orderBy('d.title', 'ASC');

        $q->setParameter('specialty', $specialty);
        $q->setParameter('country', $country);

        return $q->getQuery()->getResult();
    }
    
    public function findByOwnerAndOffice($owner, $office, $ids = null) {
        $criteria = new Criteria();
        $criteria->andWhere(Criteria::expr()->eq("owner", $owner))
                 ->andWhere(Criteria::expr()->eq("office", $office))
                 ->andWhere(Criteria::expr()->eq("status", $status = \Core\Model\Document::STATUS_ACTIVE));
        
        if($ids){
        	$criteria->andWhere(Criteria::expr()->in("id", $ids));
        }
        
        return $this->matching($criteria);
    }

    /**
     * Retrieves a document by ID only if the document belongs to
     * `$owner` or `$profile` array contains `Profile::ADMIN`
     *
     * TODO Make `$profile` optional default to `$owner`'s profile
     *
     * @param Integer                   $id      Document ID
     * @param \Core\Model\Users|integer $owner   User to list documents
     * @param Array                     $profile User profile
     *
     * @return \Core\Model\Document|null
     */
    public function findByOwnerOrProfile($id, $owner, $profile) {
    	$qb = $this->createQueryBuilder('o');
    	$qb->andWhere($qb->expr()->eq('o.id', ':id'));
    	
    	if (!in_array(Profile::ADMIN, $profile)) {
    		$qb->andWhere($qb->expr()->eq('o.owner', ':owner'));
	    	$qb->setParameter('owner', $owner);
    	}

    	$qb->setParameter('id', $id);
    	 
    	return $qb->getQuery()->getResult();
    }
    
    public function findAvailableByOffice($owner, $office, $ids = null) {
    	$qb = $this->createQueryBuilder('o');
    	$qb->andWhere($qb->expr()->eq('o.office', ':office'));
    	$qb->andWhere($qb->expr()->eq('o.status', ':status'));
    	$qb->andWhere($qb->expr()->orX(
			$qb->expr()->eq('o.owner', ':owner'),
    		$qb->expr()->eq('o.isPrivate', ':isPrivate'))
		);
    	
    	$qb->orderBy('o.title', 'ASC');

    	$qb->setParameter('office', $office);
    	$qb->setParameter('status', \Core\Model\Document::STATUS_ACTIVE);
    	$qb->setParameter('owner', $owner);
    	$qb->setParameter('isPrivate', false);
    	
    	if($ids){
    	    $qb->andWhere($qb->expr()->in("o.id", $ids));
    	}
    	    	
    	return $qb->getQuery()->getResult();
    }
}
