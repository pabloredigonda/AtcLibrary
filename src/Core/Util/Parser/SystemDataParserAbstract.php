<?php
namespace Core\Util\Parser;

abstract class SystemDataParserAbstract
{
    
    const ROW_SEPARATOR = "\n";
    const SQL_ROW_SEPARATOR = "\n";
    const COL_SEPARATOR = ";";
    const OPTIONS_SEPARATOR = "/";
    
    public function __construct( )
    {
    }
    
    protected function _getService( $service )
    {
        return $this->_sm->get("\Core\Service\{$service}");
    }
    
    abstract public function getDump();
    
    abstract public function getData();
    
    public function setServiceManager( $sm )
    {
        $this->_sm = $sm;
    }
    
    protected function _removeFirst( &$array )
    {
        $key = current(array_keys($array));
        unset( $array[$key]);
    }
    
    protected function _dumpInfo( $tableName )
    {
        $date = new \Datetime();
    
        $text = self::SQL_ROW_SEPARATOR;
        $text.= '-- ' . $tableName . ' ' . self::SQL_ROW_SEPARATOR;
        $text.= '-- This file was generated by ' . basename(__FILE__) . ' on ' . $date->format(\DateTime::RFC850) . self::SQL_ROW_SEPARATOR ;
    
        return $text;
    }
    
    protected function _formatString( $string )
    {
        return ucfirst(strtolower($string));
    }
}
