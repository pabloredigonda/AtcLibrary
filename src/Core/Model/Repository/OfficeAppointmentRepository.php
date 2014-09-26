<?php
namespace Core\Model\Repository;
use Doctrine\Common\Collections\Criteria;
use Core\Model\SystemAppointmentStatus;
 
/**
 * OfficeAppointmentRepository
 */

class OfficeAppointmentRepository extends AbstractRepository {
    
    protected $properties = array('id', 'date', 'overlapping', 'created', 'user', 'office' , 'patient', 'staff', 'status', 'ehr', 'dateEnd');

    public function markAppointmentAsNotPending($id, $office_id, $staff_id, $patient_id, $ehr_id) {
        
        $conn = $this->getEntityManager()->getConnection();

        $sql  = "UPDATE office_appointment ";
        $sql .= "   SET ehr_id = '" . $ehr_id . "' ";
        $sql .= " WHERE appointment_id = '" . $id . "'";
        $sql .= "   AND office_id = '" . $office_id . "'";
        $sql .= "   AND staff_id = '" . $staff_id . "'";
        $sql .= "   AND patient_id = '" . $patient_id . "'";
        $conn->executeUpdate($sql);
    }
    
    public function findAllAppointmentsByOfficeStaff($office, $staff, $start = null, $end = null, $status = null) {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('office', $office))
                 ->andWhere($criteria->expr()->eq('staff', $staff))
        		 ->andWhere($criteria->expr()->eq('unexpected', false));

        if($start && $end){
        	$criteria->andWhere(
				$criteria->expr()->andx(
					$criteria->expr()->gte('date', $start),
					$criteria->expr()->lte('dateEnd', $end)
        		)
    		);
        	
        }elseif ($start) {
            $criteria->andWhere($criteria->expr()->gte('date', $start));
        }
        elseif ($end) {
            $criteria->andWhere($criteria->expr()->lte('dateEnd', $end));
        }

        if ($status) {
            $criteria->andWhere($criteria->expr()->neq('status', $status));
        }

