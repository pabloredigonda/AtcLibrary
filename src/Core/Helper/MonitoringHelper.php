<?php
namespace Core\Helper;

use Doctrine\ORM\Query\ResultSetMapping;

class MonitoringHelper
{
    public static function getCountResult( $sql, $em, $office, $staff )
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('count', 'count');
        
        $query = $em->createNativeQuery($sql, $rsm);
        
        $query->setParameter('office', $office->getId());
        
        if($staff) {
            $query->setParameter('staff', $staff);
        }
        
        $result = $query->getSingleResult();
        return $result['count'];
    }
    
    public static function joinStatusTable( $table, $alias )
    {
        $status_table = "system_{$table}_status";
    
        return " INNER JOIN {$status_table} stt ON stt.status = {$alias}.status AND stt.watch = TRUE";
    }
    
    public static function joinNotificationSettingTables( $notification_type_id )
    {
        return "
            LEFT JOIN notification_setting_office no ON no.office_id = pv.office_id AND no.notification_type_id = '{$notification_type_id}'
            LEFT JOIN notification_setting_staff ns ON ns.staff_id = pv.staff_id AND ns.notification_type_id = '{$notification_type_id}'
            LEFT JOIN notification_setting_staff_patient nsp ON nsp.staff_id = pv.staff_id AND nsp.patient_id = pv.patient_id AND nsp.notification_type_id = '{$notification_type_id}'
            LEFT JOIN notification_setting_office_patient sop ON sop.office_id = pv.office_id AND sop.patient_id = pv.patient_id AND sop.notification_type_id = '{$notification_type_id}'
           ";
    }

    public static function selectCount( $table, $alias )
    {
        return "SELECT COUNT(1) as count FROM {$table} {$alias}";
    }
    
    public static function joinPatientVisitTable( $alias, $staff )
    {
        $join = " INNER JOIN patient_visit pv ON pv.patient_visit_id = {$alias}.patient_visit_id 
                AND pv.next_visit IS NOT NULL
                AND pv.office_id = :office ";
        
        if($staff){
            $join.= " AND pv.staff_id = :staff ";
        }
        
        return $join;
    }
    
    public static function selectPatientFields()
    {
        return "
                p.patient_id            		 as patient_patient_id,
                p.status                		 as patient_status,
                p.address_country_id    		 as patient_address_country_id,
                p.address_state_id      		 as patient_address_state_id,
                p.document_type_id      		 as patient_document_type_id,
                p.user_id               		 as patient_user_id,
                p.first_name            		 as patient_first_name,
                p.middle_name           		 as patient_middle_name,
                p.last_name             		 as patient_last_name,
                p.document_number       		 as patient_document_number,
                p.email                 		 as patient_email,
                p.cel_phone_prefix      		 as patient_cel_phone_prefix,
                p.cel_phone_number      		 as patient_cel_phone_number,
                p.home_phone_prefix     		 as patient_home_phone_prefix,
                p.home_phone_number     		 as patient_home_phone_number,
                p.work_phone_prefix     		 as patient_work_phone_prefix,
                p.work_phone_number     		 as patient_work_phone_number,
                p.insurance_number               as patient_insurance_number,
            ";
    }
    
    public static function selectSendNotificationField()
    {
        return "
                CASE
                    WHEN no.office_id NOTNULL THEN false
                    WHEN ns.staff_id NOTNULL THEN false
                    WHEN nsp.enabled NOTNULL THEN nsp.enabled
                    WHEN sop.patient_id NOTNULL THEN false
                    ELSE true
                    END AS send_notification
            ";
    }
    
    public static function selectOfficeFields()
    {
        return "
                os.staff_id 				as office_staff_id,
                os.email 					as office_staff_email,
                os.first_name               as office_staff_first_name,
                os.last_name                as office_staff_last_name,
            ";
    }
    
    public static function selectPatientVisitFields()
    {
        return "
                pv.priority					as priority,
                pv.patient_visit_id 		as pv_patient_visit_id,
                pv.patient_id 				as patient_id,
                pv.staff_id 				as staff_id,
                pv.office_id 				as office_id,
                pv.status 					as pv_status,
                pv.created_date 			as pv_created_data,
                pv.ehr_id 					as pv_ehr_id,
                pv.next_visit 				as pv_next_visit,
                pv.email 					as pv_email,
                pv.shared_document 			as pv_shared_document,
                pv.send_medication          as pv_send_medication,
                pv.send_practice 			as pv_send_practice,
                pv.send_derivation 			as pv_send_derivation,
                pv.send_indication          as pv_send_indication,
            ";
    }
    
    public static function nextVisitDateFilterBetween( $before = "-180", $after = "30")
    {
        return " (EXTRACT(days FROM pv.next_visit - CURRENT_DATE) BETWEEN {$before} AND {$after}) ";
    }
    
    /**
     * date of next visit up to 180 days before
     */
    public static function nextVisitDateFilterBefore( $before = "-180")
    {
        return "(EXTRACT(days FROM pv.next_visit - CURRENT_DATE) >= {$before} )";
    }
    
    /**
     * n_diff: next_visit_date - visit_date / 2
     * date of next visit up to "n_diff" days after today
     */
    public static function nextVisitDateFilterAfter( $alias )
    {
        return " ( EXTRACT(days FROM pv.next_visit - CURRENT_DATE) <= CEIL( EXTRACT(days FROM (pv.next_visit - {$alias}.date)) / 2 ) )";
    }

    public static function statusFilter($status = 'active')
    {
        return " p.status = '${status}' ";
    }
}

?>