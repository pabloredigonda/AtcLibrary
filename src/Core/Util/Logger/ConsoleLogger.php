<?php

/**
 * General class.
 *
 * PHP version 5.4
 *
 * @category General
 * @package  Core\Util\Logger
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @version  GIT:<>
 * @link     https://github.com/desyncr
 */
namespace Core\Util\Logger;

use Traversable;

/**
 * Class ConsoleLogger
 *
 * @category General
 * @package  Core\Util\Logger
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
 */
class ConsoleLogger extends AbstractLogger
{
    /**
     * Logs a string to STD OUTPUT
     *
     * @param String $str  String to log
     * @param String $type Log level
     *
     * @return null
     */
    public function log($str, $type = 'LOG')
    {
        if ($this->loggingEnabled()) {
            echo '[' . date('c') . '][' . $type . '] ' . $str . PHP_EOL;
        }
    }
}
 
