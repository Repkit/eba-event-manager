<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileTypesDataTypes;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EntityProfileTypesDataTypesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntityProfileTypesDataTypes\EntityProfileTypesDataTypesEntity';
	protected $TableName 	= 'entity_profile_types_data_types';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByEntityProfileTypeIdAndEntityProfileDataTypeId($EntityProfileTypeId, $DataTypeId)
    {

        if(!isset($EntityProfileTypeId) || empty($EntityProfileTypeId)){
            return false;
        }
        if(!isset($DataTypeId) || empty($DataTypeId)){
            return false;
        }
        return $this->select(array('EntityProfileTypeId' => $EntityProfileTypeId, 'EntityProfileDataTypeId' => $DataTypeId))->current();

    }

    public function getByEntityProfileDataTypeIdAndEntityProfileTypeId($DataTypeId, $EntityProfileTypeId)
    {
        return $this->getByEntityProfileTypeIdAndEntityProfileDataTypeId($EntityProfileTypeId, $DataTypeId);
    }

    public function getAllEntityProfileTypesByEntityProfileDataTypeId($DataTypeId, array $where = array())
    {
        if(!isset($DataTypeId) || empty($DataTypeId)){
            return false;
        }

        $select = new Select();
        $select->from($this->TableName);
        $select->join('entity_profile_types'
            , new Expression($this->TableName . '.EntityProfileTypeId = entity_profile_types.Id')
            , array(Select::SQL_STAR)
            , Select::JOIN_INNER
        );

        $select->where(array($this->TableName . '.EntityProfileDataTypeId'=>$DataTypeId));

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

    public function getAllEntityProfileDataTypesByEntityProfileTypeId($EntityProfileTypeId, array $where = array())
    {
        if(!isset($EntityProfileTypeId) || empty($EntityProfileTypeId)){
            return false;
        }

        $select = new Select();
        $select->from($this->TableName);
        $select->join('entity_profile_data_types'
            , new Expression($this->TableName . '.EntityProfileDataTypeId = entity_profile_data_types.Id')
            , array(Select::SQL_STAR)
            , Select::JOIN_INNER
        );

        $select->where(array($this->TableName . '.EntityProfileTypeId'=>$EntityProfileTypeId));

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
