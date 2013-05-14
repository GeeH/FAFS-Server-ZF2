<?php
/**
 * Gary Hockin
 * 23/04/2013
 */

namespace FAFSServer\Mapper;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

class TableGatewayMapper implements MapperInterface
{
    /**
     * @var TableGateway
     */
    protected $tableGateway;

    /**
     * @param TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * @param array $data
     * @return int
     */
    public function addCount(array $data)
    {
        return $this->tableGateway->insert(
            array(
                'when' => new Expression('FROM_UNIXTIME(' . $data[0] . ')'),
                'key' => $data[1],
                'value' => $data[2],
            )
        );
    }

    /**
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param $interval
     * @param array $keys
     * @return mixed
     */
    public function getData(\DateTime $dateFrom, \DateTime $dateTo, $interval, array $keys)
    {
        switch ($interval) {
            case 'minutes':
                $dateFormat = '%d-%m-%Y %H:%i';
                break;
            case 'hours':
                $dateFormat = '%d-%m-%Y %H';
                break;
            default:
                $dateFormat = '%d-%m-%Y';
                break;
        }
        $return = array();
        foreach ($keys as $key) {
            $select = new Select('data');
            $select->columns(
                array(
                    new Expression('DATE_FORMAT(`when`,"' . $dateFormat . '")'),
                    new Expression('SUM(`value`)'),
                )
            );
            $select->
                where->greaterThanOrEqualTo('when', $dateFrom->format('Y-m-d'))
                ->and->lessThan('when', $dateTo->format('Y-m-d'))
                ->and->equalTo('key', $key);
            $select->group(new Expression('DATE_FORMAT(`when`,"' . $dateFormat . '")'));
            $return[$key] =  $this->tableGateway->selectWith($select)->toArray();
        }
        return $return;
    }

    /**
     * @return null|\Zend\Db\ResultSet\ResultSetInterface
     */
    public function getAllKeys()
    {
        $select = new Select('data');
        $select->columns(array('key'));
        $select->quantifier('DISTINCT');
        return $this->tableGateway->selectWith($select);
    }
}