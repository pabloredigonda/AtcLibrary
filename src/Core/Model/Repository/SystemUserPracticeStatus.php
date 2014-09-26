<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;

/**
 * SystemUserPracticeStatus
 */

class SystemUserPracticeStatus extends AbstractRepository {
    
    public function findVisible() {
        $status = $this->findByStatus('hidden');
        
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->neq('status', $status));
        return $this->matching($criteria);
    }

} 