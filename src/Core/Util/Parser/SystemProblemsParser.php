<?php
namespace Core\Util\Parser;

class SystemProblemsParser
    extends SystemDataParserAbstract
{
    protected $_problems = array();
    
    
    public function parse( $problemsStr )
    {
        $this->_setInitialIds( 1 );
    
        $this->_parse( $problemsStr );
    }
    
    public function getDump()
    {
        return $this->_dumpProblems();
    }
    
    public function getData()
    {
        return $this->_problems;
    }
    
    public function getDumpForUpdate()
    {
        //Remove fake entry
        $this->_removeFirst( $this->_problems );
    
        $sql = $this->_dumpInfo( "system_problem" );
        $start = 'UPDATE system_problem SET name=\'%1$s\' WHERE code=\'%2$s\' AND lang = \'%3$s\';';
    
        foreach ($this->_problems as $id=>$problem){
            $sql.= sprintf(
                $start,
                addslashes($this->_formatString($problem['problemName'])),
                $problem['problemCode'],
                $problem['lang']
            ) . self::SQL_ROW_SEPARATOR;
        }
        
        $sql.= "--  Update indexes" . self::SQL_ROW_SEPARATOR;
        $sql.= "UPDATE system_problem SET textsearchable_index = to_tsvector('spanish', unaccent(name) ) where lang = 'es';" . self::SQL_ROW_SEPARATOR;
        $sql.= "UPDATE system_problem SET textsearchable_index = to_tsvector('english', unaccent(name) ) where lang = 'en';" . self::SQL_ROW_SEPARATOR;
    
        return $sql;
    }
    
    protected function _setInitialIds( $problemInitialId )
    {
    	$this->_problems[$problemInitialId - 1] = null;
    }
    
    protected function _parse( $str )
    {
        $lines = explode( self::ROW_SEPARATOR, $str);
        array_shift($lines);
    
        foreach ($lines as $line){
            $data = array_slice(explode(self::COL_SEPARATOR, $line), 0, 4);
    
            if(empty($data[0])){
                continue;
            }

//             print_r($data);
//             exit;
            
            list( $problemCode, $codeType, $lang, $problemName ) = $data;
    
            //FORMAT NAMES
            $problemName = trim($this->_formatString($problemName));
    
            $this->_addProblem( $problemName, $problemCode, $codeType, $lang);
        }
    }
    
    protected function _addProblem( $problemName, $problemCode, $codeType, $lang)
    {
        $exists = false;
        
        foreach ($this->_problems as $id=>$problem){
            if( $problem['problemCode'] == $problemCode ){
                $exists = true;
                break;
            }
        }
    
        if( !$exists ){
            $this->_problems[] = array(
                'problemName' => $problemName,
                'problemCode' => $problemCode,
                'codeType' => $codeType,
                'lang' => $lang
            );
        }
        
        $keys = array_keys($this->_problems);
        $problemId = end($keys);
        
        return $problemId;
    }
    
    protected function _dumpProblems()
    {
        //Remove fake entry
        $this->_removeFirst( $this->_problems );
    
        $sql = $this->_dumpInfo( "system_problems" );
        $start = 'INSERT INTO system_problems   (system_problem_id, country_id, lang, code, code_type, name)';   
        $start.= ' VALUES (%1$s, \'AR\', \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\');';
    
        foreach ($this->_problems as $id=>$problem){
            $sql.= sprintf(
                $start,
                $id,
                $problem['lang'],
                $problem['problemCode'],
                $problem['codeType'],
                addslashes($this->_formatString($problem['problemName']))
            ) . self::SQL_ROW_SEPARATOR;
        }
    
        return $sql;
    }
  
}

