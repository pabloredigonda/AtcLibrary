<?php
/**
 * Core\Dto
 *
 * PHP version 5.4
 *
 * @category General
 * @package  Core\Dto
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @version  GIT:<>
 * @link     https://github.com/desyncr
 */
namespace Core\Dto;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class CalendarDTO
 *
 * @category General
 * @package  Core\Dto
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
 */
class CalendarDTO extends ResponseDTO
{
    /**
     * @Serializer\Groups({"list", "details"})
     */
    public $appointments = array();

    /**
     * @Serializer\Groups({"list", "details"})
     */
    public $workdays = array();

    /**
     * addAppointment
     *
     * @param Object $item
     *
     * @return mixed
     */
    public function addAppointment($item)
    {
        array_push($this->appointments, $item);
    }

    /**
     * addAppointments
     *
     * @param Array $arrItems
     *
     * @return mixed
     */
    public function addAppointments($arrItems)
    {
        foreach($arrItems as $item) {
            $this->addAppointment($item);
        }
    }

    /**
     * addWorkday
     *
     * @param Object $workday
     *
     * @return mixed
     */
    public function addWorkday($workday)
    {
        array_push($this->workdays, $workday);
    }

    /**
     * addWorkdays
     *
     * @param Array $arrWorkdays
     *
     * @return mixed
     */
    public function addWorkdays($arrWorkdays)
    {
        foreach($arrWorkdays as $workday) {
            $this->addWorkday($workday);
        }
    }
}