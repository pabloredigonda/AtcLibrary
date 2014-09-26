<?php
namespace Core\Model\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Core\Helper\MonitoringHelper as Helper;
/**
 * PatientVisitMedicament
 */

class PatientVisitMedicament extends AbstractRepository {

    public function getTotalMedicamentsCloseNextDate($office, $staff) {
        
        $sql = "
            " . Helper::selectCount("patient_visit_medicament", "pvm") . "
            " . Helper::joinStatusTable("patient_visit_medicament", "pvm") . "
            " . Helper::joinPatientVisitTable("pvm", $staff) . "
            WHERE TRUE
        	AND (" . Helper::nextVisitDateFilterBetween("-180", "10") . ") 
        ";
        return Helper::getCountResult($sql, $this->getEntityManager(), $office, $staff);
    }
    
    /**
     *@TODO remove me? 
     */
    public function getPending($office, $staff) {
        $qb = $this->createQueryBuilder('o');
        $qb->select('count(o.id)')
           ->innerJoin('o.status', 's', \Doctrine\ORM\Query\Expr\Join::WITH, 's.watch = TRUE')
           ->andWhere($qb->expr()->eq('o.office', ':office'));
        $qb->setParameter('office', $office);

        if($staff) {
            $qb->andWhere($qb->expr()->eq('o.staff', ':staff'));
            $qb->setParameter('staff', $staff);
        }
        
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Returns the prescriptions by patient, office and/or staff.
     *
     * @param Integer $patient_id Filter by an specific patient
     * @param Integer $staff_id   Filter by an specific staff
     * @param Integer $office_id  Filter by an specific office
     * @param String  $status     Filter by status
     *
     * @return array
     */
    public function findMonitoringByParams(
        $patient_id = null,
        $staff_id   = null,
        $office_id  = null,
        $status = 'active'
    ) {
        $sql = "
            SELECT
                pvm.patient_visit_medicament_id		as id,
                pvm.patient_visit_medicament_id		as patient_visit_medicament_id,
                pvm.periodicity						as pvm_periodicity,
                pvm.status							as pvm_status,
                pvm.patient_id						as pvm_patient_id,
                pvm.staff_id						as pvm_staff_id,
                pvm.office_id						as pvm_office_id,
                pvm.start_date						as pvm_start_date,
                pvm.end_date						as pvm_end_date,
                pvm.next_date						as pvm_next_date,
                pvm.medicament						as pvm_medicament,
                pvm.posology						as pvm_posology,
                pvm.created_date					as pvm_created_date,
                pvm.patient_visit_id				as pvm_patient_visit_id,
            
                i.name || ' ' || op.plan            as payer, 

                " . Helper::selectPatientVisitFields() . "
                " . Helper::selectPatientFields() . "
                " . Helper::selectOfficeFields() . "

                (SELECT n.create_date
                FROM notification_target nt
                INNER JOIN notification n ON nt.notification_id = n.id
                WHERE nt.target_id = pvm.patient_visit_medicament_id AND nt.target_entity = 'Core\\\Model\\\PatientVisitMedicament'
        		AND ( (n.status = 0 AND n.mode != 'auto' ) OR (n.status = 2) )
        		ORDER BY create_date DESC
                LIMIT 1 ) AS last_notification,
            
                " . Helper::selectSendNotificationField() . "    

            FROM patient_visit_medicament pvm
                    
            INNER JOIN patient_visit pv     ON pv.patient_visit_id = pvm.patient_visit_id
            INNER JOIN patient p            ON p.patient_id = pv.patient_id
            INNER JOIN office_staff os      ON os.staff_id = pvm.staff_id

	        INNER JOIN patient_payer pp     ON pp.patient_id = p.patient_id
	        INNER JOIN office_payer op      ON pp.office_payer_id = op.office_payer_id
		    INNER JOIN insurance i          ON i.id = op.insurance_id
                    
            " . Helper::joinStatusTable("patient_visit_medicament", "pvm") . "
            " . Helper::joinNotificationSettingTables("patient.patient_visit_medicament_made") . "
            
            WHERE TRUE 
        ";

        $sql .= " AND " . Helper::statusFilter($status);
        $sql .= " AND " . Helper::nextVisitDateFilterBetween("-180", "10") . " ";
        
        if ($patient_id) {
            $sql .= "AND pvm.patient_id = ".$patient_id." ";
        }

        if ($staff_id) {
            $sql .= "AND pvm.staff_id = ".$staff_id." ";
        }

        if ($office_id) {
            $sql .= "AND pvm.office_id = ".$office_id." ";
        }

        $sql .= " ORDER BY pvm.next_date ASC, pv.priority DESC ";
        
        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }
    
    public function findPendingByUserArray($user) {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.patient', 'p')
        ->innerJoin('o.patientStatus', 's')
        ->andWhere($qb->expr()->eq('p.user', ':user'))
        ->andWhere($qb->expr()->eq('s.watch', ':watch'));
    
        $qb->setParameter('user', $user);
        $qb->setParameter('watch', true);
        return $qb->getQuery()->getResult();
    }

    public function findByPatientVisit($patientVisitId)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere($qb->expr()->eq('p.patientVisit', ':patientVisitId'));
    
        $qb->setParameter('patientVisitId', $patientVisitId);
        return $qb->getQuery()->getResult();
    }
    
}