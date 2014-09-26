<?php
namespace Core\Model\Repository;

/**
 * Specialty
 */
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Common\Collections\ArrayCollection;

class Specialty extends AbstractRepository
{
	public function findByOffice( $office )
	{
		$sql = "SELECT s.* 
				FROM specialty s 
				JOIN office_staff_specialty oss ON s.specialty_id = oss.specialty_id
				JOIN office_staff os ON os.staff_id = oss.staff_id
				JOIN office o ON o.office_id = os.office_id AND o.office_id = ?";
		
		$rsm = new ResultSetMapping();
		$rsm->addEntityResult('Core\Model\Specialty', 's');
		$rsm->addFieldResult('s', 'specialty_id', 'id');
		$rsm->addFieldResult('s', 'name', 'name');
		
		$query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
		
		$query->setParameter(1, $office->getId() );
		
		return new ArrayCollection(
				$query->getResult()
		);
	}
}

?>