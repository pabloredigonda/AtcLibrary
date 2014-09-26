<?php
namespace Core\Model\Repository;

use Doctrine\DBAL\Types\Type;
use Core\Util\Constants;
use Doctrine\ORM\Query\ResultSetMapping;
use Core\Helper\MonitoringHelper as Helper;
/**
 * PatientVisit
 */

class PatientVisit extends AbstractRepository {

    public function getTotalPatientsCloseNextAppointment($office, $staff) {
        $sql = "
        	" . Helper::selectCount("patient_visit", "pv") . "
        	" . Helper::joinStatusTable("patient_visit", "pv") . "
            WHERE  pv.next_visit IS NOT NULL 
        	AND office_id = :office
        	AND " . Helper::nextVisitDateFilterBetween() . "
        ";
        
        return Helper::getCountResult($sql, $this->getEntityManager(), $office, $staff);
    }
    
    public function updateStatus($office, $staff, $patient, $appointmentDate) {
        
        $conn = $this->getEntityManager()->getConnection();

        $sql  = " UPDATE user_visit ";
        $sql .= "    SET status = 'appointment' ";
        $sql .= "  WHERE patient_visit_id IN (";
            $sql .= " SELECT patient_visit_id ";
            $sql .= "   FROM patient_visit ";
            $sql .= "  WHERE next_visit IS NOT NULL ";
            $sql .= "    AND next_visit::date - " . Constants::PATIENT_VISIT_DONE_DAYS . " <= :appointment_date ";
            $sql .= "    AND next_visit::date + " . Constants::PATIENT_VISIT_DONE_DAYS . " >= :appointment_date ";
            $sql .= "    AND patient_id = :patient_id ";
            $sql .= "    AND staff_id = :staff_id ";
            $sql .= "    AND office_id = :office_id ";
            $sql .= "    AND status = 'pending' ";
        $sql .= ")";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('appointment_date', $appointmentDate, Type::DATETIME);
        $stmt->bindValue('patient_id', $patient);
        $stmt->bindValue('staff_id', $staff);
        $stmt->bindValue('office_id', $office);
        $stmt->execute();
        
        $sql  = " UPDATE patient_visit ";
        $sql .= "    SET status = 'done' ";
        $sql .= "  WHERE next_visit IS NOT NULL ";
        $sql .= "    AND next_visit::date - " . Constants::PATIENT_VISIT_DONE_DAYS . " <= :appointment_date ";
        $sql .= "    AND next_visit::date + " . Constants::PATIENT_VISIT_DONE_DAYS . " >= :appointment_date ";
        $sql .= "    AND patient_id = :patient_id ";
        $sql .= "    AND staff_id = :staff_id ";
        $sql .= "    AND office_id = :office_id ";
        $sql .= "    AND status = 'pending' ";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('appointment_date', $appointmentDate, Type::DATETIME);
        $stmt->bindValue('patient_id', $patient);
        $stmt->bindValue('staff_id', $staff);
        $stmt->bindValue('office_id', $office);
        $stmt->execute();
    }

    /**
     * Retrieves visits which are $days until it's due date and have a status
     * in once of $status.
     *
     * @param Integer $days   Number of days
     * @param String  $status Status
     *
     * @return array
     */
    public function getVisitsByReminderDays($days, $status)
    {
        $date = new \DateTime();
        $sql = "
            SELECT *
              FROM patient_visit AS pv
             INNER JOIN office o ON o.office_id = pv.office_id
             INNER JOIN system_timezone AS st ON st.timezone_id = o.timezone
             WHERE pv.next_visit + (st.timezone_offset || ' hours')::interval - '" . $date->format('Y-m-d H:i:s') . "'::timestamp < '" . $days . " days'
               AND pv.next_visit + (st.timezone_offset || ' hours')::interval - '" . $date->format('Y-m-d H:i:s') . "'::timestamp > '" . ($days-1) . " days'
               AND pv.status IN('" . join("', '", $status) . "')
        ";    
        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }

    /**
     * Returns the appointments by patient, office and/or staff.
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
        $staff_id = null,
        $office_id = null,
        $status = 'active'
    ) {
        $sql = "
            SELECT
                pv.patient_visit_id 		as id,
                " . Helper::selectPatientVisitFields() . "
                " . Helper::selectPatientFields() . "
                " . Helper::selectOfficeFields() . "
                		
               (SELECT n.create_date
                  FROM notification_target nt
                 INNER JOIN notification n ON nt.notification_id = n.id
                 WHERE nt.target_id = pv.patient_visit_id
                   AND nt.target_entity = 'Core\\\Model\\\PatientVisit'
                   AND ( (n.status = 0 AND n.mode != 'auto' ) OR (n.status = 2) )
                 ORDER BY n.create_date DESC
                 LIMIT 1) AS last_notification,
            
                " . Helper::selectSendNotificationField() . "

            FROM patient_visit pv
                INNER JOIN patient p ON p.patient_id = pv.patient_id
        		" . Helper::joinStatusTable("patient_visit", "pv") . "
                INNER JOIN office_staff os ON os.staff_id = pv.staff_id
        		" . Helper::joinNotificationSettingTables("patient.patient_visit_next_visit_reminder") . "		
            
            WHERE  pv.next_visit IS NOT NULL
        ";

        $sql .= " AND " . Helper::statusFilter($status);
        $sql .= " AND " . Helper::nextVisitDateFilterBetween() . " ";
        
        if ($patient_id) {
            $sql .= "AND pv.patient_id = ".$patient_id." ";
        }

        if ($staff_id) {
            $sql .= "AND pv.staff_id = ".$staff_id." ";
        }

        if ($office_id) {
            $sql .= "AND pv.office_id = ".$office_id." ";
        }
        
        $sql .= " ORDER BY pv.priority DESC, pv_next_visit ASC ";

        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }

    /**
     * Find PatientVisit by survey key
     *
     * @param String $key Survey key
     *
     * @return \Core\Model\PatientVisit|null
     */
    public function findByKey($key)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->andWhere($qb->expr()->eq('o.key', ':key'));
        $qb->setParameter('key', $key);

        $result = $qb->getQuery()->getResult();
        return $result ? $result[0] : null;
    }
}