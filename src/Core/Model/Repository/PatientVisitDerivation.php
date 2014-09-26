<?php
namespace Core\Model\Repository;

use Doctrine\ORM\Query\ResultSetMapping;    
use Core\Helper\MonitoringHelper as Helper;
/**
 * PatientVisitDerivation
 */

class PatientVisitDerivation extends AbstractRepository
{

    public function findByPatientVisit($patientVisitId)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere($qb->expr()->eq('p.patientVisit', ':patientVisitId'));

        $qb->setParameter('patientVisitId', $patientVisitId);
        return $qb->getQuery()->getResult();
    }

    public function getPending($office, $staff)
    {
        $sql = "
            " . Helper::selectCount("patient_visit_derivation", "pvd") . "
            " . Helper::joinStatusTable("patient_visit_derivation", "pvd") . "
            " . Helper::joinPatientVisitTable("pvd", $staff) . "    
            WHERE TRUE
        	AND " . Helper::nextVisitDateFilterBefore() . "
            AND " . Helper::nextVisitDateFilterAfter("pvd");

        return Helper::getCountResult($sql, $this->getEntityManager(), $office, $staff);
    }

    public function findByKey($key)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->andWhere($qb->expr()->eq('o.key', ':key'));
        $qb->setParameter('key', $key);

        $result = $qb->getQuery()->getResult();
        return $result ? $result[0] : null;
    }

    public function getDerivationsByDate($date = null)
    {
        $sql = "
            SELECT *
            FROM patient_visit_derivation p
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
        $staff_id = null,
        $office_id = null,
        $status = 'active'
    ) {
        $sql = "
            SELECT
                pvd.patient_visit_derivation_id  as id,
                pvd.patient_visit_derivation_id  as pvd_patient_visit_derivation_id,
                pvd.status                       as pvd_status,
                pvd.patient_id                   as pvd_patient_id,
                pvd.staff_id                     as pvd_staff_id,
                pvd.office_id                    as pvd_office_id,
                pvd.date                         as pvd_date,
                pvd.office_staff_name            as pvd_office_staff_name,
                pvd.specialty_name               as pvd_specialty_name,
                pvd.office_provider_name         as pvd_office_provider_name,
                pvd.key                          as pvd_key,
                pvd.created_date                 as pvd_created_date,
                pvd.patient_visit_id             as pvd_patient_visit_id,

                " . Helper::selectPatientVisitFields() . "
                " . Helper::selectPatientFields() . "
                " . Helper::selectOfficeFields() . "
            
                (SELECT n.create_date
                FROM notification_target nt
                INNER JOIN notification n ON nt.notification_id = n.id
                WHERE nt.target_id = pvd.patient_visit_derivation_id AND nt.target_entity = 'Core\\\Model\\\PatientVisitDerivation'
        		AND ( (n.status = 0 AND n.mode != 'auto' ) OR (n.status = 2) )
                ORDER BY create_date DESC
                LIMIT 1 ) AS last_notification,
            
                " . Helper::selectSendNotificationField() . "
            

            FROM patient_visit_derivation pvd
            INNER JOIN patient_visit pv ON pv.patient_visit_id = pvd.patient_visit_id
            INNER JOIN office_staff os ON os.staff_id = pv.staff_id
            INNER JOIN patient p ON p.patient_id = pv.patient_id
            " . Helper::joinStatusTable("patient_visit_derivation", "pvd") . "
            " . Helper::joinNotificationSettingTables("survey.survey_derivation_reminder") . " 
            
            WHERE TRUE
        ";

        $sql.= " AND " . Helper::statusFilter($status);
        $sql.= " AND " . Helper::nextVisitDateFilterBefore();
        $sql.= " AND " . Helper::nextVisitDateFilterAfter("pvd");
        
        if ($patient_id) {
            $sql .= "AND pvd.patient_id = " . $patient_id . " ";
        }

        if ($staff_id) {
            $sql .= "AND pvd.staff_id = " . $staff_id . " ";
        }

        if ($office_id) {
            $sql .= "AND pvd.office_id = " . $office_id . " ";
        }

        $sql .= " ORDER BY pv.priority DESC, pvd.date ASC ";
        
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
        $qb = $this->createQueryBuilder('pvd');

        $qb->andWhere($qb->expr()->in('pvd.patient', ':patients'));
        $qb->setParameter('patients', $arrPatients);

        return $qb->getQuery()->getResult();
    }
}