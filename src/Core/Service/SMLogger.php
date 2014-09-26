<?php
namespace Core\Service;

use Doctrine\DBAL\Logging\DebugStack;

class SMLogger extends DebugStack {

    protected $log_file; 
    
    public function __construct() {
        
        $this->log_file = sys_get_temp_dir() . '/sm_doctrine.log';
    }
    
    public function append() {
        
        $queries = $this->queries;
        foreach ($queries as $q) {
            $date = new \DateTime();
            $d = $date->format('d/m/Y H:i');
            $log = $d . ' - ' . $q['executionMS'] . ' - ' . $q['sql'] . PHP_EOL;            
            file_put_contents($this->log_file, $log, FILE_APPEND);
        }
        file_put_contents($this->log_file, PHP_EOL . ' ' .PHP_EOL, FILE_APPEND);
    }
    
    public function getQueries() {
        return $this->queries;
    }
    
}

?>