<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;
use Core\Model\SystemUsersStatus;

/**
 * OfficeStaff
 */

class OfficeStaff extends AbstractRepository
{
    protected $properties = array('id', 'firstName', 'lastName', 'lastAccess', 'email', 'phoneNumber', 'phonePrefix', 'celPhoneNumber', 'celPhonePrefix');

    public function getByInviteKey() {

    }

    public function findByUserAndActiveStatus($user, $systemUsersStatus) {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('user', $user))
                 ->andWhere($criteria->expr()->eq('status', $systemUsersStatus))
                 ->orderBy(array("lastAccess" => Criteria::DESC));
        return $this->matching($criteria);
    }

    public function findByUserId($userId) {

        $qb = $this->createQueryBuilder('os');
        $qb->andWhere($qb->expr()->eq('os.user', ':user'))
           ->andWhere($qb->expr()->eq('os.status', ':status'));
        $qb->setParameter('user', $userId);
        $qb->setParameter('status', SystemUsersStatus::ACTIVE);

        return $qb->getQuery()->getResult();
    }

    public function findByInvitationKey($key, $status = 'invite_pending') {
        return $this->findOneBy(array('invitationKey' => $key, 'status' => $status));
    }

    public function findOfficesByUserId($userId) {

        $qb = $this->createQueryBuilder('os');
        $qb->select('IDENTITY (os.office)')
           ->andWhere($qb->expr()->eq('os.user', ':userId'));

        $qb->setParameter('userId', $userId);
        return $qb->getQuery()->getResult();
    }

    public function findNotCurrentOffices($user, $current_office, $status) {
    	$qb = $this->createQueryBuilder('s');
    	$qb->innerJoin('s.office', 'o')
    	   ->andWhere($qb->expr()->eq('s.user', ':user'))
    	   ->andWhere($qb->expr()->neq('s.office', ':office'))
    	   ->andWhere($qb->expr()->eq('s.status', ':status'))
    	   ->orderBy('o.name', 'ASC');

    	$qb->setParameter('user', $user);
    	$qb->setParameter('office', $current_office);
    	$qb->setParameter('status', $status);

    	return $qb->getQuery()->getResult();
    }

    public function findOfficeStaffByUserAndOffice($user, $office) {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('user', $user))
                 ->andWhere($criteria->expr()->eq('office', $office))
                 ->andWhere($criteria->expr()->in('status', array('active', 'inactive')));
        
        if ($result = $this->matching($criteria)){
            return $result[0];
        }
    }

    public function findByProfileAndOffice($profile, $office, $status) {

        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.profile', 'p')
           ->andWhere($qb->expr()->eq('p.id', ':profile'))
           ->andWhere($qb->expr()->eq('o.office', ':office'))
           ->andWhere($qb->expr()->eq('o.status', ':status'))
        ;

        $qb->setParameter('profile', $profile);
        $qb->setParameter('office', $office);
        $qb->setParameter('status', $status);

        return $qb->getQuery()->getResult();
    }

    public function findSingleOfficeStaffById($id, $office) {
        $staff = $this->findById($id);
        if ($staff && $officeStaff = $this->findByQueryBuilder(array('office' => $office, 'id' => $staff))) {
            return isset($officeStaff[0]) ? $officeStaff[0] : false;
        }
        return false;
    }

    public function findOfficeStaff($office, $properties = null) {
        $properties = $properties ? $properties : $this->properties;
        $items = $this->findByQueryBuilder(array('office' => $office,
            'status' => array(SystemUsersStatus::ACTIVE, SystemUsersStatus::BLOCKED,
                SystemUsersStatus::INVITE_PENDING, SystemUsersStatus::INVITE_PENDING,
                SystemUsersStatus::INACTIVE)),
            $properties,
            array(array('lastName', 'ASC'), array('firstName', 'ASC'))
        );
        return $items;
    }

    public function findStaffByOffice($office) {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere($qb->expr()->eq('a.office', ':office'))
            ->andWhere($qb->expr()->eq('a.status', ':status'));
        $qb->setParameter('office', $office);
        $qb->setParameter('status', SystemUsersStatus::ACTIVE);

        return $qb->getQuery()->getResult();
    }

    public function findProfessionalByUserAndOffice($user, $office, $profile) {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.profile', 'p')
        ->andWhere($qb->expr()->eq('p.id', ':profile'))
        ->andWhere($qb->expr()->eq('o.user', ':user'))
        ->andWhere($qb->expr()->eq('o.office', ':office'));
        $qb->setParameter('profile', $profile);
        $qb->setParameter('user', $user);
        $qb->setParameter('office', $office);

        return $qb->getQuery()->getResult();
    }

    public function findProfessionalWithWorkday($office, $status) {
        $qb = $this->createQueryBuilder('os');
        $qb->innerJoin('Core\Model\OfficeStaffWorkday', 'ow', \Doctrine\ORM\Query\Expr\Join::WITH, 'os.id = ow.staff')
           ->innerJoin('os.profile', 'p')
           ->andWhere($qb->expr()->eq('p.id', ':profile'))
           ->andWhere($qb->expr()->eq('os.office', ':office'))
           ->andWhere($qb->expr()->eq('os.status', ':status'));

        $qb->setParameter('profile', 'professional');
        $qb->setParameter('office', $office);
        $qb->setParameter('status', $status);
        return $qb->getQuery()->getResult();
    }

    /**
     * Returns an staff object only if it have workdays available.
     *
     * @param Object $staff  OfficeStaff object
     * @param Object $status Object Status object
     *
     * @return mixed
     */
    public function findStaffWithWorkday($staff, $status) {
        $qb = $this->createQueryBuilder('os');
        $qb->innerJoin('Core\Model\OfficeStaffWorkday', 'ow', \Doctrine\ORM\Query\Expr\Join::WITH, 'os.id = ow.staff')
            ->innerJoin('os.profile', 'p')
            ->andWhere($qb->expr()->eq('p.id', ':profile'))
            ->andWhere($qb->expr()->eq('os.id', ':staff'))
            ->andWhere($qb->expr()->eq('os.status', ':status'));

        $qb->setParameter('profile', 'professional');
        $qb->setParameter('staff', $staff);
        $qb->setParameter('status', $status);
        return $qb->getQuery()->getResult();
    }

    public function getByUserAndProfile($user, $profile) {
    	$qb = $this->createQueryBuilder('o');
    	$qb->innerJoin('o.profile', 'p')
    	   ->andWhere($qb->expr()->eq('p.id', ':profile'))
    	   ->andWhere($qb->expr()->eq('o.status', ':status'))
    	   ->andWhere($qb->expr()->eq('o.user', ':user'));
    	$qb->setParameter('profile', $profile);
    	$qb->setParameter('status', SystemUsersStatus::ACTIVE);
    	$qb->setParameter('user', $user);

    	return $qb->getQuery()->getResult();
    }

    public function findProfessionalBySpecialtyAndOffice($specialty, $office) {

        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.profile', 'p');
        $qb->innerJoin('o.specialty', 's')
        ->andWhere($qb->expr()->eq('p.id', ':profile'))
        ->andWhere($qb->expr()->eq('o.office', ':office'))
        ->andWhere($qb->expr()->eq('o.status', ':status'))
        ->andWhere($qb->expr()->eq('s.id', ':specialty'));
        $qb->setParameter('profile', 'professional');
        $qb->setParameter('office', $office);
        $qb->setParameter('status', SystemUsersStatus::ACTIVE);
        $qb->setParameter('specialty', $specialty);

        return $qb->getQuery()->getResult();
    }

    // TEST
    public function findByPatientUserArray($user) {
    	return $this->findByQueryBuilder(array('lastName' => 'Knight', 'user' => $user));
    }

    /**
     * findAllByPatient
     *
     * @param Array $arrPatient Patients array
     *
     * @return mixed
     */
    public function findAllByPatient($arrPatient)
    {

        $arrPatientsId = array_map(
            function ($e) {
                return $e->getId();
            }, $arrPatient
        );

        $qb = $this->createQueryBuilder('os');
        $qb->innerJoin('\Core\Model\OfficeStaffPatient', 'osp', \Doctrine\ORM\Query\Expr\Join::WITH, 'osp.officeStaff = os.id')
            ->andWhere($qb->expr()->in('osp.patient', ':patients'));
        $qb->setParameter('patients', $arrPatientsId);

        return $qb->getQuery()->getResult();
    }

    public function findStaffById($ids, $office) {
        $fields = array(
            'o.id',
            'o.firstName',
            'o.lastName',
            'o.email',
            'CONCAT(o.phonePrefix, \' \', o.phoneNumber) AS phone',
            'CONCAT(o.celPhonePrefix, \' \', o.celPhoneNumber) AS celPhone',
            's.name',
            'o.license',
            'o.id AS profile',
            'o.id AS specialty',
            'o.lastAccess',
        );

        $qb = $this->createQueryBuilder('o');
        $qb->select($fields)
            ->leftJoin('Core\Model\SystemUsersStatus', 's', \Doctrine\ORM\Query\Expr\Join::WITH, 'o.status = s.status')
            ->andWhere($qb->expr()->eq('o.office', ':office'))
            ->andWhere($qb->expr()->eq('o.status', ':status'));

        $qb->setParameter('office', $office);
        $qb->setParameter('status', SystemUsersStatus::ACTIVE);

        if(count($ids)) {
            $qb->andWhere($qb->expr()->in('o.id', $ids));
        }

        return $qb->getQuery()->getResult();
    }

    public function findActiveAdmin($office, $staff, $profile, $status) {
		$qb = $this->createQueryBuilder('o');
        $qb->select('count(o.id)')
           ->innerJoin('o.profile', 'p')
           ->andWhere($qb->expr()->eq('p.id', ':profile'))
           ->andWhere($qb->expr()->eq('o.office', ':office'))
           ->andWhere($qb->expr()->neq('o.id', ':staff'))
           ->andWhere($qb->expr()->eq('o.status', ':status'));

        $qb->setParameter('profile', $profile);
        $qb->setParameter('office', $office);
        $qb->setParameter('staff', $staff);
        $qb->setParameter('status', $status);

        return $qb->getQuery()->getSingleScalarResult();
    }
    
    public function findOneNotDeletedByOfficeAndEmail($office, $email)
    {
    	$qb = $this->createQueryBuilder('o');
    	$qb->andWhere($qb->expr()->eq('o.office', ':office'))
    	   ->andWhere($qb->expr()->eq('o.email', ':email'))
    	   ->andWhere($qb->expr()->in('o.status', array(
    	   		SystemUsersStatus::ACTIVE,
    	   		SystemUsersStatus::INACTIVE,
    	   		SystemUsersStatus::INVITE_PENDING
             )))
    	   ->setMaxResults(1);
    
    	$qb->setParameter('office', $office);
    	$qb->setParameter('email', $email);
    
    	return $qb->getQuery()->getResult();
    }

 }