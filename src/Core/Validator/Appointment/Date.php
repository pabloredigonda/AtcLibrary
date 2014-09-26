<?php
namespace Core\Validator\Appointment;

use Core\Validator\SMValidator;

class Date extends SMValidator
{

    const NOT_AVAILABILITY = 'notAvailability';

    const OVERLAPPING_NOT_ALLOWED = 'overlappingNotAllowed';

    const OVERLAPPING_EXCEDED = 'overlappingExceded';

    protected $messageTemplates = array(
        self::NOT_AVAILABILITY => "There is not availability for the selected date",
        self::OVERLAPPING_NOT_ALLOWED => "The professional does not accept overlapped appointments",
        self::OVERLAPPING_EXCEDED => "The overlapping limit was exceeded"
    );

    /**
     * Validates:
     * if there is available appointments for the selected date
     * When overlapping param is set, if this is under the limit
     * 
     * @param Datetime $date            
     * @see \Zend\Validator\ValidatorInterface::isValid()
     * @return bool
     */
    public function isValid($date)
    {
        // required options appointmentService, workday
        $staffId = $this->getOption("staffId");
        $overlapping = $this->getOption("overlapping");
        $id = $this->getOption("id");
        $office = $this->getOption("office");
        
        // workDay of the day
        $workDay = $this->getOption("workdayService")->findByDateAndStaffId($date, $staffId);
        
        // Valid Workday ?
        if (! $workDay) {
            $this->error(self::NOT_AVAILABILITY);
            return false;
        }
        
        // Accept overlaping ?
        if ($overlapping && ! $workDay->getAcceptOverlapping()) {
            $this->error(self::OVERLAPPING_NOT_ALLOWED);
            return false;
        }
        
        // Open Appointments of the day
        $dailyAppointments = $this->getOption("appointmentService")->findOpenDailyAppointments($office, $date, $staffId);
        
        $overlapedAppointmentsCount = 0;
        foreach ($dailyAppointments as $appointment) {
            if ($appointment->getOverlapping() && $id !=$appointment->getId() ) {
                
                $workdayTimes = $workDay->getWorkdayTimes();
                
                foreach ($workdayTimes as $workdayTime) {

                    if($workdayTime->getDay() != $appointment->getDate()->format("w")){
                    	continue;
                    }

                    $isCrossTime = $this->getOption("appointmentService")->isCrossTime(
                        $workdayTime->getTimeStart(), $workdayTime->getTimeEnd(), $appointment->getDate(), $appointment->getDateEnd()
                    );
                    
                    if ($isCrossTime) {
                        $overlapedAppointmentsCount ++;
                    }
                }
            }
        }
        
        // Overlaping exceded?
        if ($overlapping) {
            
            if ($overlapedAppointmentsCount >= $workDay->getMaxOverlapping()) {
                $this->error(self::OVERLAPPING_EXCEDED);
                return false;
            } else {
                return true;
            }
        }
        
        $endDate = clone ($date);
        $endDate->add(date_interval_create_from_date_string($workDay->getAppointmentDuration() . " minutes"));
        
        // Crossed appointment
        foreach ($dailyAppointments as $appointment) {

            if( $id == $appointment->getId()){
            	continue;
            }
            
            $isCrossTime = $this->getOption("appointmentService")->isCrossTime(
                $date, $endDate, $appointment->getDate(), $appointment->getDateEnd()
            );
            
            if ($isCrossTime) {
                $this->error(self::NOT_AVAILABILITY);
                return false;
            }
        }
        
        return true;
    }
}

?>