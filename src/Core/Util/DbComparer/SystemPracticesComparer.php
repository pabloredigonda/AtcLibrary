<?php
namespace Core\Util\DbComparer;

use Core\Util\Parser\SystemPracticesParser;
use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;

class SystemPracticesComparer
{
    protected $_sm;
    protected $_parser;
    protected $_diff = array();
    protected $_dbEntities = array();
    
    const DIFF_TYPE_INSERT = 'insert';
    const DIFF_TYPE_UPDATE = 'update';
    
    public function __construct( $serviceManager, SystemPracticesParser $parser)
    {
    	$this->_parser = $parser;
    	$this->_sm = $serviceManager;
    	
    	$this->_dbEntities['commonPractices'] = new DoctrineCollection();
    }
    
    public function compare()
    {
        $this->_compareCommonPractice();
        $this->_comparePractices();
        $this->_compareRelPracticeCommonPractice();
        $this->_compareVariables();
        $this->_compareOptions();
        $this->_compareRelVariableOptions();               
    }
    
    /*
     * Check each CommonPractice, if not exists then insert it 
     */
    protected function _compareCommonPractice()
    {
    	$service = $this->_getService("SystemCommonPracticeService");
    	$commonPractices = $this->_parser->getData("commonPractices");
    	
    	$maxId = $service->getMaxId();
    	
    	foreach ($commonPractices as $parserId=>$commonPractice){
    		
    		$entity = $service->findByName($commonPractice, 'es');
    		
    		if( !$entity ){
    			
    			$maxId++;
    			
    			$this->_addDiff(
					"commonPractices", 
					self::DIFF_TYPE_INSERT, 
    				array(
    					"parserId" => $parserId,
    					"id" => $maxId,
    					"name" => $commonPractice
    				)
    			);
    		}else{
    			//@TODO check for updates
    			$this->_addDbEntity("commonPractices", $parserId, $entity);
    		}
    	}
    }
    
    /*
     * Check each Practice, if not exists then insert it
    */
    protected function _comparePractices()
    {
    	$service = $this->_getService("SystemPracticeService");
    	$practices = $this->_parser->getData("practices");
    	 
    	$maxId = $service->getMaxId();
    	 
    	foreach ($practices as $parserId=>$practice){
    
    		$entity = $service->findByCode($practice['practiceCode'], 'es');
    
    		if( !$entity ){
    			 
    			$maxId++;
    			 
    			$this->_addDiff(
    					"practices",
    					self::DIFF_TYPE_INSERT,
    					array(
    							"parserId" => $parserId,
    							"id" => $maxId,
    							"practice" => $practice
    					)
    			);
    		}else{
    			//@TODO check for updates
    			$this->_addDbEntity("practices", $parserId, $entity);
    		}
    	}
    }
    
    /*
     * Check each Practice-CommonPractice relation, if not exists then insert it
    */
    protected function _compareRelPracticeCommonPractice()
    {
    	//rel_practice_commonPractice
    	$rels = $this->_parser->getData("rel_practice_commonPractice");
    
    	//print_r($rels);
    	//exit;
    	
    	foreach ($rels as $rel){
    
    		$newCommon = $this->_getInsertDiff("commonPractices", $rel['commonPracticeId']);
    		$newPractice = $this->_getInsertDiff("practices", $rel['practiceId']);
    		$dbCommon = $newCommon ? null : $this->_getDbEntity("commonPractices", $rel['commonPracticeId']);
    		$dbPractice = $newPractice ? null : $this->_getDbEntity("practices", $rel['practiceId']);
    		
    		//Add rel
    		$addRel = true;
    		
    		switch (true){
    			case ($newCommon && $newPractice):
    				$commonPracticeId = $newCommon['id'];
    				$practiceId = $newPractice['id'];
    				break;
				case ($newCommon && $dbPractice):
    				$commonPracticeId = $newCommon['id'];
    				$practiceId = $dbPractice['entity']->getId();
    				break;
    			case ($dbCommon && $newPractice):
    				$commonPracticeId = $dbCommon['entity']->getId();
    				$practiceId = $newPractice['id'];
    				break;
    			case ($dbCommon && $dbPractice):
    				$commonPracticeId = $dbCommon['entity']->getId();
    				$practiceId = $dbPractice['entity']->getId();
    				
    				foreach ($dbCommon['entity']->getPractices() as $practice){
    					if($practiceId == $practice->getId()){
    						$addRel = false;
    						break;
    					}
    				}
    		}
    		
    		if( $addRel ){
    			$this->_addDiff(
    					"rel_practice_commonPractice",
    					self::DIFF_TYPE_INSERT,
    					array(
    							"id" => "{$commonPracticeId}-{$practiceId}",
    							"commonPracticeId" => $commonPracticeId,
    							"practiceId" => $practiceId,
    					)
    			);
    		}
    	}
    }
    
