<?php
namespace Core\Model\Repository;

/**
 * UserReminder
 */

class UserReminder extends AbstractRepository {

    public function findPendingByUserArray($user) {
        $today = new \Datetime();

        $qb = $this->createQueryBuilder('o');
        $qb->andWhere($qb->expr()->eq('o.user', ':user'))
           ->andWhere($qb->expr()->gte('o.endDate', ':today'));
        
        $qb->setParameter('user', $user);
        $qb->setParameter('today', $today);

        return $qb->getQuery()->getResult();
    }
    
    public function getRemindersByDate($date = null) {
        $sql = "
            SELECT *
            FROM user_reminder
            WHERE now() > start_date
            AND now() < end_date + interval '+15 minutes'
            AND
            (
                (
                    frequency = 0
                ) OR (
                    CAST(EXTRACT(EPOCH from (now()::time - start_date::time)) AS INTEGER) / 60 < 15
                    AND CAST(EXTRACT(EPOCH from (now()::time - start_date::time)) AS INTEGER) / 60 >= 0
                    AND
                    (
                        (
                            frequency = 1
                        ) OR (
                            frequency IN (7, 14)
                            AND ((CAST(EXTRACT(EPOCH FROM (now() - start_date)) AS INTEGER) / 3600 / 24) % frequency) = 0
                        ) OR (
                            frequency = 30
                            AND EXTRACT(DAY FROM start_date) = EXTRACT(DAY FROM now())
                        ) OR (
                            frequency = 365
                            AND EXTRACT(DAY FROM start_date) = EXTRACT(DAY FROM now())
                            AND EXTRACT(MONTH FROM start_date) = EXTRACT(MONTH FROM now())
                        )
                    )
                )
            )
        ";
        return $this->getEntityManager()->getConnection()->fetchAll($sql);
    }
    
}