<?php
namespace Core\Util\Parser;


/**
 * Reglas de parseo
 *
 * Specialties
 * Se parsearan las siguientes columnas
 * 1: especialidad médica
 * 2: código PMO
 * 3: práctica médica
 * 4: variable
 * 5: unidad de medida
 * 6: rangos de referencia
 * 7: indicador de si el resultado contiene un combo de opciones
 *
 * CÓDIGO PMO: los códigos creados manualmente debera llevar un prefijo ej: SM_280501
 * ya que no es posible interprerar el color del campo del excel
 *
 * PRÁCTICA: para los campos cuyos nombres de prácticas se encuentres vacíon se sara el nombre de la variable como nombre de la practica
 *
 * RESULTADOS DE VARIABLES CON COMBO: las variables cuyos resultados deban ser un combo de opciones deberan tener un entero
 * "1" el la séptima columna
 * SEPARADOR DE OPCIONES: el caracter delimitaror de opciones para variables con combos sera el slash (/)
 *
 *
 */

class SystemPracticesParser
{
    
    const ROW_SEPARATOR = "\n";
    const SQL_ROW_SEPARATOR = "\n";
    const COL_SEPARATOR = ";";
    const OPTIONS_SEPARATOR = "/";
    
    protected $_commonPractices = array();
    protected $_practices = array();
    protected $_variables = array();
    protected $_options = array();
    protected $_rel_variable_options = array();
    protected $_rel_practice_commonPractice = array();
    protected $_sm;
    protected $_compare;
    
    public function __construct( )
    {
    }
    
    public function parse( $specialtiesStr, $practicesStr, $practicesInitialId, $variablesInitialId, $optionsInitialId  )
    {
        $this->_setInitialIds( $practicesInitialId, $variablesInitialId, $optionsInitialId );
    
        $this->_parseSpecialtiesFile( $specialtiesStr );
    
        $this->_parsePracticesFile( $practicesStr );
    }
    
    protected function _getService( $service )
    {
        return $this->_sm->get("\Core\Service\{$service}");
    }
    
    public function compare()
    {
        $this->_compare = null;
        
        $commonPracticesService = $this->_getService("SystemCommonPracticeService");
        
        foreach ($this->_commonPractices as $id=>$commonPractice){
        	
        }
    }
    
    public function getDump()
    {
        return  $this->_dumpCommonPractices()
        . $this->_dumpPractices()
        . $this->_dumpPracticesRel()
        . $this->_dumpVariables()
        . $this->_dumpVariablesOptions()
        . $this->_dumpOptionsRel();
    }
    
    public function getData( $partial = null )
    {
    	$this->_removeEmpty();
    	
    	if($partial){
    		return $this->{"_{$partial}"};
    	}
    	
    	return array(
        	'commonPractices' => $this->_commonPractices,
        	'practices' => $this->_practices,
        	'variables' => $this->_variables,
        	'options' => $this->_options,
        	'rel_variable_options' => $this->_rel_variable_options,
        	'rel_practice_commonPractice' => $this->_rel_practice_commonPractice
        );
    }
    
    public function setData( $data )
    {
        $this->_commonPractices = $data['commonPractices'];
        $this->_practices = $data['practices'];
        $this->_variables = $data['variables'];
        $this->_options = $data['options'];
        $this->_rel_variable_options = $data['rel_variable_options'];
        $this->_rel_practice_commonPractice = $data['rel_practice_commonPractice'];
    }
    
    public function setServiceManager( $sm )
    {
        $this->_sm = $sm;
    }
    
    protected function _setInitialIds( $practicesInitialId, $variablesInitialId, $optionsInitialId )
    {
    	$this->_commonPractices[0] = null;
    	$this->_practices[$practicesInitialId - 1] = null;
    	$this->_variables[$variablesInitialId - 1] = null;
    	$this->_options[$optionsInitialId - 1] = null;
    	$this->_rel_variable_options[0] = null;
    	$this->_rel_practice_commonPractice[0] = null;
    }
    
    protected function _removeFirst( &$array )
    {
        $key = current(array_keys($array));
        
        if(empty($array[$key])){
        	unset( $array[$key]);
        }
    }
    
    protected function _removeEmpty()
    {
    	$this->_removeFirst($this->_commonPractices);
    	$this->_removeFirst($this->_practices);
    	$this->_removeFirst($this->_variables);
    	$this->_removeFirst($this->_options);
    	$this->_removeFirst($this->_rel_practice_commonPractice);
    	$this->_removeFirst($this->_rel_variable_options);
    }
    