        return $this->matching($criteria);
    }
    
    public function isOverlapped($office, $staff, $start_time, $end_time, $apointmentId) {
        
        //@TODO Modify to SQL SELECT COUNT() !!!!!
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('office', $office))
                 ->andWhere($criteria->expr()->eq('staff', $staff))
                 ->andWhere($criteria->expr()->neq('id', $apointmentId))
                 ->andWhere($criteria->expr()->lt('date', $end_time))
                 ->andWhere($criteria->expr()->gt('dateEnd', $start_time))
                 ->andWhere($criteria->expr()->in('status', array('pending','arrived','seen')));
        
        $results = $this->matching($criteria);

        return $results->count() ? true : false;
    }
    
    public function isFirstMatchingOfficeAndPatient($office, $patient) {
        $qb = $this->createQueryBuilder('o');
        $qb->select('count(o.id)')
           ->andWhere($qb->expr()->eq('o.office', ':office'))
           ->andWhere($qb->expr()->eq('o.patient', ':patient'))
           ->andWhere($qb->expr()->eq('o.first', ':first'));
        $qb->setParameter('office', $office);
        $qb->setParameter('patient', $patient);
        $qb->setParameter('first', true);
        
        return $qb->getQuery()->getSingleScalarResult() ? false : true;
    }
    
    public function getTimestamp($id, $office, $staff, $patient) {
        $qb = $this->createQueryBuilder('o');
        $qb->select('o.date')
        ->andWhere($qb->expr()->eq('o.id', ':id'))
        ->andWhere($qb->expr()->eq('o.office', ':office'))
        ->andWhere($qb->expr()->eq('o.patient', ':patient'))
        ->andWhere($qb->expr()->eq('o.staff', ':staff'));
        $qb->setParameter('id', $id);
        $qb->setParameter('office', $office);
        $qb->setParameter('patient', $patient);
        $qb->setParameter('staff', $staff);
    
        return strtotime($qb->getQuery()->getSingleScalarResult());
    }

    /**
     * Returns appointments by date, status and office.
     *
     * Returns appointments older or equal than $date which status is one of $status array
     * and belongs to a office in $offices array.
     *
     * @param $date \DateTime
     * @param $status Array Core\Model\AppointmentStatus
     * @param $offices Array
     * @return array Appointments
     */
    public function getAppointmentsByStatusAndDate($date, $status, $offices = null)
    {
        $sql = "
            SELECT *
              FROM office_appointment AS oa
             INNER JOIN office o ON o.office_id = oa.office_id
             INNER JOIN system_timezone AS st ON st.timezone_id = o.timezone
        
             WHERE (oa.date + (st.timezone_offset || ' hours')::interval)::timestamp - '" . $date->format('Y-m-d H:i:s') . "'::timestamp < interval '0 hours'
               AND oa.status IN('" . join("', '", $status) . "')
        ";
        
        if ($offices) {
            // Backward compat.
            $offices = array_map(function($i){return is_array($i) ? (int)$i['office_id'] : $i;}, $offices);
            $sql .= "AND oa.office_id IN(" . join(", ", $offices) . ")";
        }
        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }

    /**
     * Returns appointments by date and status.
     *
     * Returns appointments older or equal than $date which status is one of $status array.
     *
     * @param $date \DateTime
     * @param $status Array Core\Model\AppointmentStatus
     * @return array Appointments
     */
    public function getAppointmentsByStatusAndUpdateDate($date, $status)
    {
        $sql = "SELECT *
               FROM office_appointment oa
              WHERE (oa.updated_date is null or oa.updated_date <  '" . $date->format('Y-m-d H:i:s') . "'::timestamp)
                AND oa.created_date < '" . $date->format('Y-m-d H:i:s') . "'::timestamp
                AND oa.status IN('" . join("', '", $status) . "')
                AND '" . $date->format('Y-m-d H:i:s') . "'::timestamp - oa.updated_date < '5 minutes'
                AND '" . $date->format('Y-m-d H:i:s') . "'::timestamp - oa.updated_date > '0 minutes' ";
        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }
    
    /**
     * Returns appointments by created date and status
     */
    public function getAppointmentsByStatusAndCreatedDate($date, $status, $lower_range = null)
    {
        $sql = "SELECT *
               FROM office_appointment oa
              WHERE (oa.updated_date is null or oa.updated_date <  '" . $date->format('Y-m-d H:i:s') . "'::timestamp)
                AND oa.created_date < '" . $date->format('Y-m-d H:i:s') . "'::timestamp
                AND oa.status IN('" . join("', '", $status) . "') ";
        
        if ($lower_range) {
            $sql .= " AND oa.created_date > '" . $lower_range->format('Y-m-d H:i:s') . "'::timestamp ";
        }

        echo $sql . PHP_EOL;

        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }

    /**
     * Retrieves the appointments by status, days until due date and office.
     *
     * Returns appointments which are n days until due date (ie: due date - today <= n)
     * which status is one of $status array and belongs to office.
     *
     * @param Array $status
     * @param Integer $days
     * @param Array $offices
     * @return array
     */
    public function getAppointmentsByStatusAndDaysUntilAppointment($status, $days, $offices = null)
    {
        $date = new \DateTime();
        $sql = "
            SELECT *
            FROM office_appointment AS oa
              INNER JOIN office o ON o.office_id = oa.office_id
              INNER JOIN system_timezone AS st ON st.timezone_id = o.timezone

            WHERE DATE_PART('day', (oa.date + (st.timezone_offset || ' hours')::interval)::timestamp - '" . $date->format('Y-m-d H:i:s') . "'::timestamp) <= " . $days . "
                  AND oa.status IN('" . join("', '", $status) . "')
        ";

        if ($offices) {
            $sql .= "AND oa.office_id IN(" . join(", ", $offices) . ")";
        }

        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }

    /**
     * Retrieve appointments by status and hours until it's due date.
     *
     * @param Array $status
     * @param Integer $hours
     * @param Array $offices
     * @return array
     */
    public function getAppointmentsByStatusAndDueHours($status, $hours, $offices = null)
    {
        $date = new \DateTime();
        $sql = "
            SELECT *
              FROM office_appointment AS oa
             INNER JOIN office o ON o.office_id = oa.office_id
             INNER JOIN system_timezone AS st ON st.timezone_id = o.timezone
             WHERE oa.date + (st.timezone_offset || ' hours')::interval - '" . $date->format('Y-m-d H:i:s') . "'::timestamp <= '" . $hours . " hours'
               AND oa.date + (st.timezone_offset || ' hours')::interval - '" . $date->format('Y-m-d H:i:s') . "'::timestamp > '0 hours'
               AND oa.status IN('" . join("', '", $status) . "')
        ";

        if ($offices) {
            $sql .= "AND oa.office_id IN(" . join(", ", $offices) . ")";
        }
        echo $sql . PHP_EOL;
        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }

    /**
     * Retrieve appoints whose due date is $date
     *
     * @param Array $status
     * @param \DateTime $date
     * @param Array offices
     * @return Array
     */
    public function getAppointmentsByStatusAndDueDate($status, $date, $offices = null)
    {
        $sql = "
            SELECT *
            FROM office_appointment AS oa
              INNER JOIN office o ON o.office_id = oa.office_id
              INNER JOIN system_timezone AS st ON st.timezone_id = o.timezone

            WHERE ((oa.date + (st.timezone_offset || ' hours')::interval)::date - '" . $date->format('Y-m-d') . "'::date) = 0
                  AND oa.status IN('" . join("', '", $status) . "')
        ";

        if ($offices) {
            $sql .= "AND oa.office_id IN(" . join(", ", $offices) . ")";
        }

        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }
    
    public function findDailyAppointments($office, $date, $staff, $date_end) {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.patient', 'p')
           ->andWhere($qb->expr()->eq('o.office', ':office'))
           ->andWhere($qb->expr()->gte('o.date', ':date'))
           ->andWhere($qb->expr()->lt('o.date', ':date_end'))
           ->andWhere($qb->expr()->eq('p.status', ':status'))
           ->orderBy('o.date', 'ASC')
           ->addOrderBy('o.overlapping', 'ASC')
           ->addOrderBy('o.id', 'ASC');
        $qb->setParameter('office', $office);
        $qb->setParameter('date', $date);
        $qb->setParameter('date_end', $date_end);
        $qb->setParameter('status', \Core\Model\SystemUsersStatus::ACTIVE);
        
        if($staff) {
            $qb->andWhere($qb->expr()->eq('o.staff', ':staff'));
            $qb->setParameter('staff', $staff);
        }
        
        return $qb->getQuery()->getResult();
    }
    
    public function findDailyVisits($office, $date, $staff, $status_arrived, $status_seen, $date_end) {
        
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.patient', 'p')
           ->andWhere($qb->expr()->eq('o.office', ':office'))
           ->andWhere($qb->expr()->gte('o.date', ':date'))
           ->andWhere($qb->expr()->lt('o.date', ':date_end'))
           ->andWhere($qb->expr()->orX(
                       $qb->expr()->eq('o.status', ':status_arrived'),
                       $qb->expr()->eq('o.status', ':status_seen')))
           ->andWhere($qb->expr()->eq('p.status', ':status'))
           ->orderBy('o.date', 'ASC')
           ->addOrderBy('o.overlapping', 'ASC')
           ->addOrderBy('o.id', 'ASC');

        $qb->setParameter('office', $office);
        $qb->setParameter('date', $date);
        $qb->setParameter('date_end', $date_end);
        $qb->setParameter('status_arrived', $status_arrived);
        $qb->setParameter('status_seen', $status_seen);

        $qb->setParameter('status', \Core\Model\SystemUsersStatus::ACTIVE);

        if($staff) {
            $qb->andWhere($qb->expr()->eq('o.staff', ':staff'));
            $qb->setParameter('staff', $staff);
        }
         
        return $qb->getQuery()->getResult();
    }
    
    public function findPendingByUserArray($user) {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.patient', 'p')
           ->andWhere($qb->expr()->eq('o.status', ':status'))
           ->andWhere($qb->expr()->eq('o.watch', ':watch'))
           ->andWhere($qb->expr()->eq('p.user', ':user'));
        $qb->setParameter('status', 'pending');
        $qb->setParameter('watch', true);
        $qb->setParameter('user', $user);
        return $qb->getQuery()->getResult();
    }
    
    public function findAppointmentsByStaffAndOffice($staff_id, $office, $date) {
    	$today = clone $date;
    	$tomorrow = $date->add(new \DateInterval('P1D'));
    	
        $fields = array(
            'o.date',
            'CONCAT(p.firstName, \' \', p.lastName) AS patient',
            'CONCAT(s.firstName, \' \', s.lastName) AS staff',
            'a.status',
        );
    
        $qb = $this->createQueryBuilder('o');
        $qb->select($fields)
           ->leftJoin('Core\Model\Patient', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'o.patient = p.id')
           ->leftJoin('Core\Model\OfficeStaff', 's', \Doctrine\ORM\Query\Expr\Join::WITH, 'o.staff = s.id')
           ->leftJoin('Core\Model\SystemAppointmentStatus', 'a', \Doctrine\ORM\Query\Expr\Join::WITH, 'o.status = a.status')
           ->andWhere($qb->expr()->eq('o.office', ':office'))
           ->andWhere($qb->expr()->gte('o.date', ':today'))
           ->andWhere($qb->expr()->lt('o.date', ':tomorrow'))
           ->orderBy('o.date', 'ASC');
    
        $qb->setParameter('office', $office);
        $qb->setParameter('today', $today);
        $qb->setParameter('tomorrow', $tomorrow);
    
        if($staff_id) {
            $qb->andWhere($qb->expr()->in('o.staff', ':staff'));
            $qb->setParameter('staff', $staff_id);
        }
    
        return $qb->getQuery()->getResult();
    }

    public function findVisitsByStaffAndOffice($staff_id, $office, $date, $status_arrived, $status_seen) {
    	$today = clone $date;
    	$tomorrow = $date->add(new \DateInterval('P1D'));
    	
    	$fields = array(
    			'o.id',
    			'o.date',
    			'CONCAT(p.firstName, \' \', p.lastName) AS patient',
    			'p.id as payer',
    			'p.insuranceNumber',
    			'1 AS delay',
    			'1 AS timeWaiting',
    			'CONCAT(s.firstName, \' \', s.lastName) AS staff',
    			'a.status',
    	);
    
    	$qb = $this->createQueryBuilder('o');
    	$qb->select($fields)
    	   ->leftJoin('Core\Model\Patient', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'o.patient = p.id')
    	   ->leftJoin('Core\Model\OfficeStaff', 's', \Doctrine\ORM\Query\Expr\Join::WITH, 'o.staff = s.id')
    	   ->leftJoin('Core\Model\SystemAppointmentStatus', 'a', \Doctrine\ORM\Query\Expr\Join::WITH, 'o.status = a.status')
    	   ->andWhere($qb->expr()->eq('o.office', ':office'))
           ->andWhere($qb->expr()->gte('o.date', ':today'))
           ->andWhere($qb->expr()->lt('o.date', ':tomorrow'))
    	   ->andWhere($qb->expr()->orX(
                       $qb->expr()->eq('o.status', ':status_arrived'),
                       $qb->expr()->eq('o.status', ':status_seen')))
    	   ->orderBy('o.date', 'ASC');
    
    	$qb->setParameter('office', $office);
        $qb->setParameter('today', $today);
        $qb->setParameter('tomorrow', $tomorrow);
    	$qb->setParameter('status_arrived', $status_arrived);
        $qb->setParameter('status_seen', $status_seen);
    
    	if($staff_id) {
    		$qb->andWhere($qb->expr()->in('o.staff', ':staff'));
    		$qb->setParameter('staff', $staff_id);
    	}
    
    	return $qb->getQuery()->getResult();
    }
    
