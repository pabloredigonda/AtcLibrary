<?php
namespace Core\Util;

/**
 * Global constants
 * 
 * @author Nicolas
 */
class Constants {
    
    const FIELD_DELIMITER = "|";
    const ARRAY_DELIMITER = ",";

    // Office module name
    const OFFICE_MODULE = 'office';
    
    // Patient module name
    const PATIENT_MODULE = 'patient';
    
    // Login
    const MAX_LOGIN_ATTEMPS = 5;
    
    // Default Timezone
    const DEFAULT_TIMEZONE = "America/Argentina/Buenos_Aires";
    
    // Path
    const PATH_DELIMITER = "/";
    const PUBLIC_PATH = "/public";
    const PROFILE_PICTURE_PLACEHOLDER = "avatar.png";
    const PROFILE_PICTURE_PLACEHOLDER_PATH = "/assets/img";
    const PROFILE_PICTURE_PATH = "/assets/img/profile/patient";
    const PROFILE_PICTURE_PREFIX = "profile_";
    const PROFILE_PICTURE_SMALL_PREFIX = "small_";
    const PROFILE_STAFF_PICTURE_PATH = "/assets/img/profile/staff";
    
    // Appointment durations
    public static $appointment_durations = array(10, 15, 20, 30, 60);
    
    // Number of days where we should check for a pending next visit. This constant is used an interval.
    // Appointment date beetwen [next_visit - const : next_visit + const] trigger and patient visit status update.
    const PATIENT_VISIT_DONE_DAYS = "15";
    
    // Number of days from today where we check for next_visits
    const PATIENT_VISIT_NEXT_VISIT_DAYS = '+5 days';
    
    // Number of days from today where we check for next medicament prescriptions
    const PATIENT_VISIT_NEXT_MEDICAMENT_DAYS = '+10 days';
    
    // Messenger
    const MESSAGE_PAGE_SIZE = 10;
    
    // Routes
    const ROUTE_OFFFICE_DASHBOARD = "office/dashboard";
    const ROUTE_OFFICE_WIZARD = "wizard";

    const ROUTE_CANONICAL = 'http://saludmovil.net/';
    const ROUTE_BASE = 'http://sm.dev.braintive.com/';
}

?>