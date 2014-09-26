<?php
namespace Core\Model\Repository;

use Doctrine\ORM\EntityRepository;

class AbstractRepository extends EntityRepository
{

    public function findByQuery($dql, $params = null) {
        
    	$query = $this->getEntityManager()->createQuery($dql);
    	if ($params) {
    		$query->setParameters($params);
    	}
    	return $query->getResult();
    }

    public function getProperties() {
        return isset($this->properties) ? $this->properties : array();
    }


    public function findByQueryBuilder($conditions, $columns = null, $orderBy = null, $paginate = null) {

        $cols = "";
        if ($columns) {
            array_walk($columns, function(&$value, $key) {$value = "e.$value";});
            $cols = join(",", $columns);
        }

        $qb = $this->createQueryBuilder('e');

        $qb->select($cols);

        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                unset($conditions[$column]);
                $orx = $qb->expr()->orx();

                foreach ($value as $index => $condition) {
                    $orx->add('e.' . $column . ' = :' . $column.$index);
                    $conditions[$column.$index] = $condition;
                }

                $qb->andWhere($orx);

            } else {
                $qb->andWhere('e.' . $column . ' = :' . $column);
            }
        }

        if ($paginate) {
            $qb->setFirstResult($paginate['offset'])
                ->setMaxResults($paginate['limit']);
        }

        $qb->setParameters($conditions);

        if($orderBy) {
            if(is_array($orderBy)) {
                foreach($orderBy as $o) {
                    if(is_array($o) && isset($o[0]) && isset($o[1])) {
                        $qb->addOrderBy('e.'.$o[0], strtolower($o[1])=='desc' ? 'DESC' : 'ASC');
                    } else {
                        $qb->addOrderBy($o);
                    }
                }
            } else {
                $qb->addOrderBy($orderBy);
            }
        }
        return $qb->getQuery()->getArrayResult();
    }
    
    protected function _findIdByName( $table, $id, $name, $lang )
    {
    	$sql = "
        	SELECT {$id}
        	FROM {$table}
        	WHERE lang = :lang AND unaccent(lower(name)) = unaccent(lower(:name))
			LIMIT 1";
    
    	$connection = $this->getEntityManager()->getConnection();
    	$stmt = $connection->prepare($sql);
    	$stmt->bindValue("lang", $lang);
    	$stmt->bindValue("name", $name);
    	$stmt->execute();
    	 
    	return $stmt->fetchColumn();
    }
    
} 