<?php
/**
 * General
 *
 * PHP version 5.4
 *
 * @category General
 * @package  Core\Util\Logger
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @version  SVN:<>
 * @link     https://docs.saludmovil.net
 */
namespace Core\Util\Logger;

use Zend\Log\LoggerInterface;

/**
 * Core\Util\Logger
 *
 * @category General
 * @package  Core\Util\Logger
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://docs.saludmovil.net
 */
abstract class AbstractLogger implements LoggerInterface
{
    /**
     * @var Bool enable/disable logging
     */
    protected $logging = true;

    /**
     * log
     *
     * @param        $str
     * @param string $type
     *
     * @return mixed
     */
    abstract public function log($str, $type = 'LOG');

    /**
     * emerg
     *
     * @param string            $message Message to log
     * @param array|Traversable $extra   Extra information
     *
     * @return null
     */
    public function emerg($message, $extra = array())
    {
        $this->log($message, 'EMERG');
    }

    /**
     * alert
     *
     * @param string            $message Message to log
     * @param array|Traversable $extra   Extra information
     *
     * @return null
     */
    public function alert($message, $extra = array())
    {
        $this->log($message, 'ALERT');
    }

    /**
     * crit
     *
     * @param string            $message Message to log
     * @param array|Traversable $extra   Extra information
     *
     * @return null
     */
    public function crit($message, $extra = array())
    {
        $this->log($message, 'CRIT');
    }

    /**
     * err
     *
     * @param string            $message Message to log
     * @param array|Traversable $extra   Extra information
     *
     * @return null
     */
    public function err($message, $extra = array())
    {
        $this->log($message, 'ERR');
    }

    /**
     * warn
     *
     * @param string            $message Message to log
     * @param array|Traversable $extra   Extra information
     *
     * @return null
     */
    public function warn($message, $extra = array())
    {
        $this->log($message, 'WARN');
    }

    /**
     * notice
     *
     * @param string            $message Message to log
     * @param array|Traversable $extra   Extra information
     *
     * @return null
     */
    public function notice($message, $extra = array())
    {
        $this->log($message, 'NOTICE');
    }

    /**
     * info
     *
     * @param string            $message Message to log
     * @param array|Traversable $extra   Extra information
     *
     * @return null
     */
    public function info($message, $extra = array())
    {
        $this->log($message, 'INFO');
    }

    /**
     * debug
     *
     * @param string            $message Message to log
     * @param array|Traversable $extra   Extra information
     *
     * @return null
     */
    public function debug($message, $extra = array())
    {
        $this->log($message, 'DEBUG');
    }

    /**
     * setLogging
     *
     * @param Bool $bool True or False
     *
     * @return null
     */
    public function setLogging($bool)
    {
        $this->logging = (bool)$bool;
    }

    /**
     * getLogging
     *
     * @return Bool True or False
     */
    public function getLogging()
    {
        return $this->logging;
    }

    /**
     * loggingEnabled
     *
     * @return mixed
     */
    public function loggingEnabled()
    {
        return $this->getLogging();
    }

    /**
     * enableLogging
     *
     * @return mixed
     */
    public function enableLogging()
    {
        $this->setLogging(true);
    }

    /**
     * disableLogging
     *
     * @return mixed
     */
    public function disableLogging()
    {
        $this->setLogging(false);
    }
}
