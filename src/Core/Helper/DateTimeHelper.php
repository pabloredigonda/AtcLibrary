<?php

/**
 * General class.
 *
 * PHP version 5.4
 *
 * @category General
 * @package  Core\Helper
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @version  GIT:<>
 * @link     https://github.com/desyncr
 */
namespace Core\Helper;

/**
 * Class DateTimeHelper
 *
 * @category General
 * @package  Core\Helper
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
 */
class DateTimeHelper
{
    public static function hourStringToInterval($hours)
    {
        return explode(':', $hours);
    }

    public static function normalizeDateTime(\DateTime $date, $timezoneOffset)
    {
        $date = clone $date;
        list($hour, $minute) = explode(':', $timezoneOffset);
        $date->sub(\DateInterval::createFromDateString($hour . ' hours ' . $minute . ' minutes'));

        return $date;
    }

    /**
     * Forces a \DateTime object to fit into a fixed timeframe given by floor and ceil hours as well as
     * available days.
     *
     * If a \DateTime object doesn't fit into the given timeframe (eg: 10AM - 15PM) the hour is shifted to
     * the next available hour.
     *
     * Per example: Given the hour 09AM. It doesn't fit the hour range 10AM - 15PM, because it's below
     * the lower range hour (10AM) by an hour; so the result would be 10AM.
     *
     * Hour range : 10AM - 15PM
     * Hour       : 09AM
     * Result     : 10AM
     *
     * A more complex example: Given the hour 16PM, it's out of the timeframe 10AM - 15PM, so the hour is shifted
     * to the next day at 10AM (the lower range):
     *
     * Hour range : 10AM - 15PM
     * Hour       : 16PM
     * Result     : 10AM (next day)
     *
     * The day in which the \DateTime falls is also governed by the same rules and the day is shifted until it
     * falls on an available day.
     *
     * @param \DateTime     $date     The \DateTime the frame
     * @param array         $boundary The boundaries definition
     * @param \DateTimeZone $timezone If this argument is provided the $date is converted to this timezone
     *
     * @return \DateTime    DateTime in UTC
     */
    public static function roundToBoundary(\DateTime $date, Array $boundary, \DateTimeZone $timezone = null)
    {
        $date = clone $date;
        if ($timezone) {
            $date->setTimezone($timezone);
        }
        $h = $date->format('H');
        if ($h < $boundary['floor']) {
            $date->setTime($boundary['floor'], 0);
        }
        if ($h > $boundary['ceil']) {
            $date->setTime($boundary['floor'], 0);
            $date->modify('+1 day');
        }
        if (!empty($boundary['days'])) {
            while (!in_array($date->format('w'), $boundary['days'])) {
                $date->modify('+1 day');
            }
        }
        $date->setTimezone(new \DateTimeZone('UTC'));
        return $date;
    }
}
 