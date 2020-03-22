<?php

namespace MicroIceEventManager\V1\Rest\EventProfilesTypes;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class EventProfilesTypesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EventProfilesTypes\EventProfilesTypesEntity';
	protected $TableName 	= 'event_profiles_types';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByProfileIdAndTypeId($ProfileId, $TypeId, array $where = array())
    {

        if( !isset($ProfileId) || empty($ProfileId) )
        {
            return false;
        }
        if( !isset($TypeId) || empty($TypeId) )
        {
            return false;
        }

        $select = new Select();
        $select->from($this->TableName);
        $select->join('event_profile_types'
            , new Expression($this->TableName . '.TypeId = event_profile_types.Id')
            , array()
            , Select::JOIN_INNER
        );
        $select->join('event_profile_type_translations'
            , new Expression('event_profile_types.Id = event_profile_type_translations.TypeId AND event_profile_type_translations.Language IS NULL')
            , array('Name')
            , Select::JOIN_INNER
        );

        $select->where->equalTo($this->TableName . '.ProfileId', $ProfileId);
        $select->where->equalTo($this->TableName . '.TypeId', $TypeId);
        $select->where->in($this->TableName . '.Status', array(0,1));

        if(!empty($where['where'])){
            $select->where($where['where']);
        }

        $sql = new Sql($this->Adapter);

        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());

        return $resultSet->current();

    }

    public function getAllProfilesByTypeId($TypeId, array $where = array())
    {
        if( !isset($TypeId) || empty($TypeId) )
        {
            return false;
        }

        $select = new Select();
        $select->from($this->TableName);
        $select->join('event_profile_types'
            , new Expression($this->TableName . '.TypeId = event_profile_types.Id')
            , array()
            , Select::JOIN_INNER
        );
        $select->join('event_profile_type_translations'
            , new Expression('event_profile_types.Id = event_profile_type_translations.TypeId AND event_profile_type_translations.Language IS NULL')
            , array('Name')
            , Select::JOIN_INNER
        );

        $select->where->equalTo($this->TableName . '.TypeId', $TypeId);
        $select->where->in($this->TableName . '.Status', array(0,1));

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

    public function getAllTypesByProfileId($ProfileId, array $where = array())
    {
        if( !isset($ProfileId) || empty($ProfileId) )
        {
            return false;
        }

        $select = new Select();
        $select->from($this->TableName);
        $select->join('event_profile_types'
            , new Expression($this->TableName . '.TypeId = event_profile_types.Id')
            , array()
            , Select::JOIN_INNER
        );
        $select->join('event_profile_type_translations'
            , new Expression('event_profile_types.Id = event_profile_type_translations.TypeId AND event_profile_type_translations.Language IS NULL')
            , array('Name')
            , Select::JOIN_INNER
        );

        $select->where->equalTo($this->TableName . '.ProfileId', $ProfileId);
        $select->where->in($this->TableName . '.Status', array(0,1));

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

    public function getAllTypesByProfileIdAndLanguage($ProfileId, $Language = null, array $where = array())
    {
        if( !isset($ProfileId) || empty($ProfileId) )
        {
            return false;
        }

        $langCond = $Language == null ? ' IS NULL' : " = '$Language'";

        $select = new Select();
        $select->from($this->TableName);
        $select->join('event_profile_types'
            , new Expression($this->TableName . '.TypeId = event_profile_types.Id')
            , array()
            , Select::JOIN_INNER
        );
        $select->join('event_profile_type_translations'
            , new Expression('event_profile_types.Id = event_profile_type_translations.TypeId AND event_profile_type_translations.Language ' . $langCond)
            , array('Name')
            , Select::JOIN_INNER
        );

        $select->where->equalTo($this->TableName . '.ProfileId', $ProfileId);
        $select->where->in($this->TableName . '.Status', array(0,1));

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
}
