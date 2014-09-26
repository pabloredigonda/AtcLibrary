<?php
namespace Core\Model\Repository;

// use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Permission
 */

class Permission extends AbstractRepository {

    public function findByModulesAndProfiles( $modules, $profiles ){
        return new ArrayCollection (
            $this->findBy(array(
                'module' => $modules,
                'role' => $profiles
            ))
        );
    }
    
//     public function findAllowedByProfile($office, $profile) {
//         $sql = "
//             SELECT pr.resource_id as resource, prp.privilege, role
//             FROM permission p
//             JOIN permission_resource_privilege prp ON p.privilege_id = prp.privilege_id
//             JOIN permission_resource pr ON pr.resource_id = prp.resource_id
//             JOIN permission_module pm ON pr.module_id = pm.module_id
//             JOIN pin_module pim ON pim.module_id = pm.module_id AND pin_id = ?
//             WHERE role IN (?)
//             AND p.allowed = ?
//             ";
        
//         $rsm = new ResultSetMapping();
//         $rsm->addScalarResult('resource', 'controller');
//         $rsm->addScalarResult('privilege', 'action');
//         $rsm->addScalarResult('role', 'roles');
        
//         $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
//         $query->setParameter(1, $office->getOfficePin()->getPin()->getId());
//         $query->setParameter(2, $profile);
//         $query->setParameter(3, true);
        
//         return $query->getResult();
//     }
    
}