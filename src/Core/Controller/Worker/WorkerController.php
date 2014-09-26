<?php
/**
 * Core\Controller\Worker
 *
 * PHP version 5.4
 *
 * @category General
 * @package  Core\Controller\Worker
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @version  GIT:<>
 * @link     https://github.com/desyncr
 */
namespace Core\Controller\Worker;

use Core\Controller\SMController;

/**
 * Class WorkerController
 *
 * @category General
 * @package  Core\Controller\Worker
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
 */
class WorkerController extends SMController
{
    /**
     * emptyAction
     *
     * @return mixed
     */
    public function emptyAction()
    {
        $workerName = $this->getRequest()->getParam('workerName');
        $serviceLocator = $this->getServiceLocator();
        $gearmanService = $serviceLocator->get('Desyncr\Wtngrm\Gearman\Service\GearmanWorkerService');

        $gearmanService->add(
            $workerName,
            function ($job) {}
        );

        while ($gearmanService->dispatch()) {
        }
    }
}
 
