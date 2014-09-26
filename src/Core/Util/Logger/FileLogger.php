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

/**
 * Class FileLogger
 *
 * @category General
 * @package  Core\Util\Logger
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
 */
class FileLogger extends ConsoleLogger
{
    const INFO = 'info';
    const DEBUG = 'debug';
    const WARN = 'warning';
    const ERROR = 'error';
    
    /**
     * @var
     */
    protected $file;

    /**
     * Constructor
     *
     * @param String $filename Log file name
     * @param String $path     Log path
     * @param String $ext      Log file extension
     */
    public function __construct($filename, $path = null, $ext = 'log')
    {
        $this->setFilename($filename, $path, $ext);
    }

    /**
     * setFilename
     *
     * @param        $filename
     * @param null   $path
     * @param string $ext
     *
     * @return mixed
     */
    public function setFilename($filename, $path = null, $ext = 'log')
    {
        $this->file = ($path ?: sys_get_temp_dir()) . '/' . $filename . '.' . $ext;
    }

    /**
     * getFilename
     *
     * @return mixed
     */
    public function getFilename()
    {
        return $this->file;
    }

    /**
     * log
     *
     * @param String $str
     * @param string $type
     *
     * @return mixed
     */
    public function log($str, $type = 'LOG')
    {
        if (!$this->loggingEnabled()) {
            return;
        }

        $log = '[' . date('c') . '][' . strtoupper($type) . '] ' . $str . PHP_EOL;

        // REMOVE @-silent operator and handle errors and warnings
        @file_put_contents($this->file, $log, FILE_APPEND);
    }
}
 
