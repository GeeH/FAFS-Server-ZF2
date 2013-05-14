<?php
/**
 * Gary Hockin
 * 26/04/2013
 */

namespace FAFSServer\Controller;

use FAFSServer\Service\FAFSServer;
use Zend\Filter\StaticFilter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class BrowserController extends AbstractActionController
{
    /**
     * @var FAFSServer
     */
    protected $fafsServer;

    public function __construct(FAFSServer $fafsServer)
    {
        $this->fafsServer = $fafsServer;
    }


    public function indexAction()
    {
        $keys = $this->fafsServer->getAllKeys();
        return new ViewModel(
            array(
                'keys' => $keys
            )
        );
    }

    public function dataAction()
    {
        $dateFrom = new \DateTime(
            $this->params()->fromPost('fromDate')
        );
        $dateTo = new \DateTime(
            $this->params()->fromPost('toDate')
        );
        $interval = StaticFilter::execute($this->params()->fromPost('interval'), 'Alpha');
        $keys = $this->params()->fromPost('keys');
        if(empty($keys)) {
            throw new \InvalidArgumentException('No valid keys');
        }
        $sanitsedKeys = array();
        foreach($keys as $key) {
            $sanitsedKeys[] = StaticFilter::execute($key, 'StripTags');
        }
        $data = $this->fafsServer->getData($dateFrom, $dateTo, $interval, $sanitsedKeys);
        return new JsonModel(
            $data
        );
    }

}