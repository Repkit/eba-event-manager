<?php

namespace MicroIceEventManager\V1\Rest\EventProfileData;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EventProfileDataModel extends Model
{
    protected $EntityClass  = 'MicroIceEventManager\V1\Rest\EventProfileData\EventProfileDataEntity';
    protected $TableName    = 'event_profile_data';

    public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function fetchAllByProfileIdAndTypeId($ProfileId, $TypeId, $Options = array(), $Filter = null)
    {
        if( !isset($ProfileId) || empty($ProfileId) )
        {
            return false;
        }

        $select = new Select();
        $select->from($this->TableName);
        $select->join('event_profiles_data'
            , new Expression($this->TableName . '.Id = event_profiles_data.DataId AND event_profiles_data.Status = 1')
            , array() // do not select anything
            , Select::JOIN_INNER
        );
        $select->join('event_profile_translations'
                , new Expression('event_profile_translations.Id = event_profiles_data.ProfileId')
                , array() // do not select anything
                , Select::JOIN_INNER
            );

        if(!empty($Filter)){
            $select = \TBoxDbFilter\DbFilter::withWhere($select, $Filter, $this->TableName);
        }   
         
        $select->where->equalTo('event_profile_translations.Id',$ProfileId);
        $select->where->equalTo($this->TableName . '.TypeId',$TypeId);
        $select->where(array($this->TableName . '.Status'=>array(0,1)));

        // if(!empty($Options)){
        //     $select = $this->addSelectOptions($select, $Options);
        // }

        $sql = new Sql($this->Adapter);
        
//         echo $sql->getSqlStringForSqlObject($select);exit();
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