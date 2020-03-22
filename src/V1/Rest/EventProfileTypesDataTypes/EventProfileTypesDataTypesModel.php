<?php
namespace MicroIceEventManager\V1\Rest\EventProfileTypesDataTypes;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EventProfileTypesDataTypesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EventProfileTypesDataTypes\EventProfileTypesDataTypesEntity';
	protected $TableName 	= 'event_profile_types_data_types';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByEventProfileTypeIdAndEventProfileDataTypeId($EventProfileTypeId, $DataTypeId)
    {

        if(!isset($EventProfileTypeId) || empty($EventProfileTypeId)){
            return false;
        }
        if(!isset($DataTypeId) || empty($DataTypeId)){
            return false;
        }
        return $this->select(array('EventProfileTypeId' => $EventProfileTypeId, 'EventProfileDataTypeId' => $DataTypeId))->current();

    }

    public function getByEventProfileDataTypeIdAndEventProfileTypeId($DataTypeId, $EventProfileTypeId)
    {
        return $this->getByEventProfileTypeIdAndEventProfileDataTypeId($EventProfileTypeId, $DataTypeId);
    }

    public function getAllEventProfileTypesByEventProfileDataTypeId($DataTypeId, array $where = array())
    {
        if(!isset($DataTypeId) || empty($DataTypeId)){
            return false;
        }

        $select = new Select();
        $select->from($this->TableName);
        $select->join('event_profile_types'
            , new Expression($this->TableName . '.EventProfileTypeId = event_profile_types.Id')
            , array(Select::SQL_STAR)
            , Select::JOIN_INNER
        );

        $select->where(array($this->TableName . '.EventProfileDataTypeId'=>$DataTypeId));

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

    public function getAllEventProfileDataTypesByEventProfileTypeId($EventProfileTypeId, array $where = array())
    {
        if(!isset($EventProfileTypeId) || empty($EventProfileTypeId)){
            return false;
        }

        $select = new Select();
        $select->from($this->TableName);
        $select->join('event_profile_data_types'
            , new Expression($this->TableName . '.EventProfileDataTypeId = event_profile_data_types.Id')
            , array(Select::SQL_STAR)
            , Select::JOIN_INNER
        );

        $select->where(array($this->TableName . '.EventProfileTypeId'=>$EventProfileTypeId));

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
