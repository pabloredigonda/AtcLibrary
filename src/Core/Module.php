<?php
namespace Core;

use Core\Service\AdminUserService;
use Core\Service\UserService;
use Core\Service\OfficeService;

class Module
{

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Core\Service\AdminUserService' => function ($sm)
                {
                    return new AdminUserService($sm);
                },
                'Core\Service\UserService' => function ($sm)
                {
                    return new UserService($sm);
                },
                'Core\Service\OfficeService' => function ($sm)
                {
                    return new OfficeService($sm);
                }
            )
        )
        ;
    }
}