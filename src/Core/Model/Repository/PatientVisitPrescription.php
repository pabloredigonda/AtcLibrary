<?php
namespace Core\Model\Repository;

/**
 * PatientVisitPrescription
 */

class PatientVisitPrescription extends AbstractRepository {

    public function findByPatientVisit($patientVisitId) {
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere($qb->expr()->eq('p.patientVisit', ':patientVisitId'));
    
        $qb->setParameter('patientVisitId', $patientVisitId);
        return $qb->getQuery()->getResult();
    }

    /**
     * findAllByPatient
     *
     * @param Array $arrPatients Patients objects' array
     * @param Array $status      Status array
     *
     * @return mixed
     * @throws \Exception
     */
    public function findAllByPatient($arrPatients, $status = array())
    {
        $qb = $this->createQueryBuilder('pvp');

        $qb->andWhere($qb->expr()->in('pvp.patient', ':patients'));
        $qb->setParameter('patients', $arrPatients);

        if ($status) {
            if (in_array(null, $status)) {
                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->in('pvp.status', ':status'),
                        $qb->expr()->isNull('pvp.status')
                    )
                );
            } else {
                $qb->andWhere($qb->expr()->in('pvp.status', ':status'));
            }
            $qb->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    public function findPendingByUserArray($user) {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.patient', 'p')
           ->innerJoin('o.patientStatus', 's')
           ->andWhere($qb->expr()->eq('p.user', ':user'))
           ->andWhere($qb->expr()->eq('s.watch', ':watch'));
    
        $qb->setParameter('user', $user);
        $qb->setParameter('watch', true);
        return $qb->getQuery()->getResult();
    }
    
    public function findByIdMatchingPatients($id, $patients) {
        $qb = $this->createQueryBuilder('o');
        $qb->andWhere($qb->expr()->eq('o.id', ':id'))
           ->andWhere($qb->expr()->in('o.patient', $patients));
    
        $qb->setParameter('id', $id);
        return $qb->getQuery()->getResult();
    }
    
}