<?php
namespace Core\Model\Repository;

use Core\Helper\SearchHelper;

/**
 * SystemProblem
 */
class SystemProblem extends AbstractRepository
{

    public function search($search, $countryId = null, $lang = null)
    {
    	$params = func_get_args();
    	 
    	$options = array(
    	    'tableName'	=> 'system_problem',
    	    'modelName'	=> 'Core\Model\SystemProblem',
    	    'modelId'	=> 'system_problem_id'
    	);
    	
    	return SearchHelper::getResuls(
    	    $this->getEntityManager(), $options, $params
    	);
    }
}