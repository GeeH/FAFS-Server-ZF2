<?php
return array(
    'router' => array(
        'routes' => array(
            'fafs-browser' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/fafs-browser',
                    'defaults' => array(
                        'controller' => 'FAFSServer\Controller\BrowserController',
                        'action'     => 'index',
                    ),
                ),
            ),
            'fafs-browser-data' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/fafs-browser/data',
                    'defaults' => array(
                        'controller' => 'FAFSServer\Controller\BrowserController',
                        'action'     => 'data',
                    ),
                ),
            ),
        ),
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'start-server' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'fafsserver start',
                        'defaults' => array(
                            'controller' => 'FAFSServer\Controller\CliController',
                            'action' => 'start'
                        )
                    )
                )
            )
        )
    ),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
        'template_map' => array(
            'fafs-server/browser/index' => __DIR__ . '/../view/browser/index/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);