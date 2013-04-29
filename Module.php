<?php
namespace FAFSServer;

use FAFSServer\Controller\CliController;
use FAFSServer\Service\FAFSServer;
use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;

class Module
{

    public function onBootstrap(MvcEvent $e)
    {

    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'FAFSServer\Service\FAFSServer' => function (ServiceManager $serviceManager) {
                    $config = $serviceManager->get('config');
                    if ($serviceManager->has('Zend\Log\Logger')) {
                        $logger = $serviceManager->get('Zend\Log\Logger');
                    } else {
                        $logger = null;
                    }
                    if (!array_key_exists('fafs', $config) || empty($config['fafs'])) {
                        throw new \InvalidArgumentException("Cannot find config for fafs");
                    }
                    $adapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
                    $fafsServer = new FAFSServer($config['fafs'], $adapter, $logger);
                    return $fafsServer;
                }
            ),
        );
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'FAFSServer\Controller\CliController' => function(ControllerManager $controllerManager) {
                    $fafsService = $controllerManager->getServiceLocator()->get('FAFSServer\Service\FAFSServer');
                    $controller = new CliController($fafsService);
                    return $controller;
                }
            ),
        );
    }
}
