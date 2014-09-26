<?php
/**
 * Core\Util\Logger
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

use Zend\Log\LoggerInterface;

/**
 * Class AggregateLogger
 *
 * @category General
 * @package  Core\Util\Logger
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
 */
class AggregateLogger extends AbstractLogger
{
    /**
     * @var array
     */
    protected $loggers = array();

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
        /** @var \Core\Util\Logger\AbstractLogger $logger */
        foreach ($this->loggers as $logger) {
            $logger->log($str, $type);
        }
    }

    /**
     * add
     *
     * @param LoggerInterface $logger
     *
     * @return mixed
     */
    public function add(LoggerInterface $logger)
    {
        array_push($this->loggers, $logger);
    }

    /**
     * has
     *
     * @param $class
     *
     * @return mixed
     */
    public function has($class)
    {
        foreach ($this->loggers as $logger) {
            if ($logger instanceof $class) {
                return true;
            }
        }

        return false;
    }
}
 
