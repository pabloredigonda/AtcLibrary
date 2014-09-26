<?php
namespace Core\Service;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceManager;
use Doctrine\Common\Collections\Criteria;
use Core\Exception\NullIdentifierException;
use Doctrine\ORM\Query;
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\DBAL\Logging\DebugStack;

class AbstractService {
    
	protected $em;
	protected $sm;
	
	public function __construct(ServiceManager $sm) {
	    
		$this->sm = $sm;
		$this->em = $sm->get('Doctrine\ORM\EntityManager');

// 		if (getenv('APP_ENV') != "production") {
// 		    $logger = $sm->get('Core\SMLogger');
//     		$this->em->getConnection()->getConfiguration()->setSQLLogger($logger);
// 		}
	}
	
	public function getEntityManager() {
		return $this->em;
	}

	public function setServiceManager( ServiceManager $sm ) {
		$this->sm = $sm;
	}
	
	public function setEntityManager( EntityManager $em ) {
	    $this->em = $em;
	}
	
	public function getServiceManager() {
	    return $this->sm;
	}
    
    public function getRepository($repository = null) {
        return $repository ? $this->em->getRepository($repository) : $this->em->getRepository($this->getClassName());
    }
    
    public function getService( $entityName = null ){
    	
    	$entityName = $entityName ? $entityName : $this->getClassName();
    	
    	$prefix =  'Core\Service\\';
    	$postfix =  'Service';

    	$entityName = str_replace(array($prefix, $postfix), array('', ''), $entityName);
    	
    	$serviceName = $prefix . $entityName . $postfix;
    	
    	return $this->getServiceManager()->get($serviceName);
    }

	public function save($entity, $options = null) {
		if ($entity->getId()) {
			$this->em->merge($entity);
		} else {
			$this->em->persist($entity);
		}
		$this->em->flush();
		return $entity->getId();
	}

    public function delete($entity) {
        if (!is_object($entity)) {
            $entity = $this->em->getReference($this->getClassName(), $entity);
        }
        $this->em->remove($entity);
        $this->em->flush();
    }

	public function findById($id) {
        if ($id === null) {
            throw new NullIdentifierException();
        }
		return $this->getRepository()->find($id);
	}

	public function findAllBy($attribute = null, $value = null, $orderBy = array()) {
	    $criteria = array();
	    if ($attribute) {
	        $criteria = array($attribute => $value);
	    }
		return $this->getRepository()->findBy($criteria, $orderBy);
	}
	
	public function findBy($attribute, $value = null) {
        if (is_array($attribute)) {
            $conditions = $attribute;
        } else {
            $conditions = array($attribute => $value);
        }
        return $this->getRepository()->findOneBy($conditions);
	}
	
	public function findAll() {
		return $this->getRepository()->findAll();
	}

	public function find($conditions, $columns = null, $orderBy = null) {
	    return $this->getRepository()->findByQueryBuilder($conditions, $columns, $orderBy);
	}

	public function queryByCriteria(Criteria $criteria) {
		return $this->getRepository()->matching($criteria);
	}
	
	public function getMaxId() {
		$entities = $this->getRepository()->findBy(array(), array("id"=>"DESC"), 1);
		$entity = current($entities);
		
		return $entity->getId();
	}

}

?>