<?php
return array(
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
);