    protected function _parseSpecialtiesFile( $str )
    {
        $lines = explode( self::ROW_SEPARATOR, $str);
        array_shift($lines);
    
        foreach ($lines as $line){
            $data = array_slice(explode(self::COL_SEPARATOR, $line), 0, 8);
    
            if(empty($data[0])){
                continue;
            }
    
            list(
                $commonPractice, $practiceCode, $practiceName, $variableName,
                $measureType, $refence, $hasOptions, $customCode
            ) = $data;
    
            //FORMAT NAMES
            $commonPractice = $this->_formatString($commonPractice);
            $practiceName = $this->_formatString($practiceName);
            $variableName = $this->_formatString($variableName);
    
            $commonPracticeId = $this->_addCommonPractice( $commonPractice );
    
            if(empty($practiceName)){
                $practiceName = $variableName;
            }
    
            if($hasOptions){
                $optionsText = $measureType;
                $measureType = null;
                $refence = null;
            }
    
            $practiceId = $this->_addPractice( $practiceName, $practiceCode, $commonPracticeId, null, $customCode );
    
            $variableId = $this->_addVariable( $variableName, $measureType, $refence, $practiceId, $hasOptions );
    
            if($hasOptions){
                $variableId = $this->_addOptions( $variableId, $optionsText );
            }
        }
    }
    
    protected function _parsePracticesFile( $str )
    {
        $lines = explode( self::ROW_SEPARATOR, $str);
        array_shift($lines);
    
        $currentPracticeGroup = null;
    
        foreach ($lines as $line){
            $data = array_slice(explode(self::COL_SEPARATOR, $line), 0, 3);
    
            if(empty($data[0])){
                continue;
            }
    
            list( $practiceCode, $practiceName, $customCode ) = $data;
    
            //FORMAT NAMES
            $practiceName = trim($this->_formatString($practiceName));
            $practiceCode = trim($this->_formatString($practiceCode));
    
            if( !is_numeric($practiceCode) && empty($practiceName)){
                $currentPracticeGroup = $practiceCode;
                continue;
            }
    
            $practiceId = $this->_addPractice( $practiceName, $practiceCode, null, $currentPracticeGroup, $customCode);
    
            if( ! $this->_practiceHasVariables( $practiceId )){
            
                $variableName = $practiceName;
    
                $this->_addVariable( $variableName, null, null, $practiceId, false );
            }
        }
    }
    
    protected function _addCommonPractice( $commonPractice )
    {
        $id = array_search($commonPractice, $this->_commonPractices);
    
        if( is_numeric($id) ) {
            return $id;
        }
    
        $this->_commonPractices[] = $commonPractice;

        $keys = array_keys($this->_commonPractices);
        return end( $keys );
    }
    
    protected function _addPractice( $practiceName, $practiceCode, $commonPracticeId, $groupName, $customCode)
    {
        $exists = false;
        
        foreach ($this->_practices as $id=>$practice){
            if( $practice['practiceCode'] == $practiceCode ){
                $exists = true;
                break;
            }
        }
    
        if( !$exists ){
            $this->_practices[] = array(
                'practiceName' => $practiceName,
                'practiceCode' => $practiceCode,
                'groupName' => $groupName,
                'customCode' => (bool) $customCode
            );
        }
        
        $keys = array_keys($this->_practices);
        $practiceId = end($keys);
    
        if( $commonPracticeId ){
            $this->_addPracticeRel( $practiceId, $commonPracticeId );
        }
        
        return $practiceId;
    }
    
    protected function _practiceHasVariables( $practiceId )
    {
        foreach ($this->_variables as $id=>$variable){
            if( $variable['practiceId'] == $practiceId ){
                return true;
            }
        }
        
        return false;
    }
    
    protected function _addPracticeRel( $practiceId, $commonPracticeId )
    {
        foreach ($this->_rel_practice_commonPractice as $id=>$rel){
            if( $rel['practiceId'] == $practiceId && $rel['commonPracticeId'] == $commonPracticeId ){
                return;
            }
        }
    
        $this->_rel_practice_commonPractice[] = array(
            'practiceId' => $practiceId,
            'commonPracticeId' => $commonPracticeId
        );
    }
    
    protected function _addVariable( $variableName, $measureType, $refence, $practiceId )
    {
        foreach ($this->_variables as $id=>$variable){
            if( $variable['variableName'] == $variableName && $variable['practiceId'] == $practiceId ){
                return $id;
            }
        }
    
        $this->_variables[] = array(
            'variableName' => $variableName,
            'measureType' => $measureType,
            'refence' => $refence,
            'practiceId' => $practiceId
        );
    
        $keys = array_keys($this->_variables);
        return end($keys);
    }
    
    protected function _addOptions( $variableId, $measureType )
    {
        $options = explode(self::OPTIONS_SEPARATOR, $measureType);
    
        foreach ($options as $optionName){
             
            $optionId = $this->_addOption($optionName);
    
            $this->_addVariableRel($variableId, $optionId);
        }
    }
    
