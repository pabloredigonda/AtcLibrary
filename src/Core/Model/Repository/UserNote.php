<?php
namespace Core\Model\Repository;

class UserNote extends AbstractRepository
{
    /**
     * findByUser
     *
     * @param \Core\Model\Users $user Users object
     *
     * @return mixed
     */
    public function findByUser($user)
    {
        $qb = 
        	$this->createQueryBuilder('o')
        	->where('o.user = :user' )
        	->setParameter('user', $user);
        
        return $qb->getQuery()->getResult();
    }
    
    public function findUserNotesByUser($user) {
        $fields = array(
            'n.date',
            'n.topic',
            'n.note',
        );
        
        $qb = $this->createQueryBuilder('n');
        $qb->select($fields)
           ->andWhere('n.user = :user')
           ->setParameter('user', $user);
        
        return $qb->getQuery()->getResult();
    }
    
    
}