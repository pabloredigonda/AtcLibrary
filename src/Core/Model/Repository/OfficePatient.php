<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;
use Core\Model\SystemUsersStatus;

/**
 * OfficePatient
 */

class OfficePatient extends AbstractRepository {
    
    protected $properties = array('id', 'firstName', 'lastName', 'sex', 'dateBirth', 'documentNumber' , 'homePhoneNumber');
    
    public function updateEmail($patient_id, $email) {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "UPDATE patient SET email = '" . $email . "' WHERE patient_id = " . $patient_id;
        $conn->executeUpdate($sql);
    }
    
    public function findByDocument($documentNumber, $documentType = null, $patientId = null, $office = null) {
    	$criteria = new Criteria();
    	
    	$criteria->andWhere(Criteria::expr()->eq("documentNumber", $documentNumber));
    	
    	$criteria->andWhere(Criteria::expr()->eq("status", $this->getEntityManager()->getReference('Core\Model\SystemUsersStatus', SystemUsersStatus::ACTIVE)));
    	
    	if ($documentType) {
    		$criteria->andWhere(Criteria::expr()->eq("documentType", $documentType));
    	}
    	 
    	if ($patientId) {
    		$criteria->andWhere(Criteria::expr()->neq("id", $patientId));
    	}
    	 
    	if ($office) {
    		$criteria->andWhere(Criteria::expr()->eq("office", $office));
    	}

    	return $this->matching($criteria);
    }
    
    public function findByUserId($userId) {
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere($qb->expr()->eq('p.user', ':user'))
           ->andWhere($qb->expr()->eq('p.status', ':status'));
        $qb->setParameter('user', $userId);
        $qb->setParameter('status', SystemUsersStatus::ACTIVE);
    
        return $qb->getQuery()->getResult();
    }
    
    public function findByOfficeAndUser($office, $user) {
        
        $criteria = new Criteria();
        $criteria->andWhere(Criteria::expr()->eq("office", $office));
    	$criteria->andWhere(Criteria::expr()->eq("user", $user));
        $result = $this->matching($criteria);
        if (count($result) > 0) {
            return $result[0];
        }
        return null;
    }

    public function getPictureFromAnyOffice($patientUser, $offices) {
        
        $criteria = new Criteria();
        $criteria->andWhere(Criteria::expr()->in("office", $offices));
        $criteria->andWhere(Criteria::expr()->eq("user", $patientUser));
        $criteria->andWhere(Criteria::expr()->neq("picture", null));
        return $this->matching($criteria);
    }
    
    /**
     * 
     * @param unknown $key      invitation key
     * @param boolean $user     true when you want to search for patients without user param
     */
    public function getByInviteKey($key, $user = null) {
        
        $params = array('invitationKey' => $key);
        if ($user === null) {
            $params['user'] = null;
        }
        return $this->findOneBy($params);
    }
    
    public function findByUserAndOffice($user, $office) {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('user', $user))
                 ->andWhere($criteria->expr()->eq('office', $office));
        if ($result = $this->matching($criteria)){
            return $result[0];
        }
    }

    public function findOfficesByUserId($userId) {
    
        $qb = $this->createQueryBuilder('os');
        $qb->select('IDENTITY (os.office)')
        ->andWhere($qb->expr()->eq('os.user', ':userId'));
    
        $qb->setParameter('userId', $userId);
        return $qb->getQuery()->getResult();
    }
    
    public function findPatientsById($ids, $office) {
        $fields = array(
            'p.id',
            'p.firstName',
            'p.lastName',
            'p.dateBirth',
            'dt.name AS dtName',
            'p.documentNumber',
            'p.id AS payer',
            'p.insuranceNumber',
            'p.email',
            'CONCAT(p.celPhonePrefix, \' \', p.celPhoneNumber) AS celPhone',
            'CONCAT(p.homePhonePrefix, \' \', p.homePhoneNumber) AS homePhone',
            'p.sex as gender',
            'p.addressStreet1',
            'p.addressStreetNumber',
            'p.addressDept',
            'p.addressFloor',
            'p.addressCity',
            'p.addressPostalCode',
            'sc.name AS scName',
            'scs.name AS scsName',
        );
    
        $qb = $this->createQueryBuilder('p');
        $qb->select($fields)
           ->leftJoin('Core\Model\SystemDocumentType', 'dt', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.documentType = dt.id')
           ->leftJoin('Core\Model\SystemCountry', 'sc', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.addressCountry = sc.id')
           ->leftJoin('Core\Model\SystemCountryState', 'scs', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.addressState = scs.id')
           ->andWhere($qb->expr()->eq('p.office', ':office'))
           ->andWhere($qb->expr()->eq('p.status', ':status'));
    
        $qb->setParameter('office', $office);
        $qb->setParameter('status', SystemUsersStatus::ACTIVE);
    
        if(count($ids)) {
            $qb->andWhere($qb->expr()->in('p.id', $ids));
        }
    
        return $qb->getQuery()->getResult();
    }

    /**
     * getByCountry
     *
     * @param $countries
     *
     * @return mixed
     */
    public function getByCountry($countries)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere($qb->expr()->in('p.addressCountry', $countries));

        return $qb->getQuery()->getResult();
    }

    /**
     * getByOffice
     *
     * @param $office
     *
     * @return mixed
     */
    public function getByOffice($office)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere($qb->expr()->eq('p.office', $office))
           ->andWhere($qb->expr()->eq('p.status', ':status'));
        $qb->setParameter('status', SystemUsersStatus::ACTIVE);
        
        return $qb->getQuery()->getResult();
    }

    /**
     * getCountOfficePatientByStatus
     *
     * @param \Core\Model\Office            $office Office to filter from
     * @param \Core\Model\SystemUsersStatus $status Status to filter from
     *
     * @return integer
     */
    public function getCountOfficePatientByStatus($office, $status)
    {
        return count($this->createQueryBuilder('p')
            ->select('count(p.id)')

            ->where('p.office = :office')
            ->andWhere('p.status = :status')

            ->setParameter('office', $office)
            ->setParameter('status', $status)

            ->groupBy('p.id')

            ->getQuery()
            ->getResult());
    }
}