<?php
namespace Core\Model\Repository;

use Core\Helper\SearchHelper;
use Doctrine\ORM\Query\ResultSetMapping;
/**
 * SystemCommonPractice
 */
class SystemCommonPractice extends AbstractRepository
{
	public function findIdByName( $name, $lang )
    {
    	return parent::_findIdByName(
			"system_common_practice", 
			"system_common_practice_id", 
    		$name, 
			$lang
		);
    }
}