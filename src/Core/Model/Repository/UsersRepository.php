<?php
/**
 * Core\Model\Respository
 *
 * PHP version 5.4
 *
 * @category General
 * @package  Core\Model\Respository
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @version  GIT:<>
 * @link     https://github.com/desyncr
 */
namespace Core\Model\Repository;

use Doctrine\ORM\Query\Expr\Join;

/**
 * Class UsersRepository
 *
 * @category General
 * @package  Core\Model\Repository
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
 */
class UsersRepository extends AbstractRepository
{
    /**
     * getByCountryAndProfile
     *
     * @param      $countries
     * @param null $profiles
     *
     * @return mixed
     */
    public function getByCountryAndProfile($countries, $profiles = null)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->leftJoin('Core\Model\OfficeStaff', 'o', Join::WITH, 'u.id = o.user');
        $qb->innerJoin('o.profile', 'p')
            ->andWhere($qb->expr()->in('u.country', $countries));

        if ($profiles) {
            $qb->andWhere($qb->expr()->in('p.id', $profiles));
        }

        return $qb->getQuery()->getResult();
    }
}
 