<?php
namespace MicroIceEventManager\V1\Rest\EventTypesDataTypes;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EventTypesDataTypesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EventTypesDataTypes\EventTypesDataTypesEntity';
	protected $TableName 	= 'event_types_data_types';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByEventTypeIdAndEventDataTypeId($EventTypeId, $DataTypeId)
    {

        if(!isset($EventTypeId) || empty($EventTypeId)){
            return false;
        }
        if(!isset($DataTypeId) || empty($DataTypeId)){
            return false;
        }
        return $this->select(array('EventTypeId' => $EventTypeId, 'EventDataTypeId' => $DataTypeId))->current();

    }

    public function getByEventDataTypeIdAndEventTypeId($DataTypeId, $EventTypeId)
    {
        return $this->getByEventTypeIdAndEventDataTypeId($EventTypeId, $DataTypeId);
    }

    public function getAllEventTypesByEventDataTypeId($DataTypeId, array $where = array())
    {
        if(!isset($DataTypeId) || empty($DataTypeId)){
            return false;
        }

        $select = new Select();
        $select->from(array('event_types_data_types' => $this->TableName))
            ->join(array('event_types' => 'event_types')
                , 'event_types_data_types.EventTypeId = event_types.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
            );
        $select->where(array('event_types_data_types.EventDataTypeId'=>$DataTypeId));

        if(!empty($where['where'])){
            $select->where($where['where']);
        }

        $sql = new Sql($this->Adapter);
        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }

    public function getAllEventDataTypesByEventTypeId($EventTypeId, array $where = array())
    {
        if(!isset($EventTypeId) || empty($EventTypeId)){
            return false;
        }

        $select = new Select();
        $select->from(array('event_types_data_types' => $this->TableName))
            ->join(array('event_data_types' => 'event_data_types')
                , 'event_types_data_types.EventDataTypeId = event_data_types.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
            );
        // $select->columns(array());
        $select->where(array('event_types_data_types.EventTypeId'=>$EventTypeId));

        if(!empty($where['where'])){
            $select->where($where['where']);
        }

        $sql = new Sql($this->Adapter);
        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }

    public function fetchAll(array $where = array())
    {
        if(!empty($where['where'])){
            $resultSet = $this->select($where['where']);
        }else{
            $resultSet = $this->select();
        }

        return $resultSet;
    }
}
