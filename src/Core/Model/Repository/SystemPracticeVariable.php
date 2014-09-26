<?php
namespace Core\Model\Repository;

/**
 * SystemPracticeVariable
 */
class SystemPracticeVariable extends AbstractRepository
{
	public function findByPracticeAndName( $practiceId, $name )
	{
		$sql = "
        	SELECT system_practice_variable_id
        	FROM system_practices_variables
        	WHERE system_practice_id = :practiceId AND unaccent(lower(name)) = unaccent(lower(:name))
			LIMIT 1";
    
    	$connection = $this->getEntityManager()->getConnection();
    	$stmt = $connection->prepare($sql);
    	$stmt->bindValue("practiceId", $practiceId);
    	$stmt->bindValue("name", $name);
    	$stmt->execute();
    	 
    	return $stmt->fetchColumn();
	}
}