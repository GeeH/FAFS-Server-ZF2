<?php
/**
 * Gary Hockin
 * 23/04/2013
 */

namespace FAFSServer\Service;

use FAFSServer\Mapper\MapperInterface;
use FAFSServer\Mapper\TableGatewayMapper;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Log\Logger;

class FAFSServer implements EventManagerAwareInterface
{
    /**
     * @var array
     */
    protected $config;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var MapperInterface
     */
    protected $mapper;
    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @param array $config
     * @param Adapter $adapter
     * @param Logger $logger
     * @param MapperInterface $mapper
     */
    public function __construct(array $config, Adapter $adapter, Logger $logger = null, MapperInterface $mapper = null)
    {
        $this->config = $config;
        $this->logger = $logger;
        if (!is_null($mapper)) {
            $this->mapper = $mapper;
        }
        $tableGateway = new TableGateway('data', $adapter);
        $this->mapper = new TableGatewayMapper($tableGateway);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function start()
    {
        $this->log('info', 'Attempting to start server...');
        // get config values (or use some sane defaults)
        $ip = isset($this->config['ip']) ? $this->config['ip'] : '127.0.0.1';
        die($ip);
        $port = isset($this->config['port']) ? $this->config['port'] : '6610';
        $length = isset($this->config['maxLength']) ? $this->config['maxLength'] : 1024;

        // Create socket server
        $socket = stream_socket_server("udp://$ip:$port", $errorNo, $errorMessage, STREAM_SERVER_BIND);
        if ($errorNo || $errorMessage || get_resource_type($socket) !== 'stream') {
            throw new \Exception("Error creating server: $errorMessage", $errorNo);
        }

        // Loop through data as it's received
        while ($data = stream_socket_recvfrom($socket, $length, null, $receivedFrom)) {
            stream_socket_sendto($socket, json_encode(array('status' => 1)), null, $receivedFrom);
            $this->log('info', 'Data received: ' . $data);
            $this->handleData($data);
        }

        // return is pointless
        return 'Server Started';
    }

    /**
     * @param $level
     * @param $data
     */
    protected function log($level, $data)
    {
        if (method_exists($this->logger, $level)) {
            $this->logger->{$level}($data);
        }
    }

    /**
     * @return null|\Zend\Db\ResultSet\ResultSetInterface
     */
    public function getAllKeys()
    {
        return $this->mapper->getAllKeys();
    }

    public function getData(\DateTime $dateFrom, \DateTime $dateTo, $interval, $keys)
    {
        switch ($interval) {
            case 'minutes':
                $intervalString = new \DateInterval('PT1M');
                $dateFormat = 'd-m-Y H:i';
                break;
            case 'hours':
                $intervalString = new \DateInterval('PT1H');
                $dateFormat = 'd-m-Y H';
                break;
            default:
                $intervalString = new \DateInterval('P1D');
                $dateFormat = 'd-m-Y';
                break;
        }
        $data = $this->mapper->getData($dateFrom, $dateTo, $interval, $keys);
        $titles = array('Interval');
        $titles = array_merge($titles, array_keys($data));
        $returnData[] = $titles;
        while ($dateFrom <= $dateTo) {
            $formattedDate = $dateFrom->format($dateFormat);
            $rowData = array();
            $rowData[] = $formattedDate;
            foreach ($keys as $key) {
                $value = 0;
                if(isset($data[$key][0]) && $data[$key][0]['Expression1'] === $formattedDate) {
                    $value = (int)$data[$key][0]['Expression2'];
                    array_shift($data[$key]);
                }
                $rowData[] = $value;
            }

            $returnData[] = $rowData;
            $dateFrom = $dateFrom->add($intervalString);
        }
        return $returnData;
    }

    /**
     * @return void|EventManagerInterface
     */
    public function getEventManager()
    {

        return $this->eventManager;
    }

    /**
     * @param EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->addIdentifiers(
            array(
                get_called_class()
            )
        );
        $this->eventManager = $eventManager;
    }

    /**
     * @param $data
     * @return bool
     */
    protected function handleData($data)
    {
        $decodedData = json_decode($data, false);
        if (!is_array($decodedData)) {
            $this->log('err', 'Data is not in valid JSON format: ' . $data);
            return false;
        }
        foreach ($decodedData as $decodedRow) {
            if (!is_array($decodedRow) || !$this->validateRow($decodedRow)) {
                $this->log('err', 'Malformed data row: ' . $decodedRow);
                return false;
            }

            $this->mapper->addCount($decodedRow);
            $this->log('info', 'Row added: ' . json_encode($decodedRow));
        }
        return true;
    }

    /**
     * @param array $decodedRow
     * @return bool
     */
    protected function validateRow(array $decodedRow)
    {
        // Validate timestamp
        if (!isset($decodedRow[0]) || !$this->isValidNumber($decodedRow[0])) {
            $this->log('err', 'Timestamp field is not valid: ' . $decodedRow[0]);
            return false;
        }
        if (!isset($decodedRow[1]) || !$this->isValidKey($decodedRow[1])) {
            $this->log('err', 'Key field is not valid: ' . $decodedRow[1]);
            return false;
        }
        if (!isset($decodedRow[2]) || !$this->isValidNumber($decodedRow[2])) {
            $this->log('err', 'Count field is not valid: ' . $decodedRow[2]);
            return false;
        }
        return true;
    }

    /**
     * @param $string
     * @return bool
     */
    public function isValidNumber($string)
    {
        return (is_numeric($string));
    }

    /**
     * @param $string
     * @return bool
     */
    public function isValidKey($string)
    {
        return preg_match('^[a-zA-Z0-9.]{1,}$^', $string) === 1;
    }

}