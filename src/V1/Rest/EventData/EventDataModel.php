<?php

namespace MicroIceEventManager\V1\Rest\EventData;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EventDataModel extends Model
{
    protected $EntityClass  = 'MicroIceEventManager\V1\Rest\EventData\EventDataEntity';
    protected $TableName    = 'event_data';

    public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function fetchAllByEventIdAndTypeId($EventId, $TypeId, $Options = array(), $Filter = null)
    {
        if(!isset($EventId) || empty($EventId)){
            return false;
        }

        $select = new Select();
        $select
            ->from(array('event_data' => $this->TableName))
            ->join('events_data'
                , new Expression('events_data.DataId = event_data.Id AND events_data.Status = 1')
                , array() // do not select anything
                , Select::JOIN_INNER
            )
            ->join('event_translations'
                , new Expression('event_translations.Id = events_data.EventId')
                , array() // do not select anything
                , Select::JOIN_INNER
            );

        if(!empty($Filter)){
            $select = \TBoxDbFilter\DbFilter::withWhere($select, $Filter, $this->TableName);
        }   
         
        $select->where->equalTo('event_translations.Id',$EventId);
        $select->where->equalTo('event_data.TypeId',$TypeId);
        $select->where(array('event_data.Status'=>array(0,1)));

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