<?php

namespace MicroIceEventManager\V1\Rest\EntityData;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EntityDataModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntityData\EntityDataEntity';
	protected $TableName 	= 'entity_data';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function fetchAllByEntityIdAndTypeId($EntityId, $TypeId, $Options = array(), $Filter = null)
    {
        if(!isset($EntityId) || empty($EntityId)){
            return false;
        }

        $select = new Select();
        $select
            ->from(array('entity_data' => $this->TableName))
            ->join('entities_data'
                , new Expression('entities_data.DataId = entity_data.Id AND entities_data.Status = 1')
                , array() // do not select anything
                , Select::JOIN_INNER
            )
            ->join('entity_translations'
                , new Expression('entity_translations.Id = entities_data.EntityId')
                , array() // do not select anything
                , Select::JOIN_INNER
            );

        if(!empty($Filter)){
            $select = \TBoxDbFilter\DbFilter::withWhere($select, $Filter, $this->TableName);
        }   
         
        $select->where->equalTo('entity_translations.Id',$EntityId);
        $select->where->equalTo('entity_data.TypeId',$TypeId);
        $select->where(array('entity_data.Status'=>array(0,1)));

        // if(!empty($Options)){
        //     $select = $this->addSelectOptions($select, $Options);
        // }

        $sql = new Sql($this->Adapter);
        
        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        
        return $resultSet;

    }

    public function getByIdAndTypeId($Id, $TypeId)
    {

        if(!isset($Id) || empty($Id)){
            return false;
        }
        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }
        return $this->select(array('Id' => $Id, 'TypeId' => $TypeId))->current();

    }
}