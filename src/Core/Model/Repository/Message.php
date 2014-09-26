<?php
namespace Core\Model\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Core\Util\Constants;

/**
 * Message
 */

class Message extends AbstractRepository {
    
    public function findByConversation($conversationId, $offset, $role) {
    	$qb = $this->createQueryBuilder('m');
    	$qb->andWhere($qb->expr()->orX($qb->expr()->eq('m.conversation', ':conversationId'),
                                       $qb->expr()->eq('m.id', ':conversationId')));
        if($role == 'sender') {
            $qb->andWhere($qb->expr()->eq('m.senderDeleted', ':userDeleted'));
        } else {
            $qb->andWhere($qb->expr()->eq('m.recipientDeleted', ':userDeleted'));
        }
    	$qb->orderBy('m.createdDate', 'DESC');
    	
    	$qb->setParameter('conversationId', $conversationId);
    	$qb->setParameter('userDeleted', false);
    	 
    	$qb->setFirstResult($offset);
    	$qb->setMaxResults(Constants::MESSAGE_PAGE_SIZE);
    	
    	$paginator = new Paginator($qb->getQuery());
    	return $paginator;
    }
    
    public function findConversationByIdAndUser($id, $user) {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('id', $id))
                 ->andWhere($criteria->expr()->isNull('conversation'))
                 ->andWhere($criteria->expr()->orX(
                        $criteria->expr()->andX(
                            $criteria->expr()->eq('sender', $user),
                            $criteria->expr()->eq('senderDeleted', false)
                        ),
                        $criteria->expr()->andX(
                            $criteria->expr()->eq('recipient', $user),
                            $criteria->expr()->eq('recipientDeleted', false)
                        ))
                );
        return $this->matching($criteria);
    }
    
    public function findByUser($user) {
        $criteria = new Criteria();
    	$criteria->andWhere($criteria->expr()->isNull('conversation'))
             	 ->andWhere($criteria->expr()->orX(
             	     $criteria->expr()->andX(
                         $criteria->expr()->eq('sender', $user),
             	         $criteria->expr()->eq('senderDeleted', false)
             	     ),
             	     $criteria->expr()->andX(
             	         $criteria->expr()->eq('recipient', $user),
             	         $criteria->expr()->eq('recipientDeleted', false)
             	     )))
                 ->orderBy(array("lastActivity" => Criteria::DESC));
        return $this->matching($criteria);
    }
    
    public function findUnreadConversationsByUser($user) {
        $criteria = new Criteria();
    	$criteria->andWhere($criteria->expr()->isNull('conversation'))
             	 ->andWhere($criteria->expr()->orX(
             	     $criteria->expr()->andX(
                         $criteria->expr()->eq('sender', $user),
                         $criteria->expr()->eq('senderStatus', \Core\Model\Message::STATUS_UNREAD),
             	         $criteria->expr()->eq('senderDeleted', false)
    	             ),
             	     $criteria->expr()->andX(
             	         $criteria->expr()->eq('recipient', $user),
             	         $criteria->expr()->eq('recipientStatus', \Core\Model\Message::STATUS_UNREAD),
             	         $criteria->expr()->eq('recipientDeleted', false)
             	     ),
    	             $criteria->expr()->gte('createdDate', new \DateTime('-5 seconds'))
    	             ))
    	         ->orderBy(array("lastActivity" => Criteria::ASC));
        return $this->matching($criteria);
    }
    
    public function markAsRead($conversationId, $recipientId) {
        $conn = $this->getEntityManager()->getConnection();
        
        // Update messages in conversation
        $sql  = "UPDATE message SET recipient_status = '" . \Core\Model\Message::STATUS_READ . "' ";
        $sql .= " WHERE conversation_id = '" . $conversationId . "'";
        $sql .= "   AND recipient_id = '" . $recipientId . "'";
        $sql .= "   AND recipient_status = '" . \Core\Model\Message::STATUS_UNREAD . "'";
        $conn->executeUpdate($sql);

        // TODO: DQL
        // Update conversation depending on user's role | Role: Recipient
        $sql  = "UPDATE message SET recipient_status = '" . \Core\Model\Message::STATUS_READ . "' ";
        $sql .= " WHERE message_id = '" . $conversationId . "'";
        $sql .= "   AND recipient_id = '" . $recipientId . "'";
        $conn->executeUpdate($sql);

        // TODO: DQL
        // Update conversation depending on user's role | Role: Sender
        $sql  = "UPDATE message SET sender_status = '" . \Core\Model\Message::STATUS_READ . "' ";
        $sql .= " WHERE message_id = '" . $conversationId . "'";
        $sql .= "   AND sender_id = '" . $recipientId . "'";
        $conn->executeUpdate($sql);
    }
    
    public function findTotalUnreadByUser($user) {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->isNull('conversation'))
             	 ->andWhere($criteria->expr()->orX(
             	     $criteria->expr()->andX(
                         $criteria->expr()->eq('sender', $user),
             	         $criteria->expr()->eq('senderDeleted', false)
             	     ),
             	     $criteria->expr()->andX(
             	         $criteria->expr()->eq('recipient', $user),
             	         $criteria->expr()->eq('recipientDeleted', false)
             	     )))
             	 ->andWhere($criteria->expr()->orX(
                     $criteria->expr()->andX(
                         $criteria->expr()->eq('sender', $user),
                         $criteria->expr()->eq('senderStatus', \Core\Model\Message::STATUS_UNREAD)
                     ),
                     $criteria->expr()->andX(
                         $criteria->expr()->eq('recipient', $user),
                         $criteria->expr()->eq('recipientStatus', \Core\Model\Message::STATUS_UNREAD)
                     )));
        return $this->matching($criteria)->count();
    }

    public function findUnreadByUser($user) {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->isNull('conversation'))
                ->andWhere($criteria->expr()->orX(
                    $criteria->expr()->andX(
                        $criteria->expr()->eq('sender', $user),
                        $criteria->expr()->eq('senderDeleted', false),
                        $criteria->expr()->eq('senderStatus', \Core\Model\Message::STATUS_UNREAD)
                    ),
                    $criteria->expr()->andX(
                        $criteria->expr()->eq('recipient', $user),
                        $criteria->expr()->eq('recipientDeleted', false),
                        $criteria->expr()->eq('recipientStatus', \Core\Model\Message::STATUS_UNREAD)
                    )))             	 
                 ->orderBy(array('lastActivity' => Criteria::DESC));
        return $this->matching($criteria);
    }

    public function findUnreadByConversation($user, $conversation) {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('conversation', $conversation))
             	 ->andWhere($criteria->expr()->orX(
             	     $criteria->expr()->andX(
                         $criteria->expr()->eq('sender', $user),
             	         $criteria->expr()->eq('senderDeleted', false)
             	     ),
             	     $criteria->expr()->andX(
             	         $criteria->expr()->eq('recipient', $user),
             	         $criteria->expr()->eq('recipientDeleted', false)
             	     )))
                 ->andWhere($criteria->expr()->eq('recipientStatus', 'unread'))
                 ->andWhere($criteria->expr()->eq('recipient', $user))
                 ->orderBy(array('createdDate' => Criteria::ASC));
        return $this->matching($criteria);
    }

    public function findTotal($conversation) {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('conversation', $conversation));
        return 1 + $this->matching($criteria)->count();
    }
    
    public function deleteMessages($conversation, $role) {
        $conn = $this->getEntityManager()->getConnection();
        if($role == 'sender') {
            $sql = "UPDATE message SET sender_deleted = '" . true . "' WHERE conversation_id = '" . $conversation->getId() . "'";
        } else {
            $sql = "UPDATE message SET recipient_deleted = '" . true . "' WHERE conversation_id = '" . $conversation->getId() . "'";
        }
        $conn->executeUpdate($sql);
    }
    
}

?>