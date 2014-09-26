<?php
/**
 * Core\Controller\Cron
 *
 * PHP version 5.4
 *
 * @category General
 * @package  Core\Controller\Cron
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @version  GIT:<>
 * @link     https://github.com/desyncr
 */
namespace Core\Controller\Cron;

use Core\Controller\SMController;

/**
 * Class CronController
 *
 * @category General
 * @package  Core\Controller\Cron
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
 */
class CronController extends SMController
{
    /**
     * indexAction
     *
     * @return mixed
     */
    public function executeAction()
    {
        $request = $this->getRequest();
        if ($request instanceof ConsoleRequest) {
            $param = $this->getRequest()->getParam('cronName');
        } else {
            $param = $this->params('cronName');
        }

        $cron = $this->_getCronInstance($param);
        $sm = $this->getServiceLocator();
        $params = null;

        if ($cron->setUp($sm, $params) !== false) {
            $cron->execute($params, $sm);
        }
        $cron->tearDown();
    }

    /**
     * _getCronInstance
     *
     * @param $cronName
     *
     * @return mixed
     * @throws \Exception
     */
    private function _getCronInstance($cronName)
    {
        $configuration = $this->getServiceLocator()->get('Config');
        $crons = $configuration['wtngrm']['cron-adapter']['workers'];
        if (isset($crons[$cronName])) {
            $cron = $crons[$cronName]['handler'];
            return new $cron();
        }
        throw new \Exception('Cron not found!');
    }
}
 
