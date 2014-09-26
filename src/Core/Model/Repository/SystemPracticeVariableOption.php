<?php
namespace Core\Model\Repository;

/**
 * SystemPracticeVariableOption
 */
class SystemPracticeVariableOption extends AbstractRepository
{
	public function findIdByName( $name )
    {
    	$sql = "
    	SELECT system_practice_variable_option_id
    	FROM system_practice_variable_option
    	WHERE unaccent(lower(name)) = unaccent(lower(:name))
    	LIMIT 1";
    	
    	$connection = $this->getEntityManager()->getConnection();
    	$stmt = $connection->prepare($sql);
    	$stmt->bindValue("name", $name);
    	$stmt->execute();
    	
    	return $stmt->fetchColumn();
    }
}