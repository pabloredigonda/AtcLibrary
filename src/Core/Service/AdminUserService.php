<?php
/**
 * Core\Service\AdminUserService
 *
 * PHP version 5.4
 *
 * @category General
 * @package  Core\Service\AdminUserService
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @version  GIT:<>
 * @link     https://github.com/desyncr
 */
namespace Core\Service;

use Core\Model\AdminUsers;

/**
 * Class AdminUserService
 *
 * @category General
 * @package  Core\Service
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
 */
class AdminUserService extends AbstractService implements Service
{
    /**
     * getClassName
     *
     * @return mixed
     */
    public function getClassName() {
		return get_class(new AdminUsers());
	}

    /**
     * findByEmail
     *
     * @param String $email Email address to search by
     *
     * @return \Core\Model\AdminUsers|null
     */
    public function findByEmail($email) {
		return $this->findBy('email', $email);
	}

    /**
     * existsEmail
     *
     * @param String $email Email address to search by
     *
     * @return Boolean
     */
    public function existsEmail($email) {
        if ($user = $this->findBy('email', $email)) {
			return true;
		}
		return false;
	}
}