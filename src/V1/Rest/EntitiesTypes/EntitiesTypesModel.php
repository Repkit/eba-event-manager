<?php
namespace MicroIceEventManager\V1\Rest\EntitiesTypes;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EntitiesTypesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntitiesTypes\EntitiesTypesEntity';
	protected $TableName 	= 'entities_types';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByEntitiyIdAndTypeId($EntityId, $TypeId)
    {

        if(!isset($EntityId) || empty($EntityId)){
            return false;
        }
        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }
        return $this->select(array('EntityId' => $EntityId, 'TypeId' => $TypeId))->current();

    }

    public function getByTypeIdAndEntityId($TypeId, $EntityId)
    {
        return $this->getByEntityIdAndTypeId($EntityId, $TypeId);
    }

    public function getAllEntitiesByTypeId($TypeId, array $where = array())
    {
        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }

        $select = new Select();
        $select->from(array('entities_types' => $this->TableName))
            ->join(array('entities' => 'entities')
                , 'entities_types.EntityId = entities.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
            )
            ->join(array('entity_translations' => 'entity_translations')
                , 'entity_translations.EntityId = entities.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
            );
        $select->where(array('entities_types.TypeId'=>$TypeId));
        $select->where(array('entity_translations.Language'=>null));

        if (!empty($where['where'])) {
            $select->where($where['where']);
            unset($where['where']);
        }

        if (!empty($where)) {
            // $select->where($Where);
            $select = \TBoxDbFilter\DbFilter::withWhere($select, $where);
        }

        $sql = new Sql($this->Adapter);
        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }

    public function getAllTypesByEntityIdAndLanguage($EntityId, $Language = null, array $where = array())
    {
        if(!isset($EntityId) || empty($EntityId)){
            return false;
        }

        if(null == $Language){
            $langcond = ' IS NULL';
        }else{
            $langcond = " = '$Language' ";
        }

        $select = new Select();
        $select->from(array('entities_types' => $this->TableName));

        $select->join(array('entity_types' => 'entity_types')
                , 'entities_types.TypeId = entity_types.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
        );

        $select->join(array('entity_type_translations' => 'entity_type_translations')
            , new Expression('entity_type_translations.TypeId = entity_types.Id AND entity_type_translations.Language ' . $langcond)
            // , array('Type' => new Expression('GROUP_CONCAT(entity_type_translations.Name)'))
            , array('Name')
            , \Zend\Db\Sql\Select::JOIN_LEFT
        );

        // $select->columns(array());
        $select->where(array('entities_types.EntityId'=>$EntityId));

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

    public function getAllTypesByEntityId($EntityId, array $where = array())
    {
        if(!isset($EntityId) || empty($EntityId)){
            return false;
        }

        $select = new Select();
        $select->from(array('entities_types' => $this->TableName));

        $select->join(array('entity_types' => 'entity_types')
                , 'entities_types.TypeId = entity_types.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
        );

        $select->join(array('entity_type_translations' => 'entity_type_translations')
            , new Expression('entity_type_translations.TypeId = entity_types.Id AND entity_type_translations.Language IS NULL')
            // , array('Type' => new Expression('GROUP_CONCAT(entity_type_translations.Name)'))
            , array('Name')
            , \Zend\Db\Sql\Select::JOIN_INNER
        );
        
        // $select->columns(array());
        $select->where(array('entities_types.EntityId'=>$EntityId));

        if (!empty($where['where'])) {
            $select->where($where['where']);
            unset($where['where']);
        }

        if (!empty($where)) {
            // $select->where($Where);
            $select = \TBoxDbFilter\DbFilter::withWhere($select, $where);
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
