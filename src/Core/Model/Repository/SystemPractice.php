<?php
namespace Core\Model\Repository;

use Core\Helper\SearchHelper;
// use Doctrine\ORM\Query\ResultSetMapping;

/**
 * SystemPractice
 */
class SystemPractice extends AbstractRepository
{

    public function search($search, $countryId = null, $lang = null)
    {
        $params = func_get_args();
    	
    	$options = array(
    	    'tableName'	=> 'system_practices',
    	    'modelName'	=> 'Core\Model\SystemPractice',
    	    'modelId'	=> 'system_practice_id'
    	);
    	 
    	return SearchHelper::getResuls( 
    			$this->getEntityManager(), $options, $params
    	);
    }
}