//     public function findBillingVistits( $office_id, $staff_id, $date )
//     {
//     	$conn = $this->getEntityManager()->getConnection();
    	
//     	$sql = "
//     			SELECT 
// 					a.appointment_id, 
// 					a.date AS appointment_date, 
// 					a.ehr_id AS appointment_ehr, 
					
// 					p.patient_id AS patient_id,
// 					(p.first_name || ' ' || p.last_name) AS patient_name,
// 					p.document_number AS patient_document_number,
//     				p.insurance_number AS patient_insurance_number,
    			
// 					d.name AS patient_document_type,
					
// 					ob.visit_type AS ehr_visit_type,
// 					ob.price AS ehr_price,
					
// 					op.price AS office_payer_price,
// 					op.plan AS office_payer_plan,
// 					oi.name AS office_payer_name,
					
// 					pp.price AS patient_payer_price,
// 					pp.plan AS patient_payer_plan,
// 					pi.name AS patient_payer_name

// 				FROM office_appointment a
// 				JOIN patient p ON a.patient_id = p.patient_id
// 				JOIN system_document_type d ON p.document_type_id != '' AND p.document_type_id IS NOT NULL AND p.document_type_id = d.document_type_id
				
//     			LEFT JOIN ehr e on a.ehr_id IS NOT NULL AND e.ehr_id = a.ehr_id AND e.draft = FALSE
// 				LEFT JOIN office_billing ob on a.ehr_id IS NOT NULL AND ob.ehr_id = e.ehr_id
// 				LEFT JOIN office_payer op on a.ehr_id IS NOT NULL AND op.deleted = FALSE AND op.office_payer_id = ob.office_payer_id
// 				LEFT JOIN insurance oi on a.ehr_id IS NOT NULL AND oi.id = op.insurance_id

// 				JOIN patient_payer ppr on ppr.patient_id = p.patient_id
// 				JOIN office_payer pp on pp.deleted = FALSE AND pp.office_payer_id = ppr.office_payer_id
// 				JOIN insurance pi on pi.id = pp.insurance_id

// 				WHERE a.office_id = ?
// 				AND a.staff_id = ?
// 				AND date(a.date) = ?
// 				AND a.status = 'seen'
//     	";
    	
//     	return $conn->fetchAll( $sql, array(
//     		$office_id, $staff_id, $date
//     	));
//     }
    
}