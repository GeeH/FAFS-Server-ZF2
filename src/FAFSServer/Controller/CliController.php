<?php
/**
 * Gary Hockin
 * 26/04/2013
 */

namespace FAFSServer\Controller;


use FAFSServer\Service\FAFSServer;
use Zend\Mvc\Controller\AbstractActionController;

class CliController extends AbstractActionController
{
    /**
     * @var FAFSServer
     */
    protected $fafsServer;

    public function __construct(FAFSServer $fafsServer)
    {
        $this->fafsServer = $fafsServer;
    }


    /**
     * Starts the server!
     */
    public function startAction()
    {
        $this->fafsServer->start();
    }
}