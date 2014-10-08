<?php
namespace Core\Service;

use Core\Model\AdminUser;

/**
 * Class AdminUserService
 *
 * @category General
 * @package  Core\Service
 * @author   Pablo Redigonda <pablo.redigonda@globant.com>
 */
class AdminUserService extends AbstractService implements Service
{
    /**
     * getClassName
     *
     * @return mixed
     */
    public function getClassName() {
		return get_class(new AdminUser());
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