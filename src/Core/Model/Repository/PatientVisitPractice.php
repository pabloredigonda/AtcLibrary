<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\ResultSetMapping;
use Core\Helper\MonitoringHelper as Helper;
/**
 * PatientVisitPractice
 */

class PatientVisitPractice extends AbstractRepository {

    public function findByPatientVisit($patientVisitId) {
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere($qb->expr()->eq('p.patientVisit', ':patientVisitId'));
    
        $qb->setParameter('patientVisitId', $patientVisitId);
        return $qb->getQuery()->getResult();
    }
    
    public function getPending($office, $staff) {
        
        $sql = " 
            SELECT count(*) FROM (
                SELECT COUNT(1) FROM patient_visit_practice pvp " . 
                    Helper::joinStatusTable("patient_visit_practice", "pvp") . 
                    Helper::joinPatientVisitTable("pvp", $staff) . "    
                 WHERE TRUE 
            	   AND " . Helper::nextVisitDateFilterBefore() . " 
            	   AND " . Helper::nextVisitDateFilterAfter("pvp"). "
                 GROUP BY pv.patient_id, pv.patient_visit_id  
            ) as cnt";
        	
        return Helper::getCountResult($sql, $this->getEntityManager(), $office, $staff);
    }
    
    public function findByKey($key) {
        $qb = $this->createQueryBuilder('o');
        $qb->andWhere($qb->expr()->eq('o.key', ':key'));
        $qb->setParameter('key', $key);
        
        $result = $qb->getQuery()->getResult();
        return $result ? $result[0] : null;
    }
     
    public function getPracticesByDate($date = null) {
        $sql = "
            SELECT *
            FROM patient_visit_practice p
            INNER JOIN patient_visit v ON v.patient_visit_id = p.patient_visit_id
            WHERE CAST(EXTRACT(epoch FROM (v.next_visit - 'today'))/3600/24 AS integer) = 
                  CAST(EXTRACT(epoch FROM (v.next_visit - p.date))/3600/24 AS integer) / 2
        ";
        echo $sql . PHP_EOL;
        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }

    /**
     * Returns the derivations by patient, office and/or staff.
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
                pvp.patient_visit_practice_id 	 as id,
                pvp.patient_visit_practice_id 	 as pvp_patient_visit_practice_id,
                pvp.status                    	 as pvp_status,
                pvp.date                      	 as pvp_date,
                pvp.practice                  	 as pvp_practice,
                pvp.recommendedprovider       	 as pvp_recommendedprovider,
                pvp.created_date              	 as pvp_created_date,
                pvp.patient_visit_id          	 as pvp_patient_visit_id,

                " . Helper::selectPatientVisitFields() . "
                " . Helper::selectPatientFields() . "
                " . Helper::selectOfficeFields() . "
            
                (SELECT n.create_date
                FROM notification_target nt
                INNER JOIN notification n ON nt.notification_id = n.id
                WHERE nt.target_id = pvp.patient_visit_practice_id AND nt.target_entity = 'Core\\\Model\\\PatientVisitPractice'
        		AND ( (n.status = 0 AND n.mode != 'auto' ) OR (n.status = 2) )
        		ORDER BY create_date DESC
                LIMIT 1 ) AS last_notification,
            
                " . Helper::selectSendNotificationField() . "

            FROM patient_visit_practice pvp
            INNER JOIN patient_visit pv ON pv.patient_visit_id = pvp.patient_visit_id
            INNER JOIN office_staff os ON os.staff_id = pv.staff_id
            INNER JOIN patient p ON p.patient_id = pv.patient_id
            " . Helper::joinStatusTable("patient_visit_practice", "pvp") . "
            " . Helper::joinNotificationSettingTables("survey.survey_practice_reminder") . "    
            WHERE TRUE 
        ";

        $sql .= " AND " . Helper::statusFilter($status);
        $sql .= " AND " . Helper::nextVisitDateFilterBefore();
        $sql .= " AND " . Helper::nextVisitDateFilterAfter("pvp");
        
        if ($patient_id) {
            $sql .= "AND pvp.patient_id = ".$patient_id." ";
        }

        if ($staff_id) {
            $sql .= "AND pvp.staff_id = ".$staff_id." ";
        }

        if ($office_id) {
            $sql .= "AND pvp.office_id = ".$office_id." ";
        }

        $sql .= " ORDER BY pv.priority DESC, pvp.date ASC ";
        
        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }
    
    /**
     * findAllByPatient
     *
     * @param Array $arrPatients Patients array
     *
     * @return array
     */
    public function findAllByPatient($arrPatients)
    {
        $qb = $this->createQueryBuilder('pvp');

        $qb->andWhere($qb->expr()->in('pvp.patient', ':patients'));
        $qb->setParameter('patients', $arrPatients);

        return $qb->getQuery()->getResult();
    }
}