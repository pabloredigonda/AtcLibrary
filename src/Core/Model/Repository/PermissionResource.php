<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\ArrayCollection;
/**
 * PermissionResource
 */

class PermissionResource extends AbstractRepository {

    public function findByModules( $modules){
        return new ArrayCollection (
            $this->findBy(array(
                'module' => $modules
            ))
        );
    }
}