    protected function _addOption( $optionName )
    {
        $id = array_search($optionName, $this->_options);
    
        if( is_numeric($id) ) {
            return $id;
        }
    
        $this->_options[] = $optionName;
    
        $keys = array_keys($this->_options);
        return end($keys);
    }
    
    protected function _addVariableRel( $variableId, $optionId )
    {
        foreach ($this->_rel_variable_options as $id=>$rel){
            if( $rel['variableId'] == $variableId && $rel['optionId'] == $optionId ){
                return;
            }
        }
    
        $this->_rel_variable_options[] = array(
            'variableId' => $variableId,
            'optionId' => $optionId
        );
    }
    
    protected function _dumpCommonPractices(  )
    {
        //Remove fake entry
        $this->_removeFirst( $this->_commonPractices );
    
        $sql = $this->_dumpInfo( "system_common_practice" );
        $start = 'INSERT INTO system_common_practice VALUES (%1$s, NULL,\'es\', \'%2$s\');';
    
        foreach ($this->_commonPractices as $id=>$commonPractice){
            $sql.= sprintf(
                $start,
                $id,
                $this->_formatString($commonPractice)
            ) . self::SQL_ROW_SEPARATOR;
        }
    
        return $sql;
    }
    
    protected function _dumpPractices()
    {
        //Remove fake entry
        $this->_removeFirst( $this->_practices );
    
        $sql = $this->_dumpInfo( "system_practices" );
        $start = 'INSERT INTO system_practices(system_practice_id, country_id, lang, code, code_type, name, "group", custom_code)';
        $start.= ' VALUES(%1$s, \'AR\', \'es\', %2$s, \'PMO\', \'%3$s\', \'%4$s\', %5$s);';
    
        foreach ($this->_practices as $id=>$practice){
            $sql.= sprintf(
                $start,
                $id,
                $practice['practiceCode'],
                $this->_formatString($practice['practiceName']),
                $this->_formatString($practice['groupName']),
                $practice['customCode'] ? "TRUE" : "FALSE"
            ) . self::SQL_ROW_SEPARATOR;
        }
    
        return $sql;
    }
    
    protected function _dumpPracticesRel()
    {
        //Remove fake entry
        $this->_removeFirst( $this->_rel_practice_commonPractice );
    
        $sql = $this->_dumpInfo( "system_practice_parents" );
        $start = 'INSERT INTO system_practice_parents(system_common_practice_id, system_practice_id) VALUES ( %1$d, %2$d);';
    
        foreach ($this->_rel_practice_commonPractice as $id=>$rel){
            $sql.= sprintf(
                $start,
                $rel['commonPracticeId'],
                $rel['practiceId']
            ) . self::SQL_ROW_SEPARATOR;
        }
    
        return $sql;
    }
    
    protected function _dumpVariables()
    {
        //Remove fake entry
        $this->_removeFirst( $this->_variables );
    
        $sql = $this->_dumpInfo( "system_practices_variables" );
        $start = 'INSERT INTO system_practices_variables(system_practice_variable_id, system_practice_id, name, measure_type, min, max, reference, sort) ';
        $start.= 'VALUES (%1$d, %2$d, \'%3$s\', \'%4$s\', null, null, \'%5$s\', %6$d);';
    
        foreach ($this->_variables as $id=>$variable){
            $sql.= sprintf(
                $start,
                $id,
                $variable['practiceId'],
                $this->_formatString($variable['variableName']),
                addslashes($variable['refence']),
                addslashes($variable['measureType']),
                0 //sort
            ) . self::SQL_ROW_SEPARATOR;
        }
    
        return $sql;
    }
    
    protected function _dumpVariablesOptions()
    {
        //Remove fake entry
        $this->_removeFirst( $this->_options );
    
        $sql = $this->_dumpInfo( "system_practice_variable_option" );
        $start = 'INSERT INTO system_practice_variable_option(system_practice_variable_option_id, name) VALUES(%1$d, \'%2$s\');';
    
        foreach ($this->_options as $id=>$optionName){
            $sql.= sprintf(
                $start,
                $id,
                $this->_formatString($optionName)
            ) . self::SQL_ROW_SEPARATOR;
        }
    
        return $sql;
    }
    
    protected function _dumpOptionsRel()
    {
        //Remove fake entry
        $this->_removeFirst( $this->_rel_variable_options );
    
        $sql = $this->_dumpInfo( "system_practice_variable_options_rel" );
        $start = 'INSERT INTO system_practice_variable_options_rel(system_practice_variable_id, system_practice_variable_option_id)VALUES(%1$d, %2$d);';
    
        foreach ($this->_rel_variable_options as $id=>$rel){
            $sql.= sprintf(
                $start,
                $rel['variableId'],
                $rel['optionId']
            ) . self::SQL_ROW_SEPARATOR;
        }
    
        return $sql;
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
        return ucfirst(mb_strtolower($string, "UTF-8"));
        //return ucfirst(strtolower($string));
    }
}