    protected function _compareVariables()
    {
    	$service = $this->_getService("SystemPracticeVariableService");
    	$variables = $this->_parser->getData("variables");
    
    	$maxId = $service->getMaxId();
    
    	foreach ($variables as $parserId=>$variable){
    
    		$newPractice = $this->_getInsertDiff("practices", $variable['practiceId']);
    		$dbPractice = $newPractice ? null : $this->_getDbEntity("practices", $variable['practiceId']);
    		$dbPracticeId = $newPractice ? $newPractice['id']: $dbPractice['entity']->getId();;
    		$variable['practiceId'] = $dbPracticeId;
    		$entity = $service->findByPracticeAndName($dbPracticeId, $variable['variableName']);
    
    		if( !$entity ){
    
    			$maxId++;
    
    			$this->_addDiff(
    					"variables",
    					self::DIFF_TYPE_INSERT,
    					array(
    							"parserId" => $parserId,
    							"id" => $maxId,
    							"variable" => $variable
    					)
    			);
    		}else{
    			//@TODO check for updates
    			$this->_addDbEntity("variables", $parserId, $entity);
    		}
    	}
    }
    
    protected function _compareOptions()
    {
    	$service = $this->_getService("SystemPracticeVariableOptionsService");
    	$options = $this->_parser->getData("options");
    	 
    	$maxId = $service->getMaxId();
    	 
    	foreach ($options as $parserId=>$option){
    
    		$entity = $service->findByName($option);
    
    		if( !$entity ){
    			 
    			$maxId++;
    			 
    			$this->_addDiff(
    					"options",
    					self::DIFF_TYPE_INSERT,
    					array(
    							"parserId" => $parserId,
    							"id" => $maxId,
    							"name" => $option
    					)
    			);
    		}else{
    			//@TODO check for updates
    			$this->_addDbEntity("options", $parserId, $entity);
    		}
    	}
    }
    
    protected function _compareRelVariableOptions()
    {
    	//rel_variable_options
    	$rels = $this->_parser->getData("rel_variable_options");
    
    	foreach ($rels as $rel){
    
    		$newVariable = $this->_getInsertDiff("variables", $rel['variableId']);
    		$newOption = $this->_getInsertDiff("options", $rel['optionId']);
    		$dbVariable = $newVariable ? null : $this->_getDbEntity("variables", $rel['variableId']);
    		$dbOption = $newOption ? null : $this->_getDbEntity("options", $rel['optionId']);
    
    		//Add rel
    		$addRel = true;
    
    		switch (true){
    			case ($newVariable && $newOption):
    				$variableId = $newVariable['id'];
    				$optionId = $newOption['id'];
    				break;
    			case ($newVariable && $dbOption):
    				$variableId = $newVariable['id'];
    				$optionId = $dbOption['entity']->getId();
    				break;
    			case ($dbVariable && $newOption):
    				$variableId = $dbVariable['entity']->getId();
    				$optionId = $newOption['id'];
    				break;
    			case ($dbVariable && $dbOption):
    				$variableId = $dbVariable['entity']->getId();
    				$optionId = $dbOption['entity']->getId();
    
    				foreach ($dbVariable['entity']->getOptions() as $option){
    					if($optionId == $option->getId()){
    						$addRel = false;
    						break;
    					}
    				}
    		}
    
    		if( $addRel ){
    			$this->_addDiff(
    					"rel_variable_options",
    					self::DIFF_TYPE_INSERT,
    					array(
    							"id" => "{$variableId}-{$optionId}",
    							"variableId" => $variableId,
    							"optionId" => $optionId,
    			)
    			);
    		}
    	}
    }
    
    protected function _getService( $service )
    {
    	return $this->_sm->get('Core\Service\\' . $service );
    }
    
    protected function _addDiff( $entityName, $type, $data)
    {
    	$this->_diff[$entityName][$type][$data['id']] = $data; 
    }
    
    protected function _getInsertDiff( $entityName, $parserId)
    {
    	foreach ($this->_diff[$entityName][self::DIFF_TYPE_INSERT] as $insertRow){
    		if( $parserId ==  $insertRow['parserId']){
    			return $insertRow;
    		}
    	};
    }
    
    protected function _addDbEntity( $entityName, $parserId, $entity)
    {
    	$this->_dbEntities[$entityName][] = array(
    		'parserId' => $parserId,
    		'entity' => $entity
    	);
    }
    
    protected function _getDbEntity( $entityName, $parserId)
    {
    	if(!isset($this->_dbEntities[$entityName])){
    		echo $entityName;
    		exit;
    	}
    	
    	foreach ($this->_dbEntities[$entityName] as $dbEntity){
    		if( $parserId ==  $dbEntity['parserId']){
    			return $dbEntity;
    		}
    	};
    }
    
    public function getDiff()
    {
    	return $this->_diff;	
    }
}

