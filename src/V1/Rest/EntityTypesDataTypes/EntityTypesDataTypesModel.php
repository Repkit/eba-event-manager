<?php
namespace MicroIceEventManager\V1\Rest\EntityTypesDataTypes;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EntityTypesDataTypesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntityTypesDataTypes\EntityTypesDataTypesEntity';
	protected $TableName 	= 'entity_types_data_types';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByEntityTypeIdAndEntityDataTypeId($EntityTypeId, $DataTypeId)
    {

        if(!isset($EntityTypeId) || empty($EntityTypeId)){
            return false;
        }
        if(!isset($DataTypeId) || empty($DataTypeId)){
            return false;
        }
        return $this->select(array('EntityTypeId' => $EntityTypeId, 'EntityDataTypeId' => $DataTypeId))->current();

    }

    public function getByEntityDataTypeIdAndEntityTypeId($DataTypeId, $EntityTypeId)
    {
        return $this->getByEntityTypeIdAndEntityDataTypeId($EntityTypeId, $DataTypeId);
    }

    public function getAllEntityTypesByEntityDataTypeId($DataTypeId, array $where = array())
    {
        if(!isset($DataTypeId) || empty($DataTypeId)){
            return false;
        }

        $select = new Select();
        $select->from(array('entity_types_data_types' => $this->TableName))
            ->join(array('entity_types' => 'entity_types')
                , 'entity_types_data_types.EntityTypeId = entity_types.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
            );
        $select->where(array('entity_types_data_types.EntityDataTypeId'=>$DataTypeId));

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

    public function getAllEntityDataTypesByEntityTypeId($EntityTypeId, array $where = array())
    {
        if(!isset($EntityTypeId) || empty($EntityTypeId)){
            return false;
        }

        $select = new Select();
        $select->from(array('entity_types_data_types' => $this->TableName))
            ->join(array('entity_data_types' => 'entity_data_types')
                , 'entity_types_data_types.EntityDataTypeId = entity_data_types.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
            );
        // $select->columns(array());
        $select->where(array('entity_types_data_types.EntityTypeId'=>$EntityTypeId));

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
