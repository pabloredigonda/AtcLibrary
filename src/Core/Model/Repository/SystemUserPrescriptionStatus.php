<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;

/**
 * SystemUserPrescriptionStatus
 */

class SystemUserPrescriptionStatus extends AbstractRepository {
    
    public function findVisible() {
        $hidden = array('hidden', 'finished', 'left');
        $criteria = new Criteria();
        foreach($hidden as $status) {
            $criteria->andWhere($criteria->expr()->neq('status', $this->findByStatus($status)));
        }
        return $this->matching($criteria);
    }

} 