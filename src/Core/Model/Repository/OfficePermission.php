<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\ArrayCollection;
/**
 * OfficePermission
 */

class OfficePermission extends AbstractRepository {

    public function findByModulesAndProfiles( $office, $modules, $profiles ){
        return new ArrayCollection (
            $this->findBy(array(
                'office' => $office,
                'module' => $modules,
                'role' => $profiles
            ))
        );
    }
}