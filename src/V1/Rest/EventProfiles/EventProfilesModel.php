<?php

namespace MicroIceEventManager\V1\Rest\EventProfiles;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EventProfilesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EventProfiles\EventProfilesEntity';
	protected $TableName 	= 'event_profiles';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        $this->AutoIncrementColumns = array('Id');
        parent::__construct($Adapter, $Options);
    }

    public function getById($Id)
    {
        if(!isset($Id) || empty($Id)){
            return false;
        }
        $select = new Select();
        $select->from($this->TableName);
        $select->join('event_profile_translations'
            , new Expression($this->TableName . '.Id = event_profile_translations.ProfileId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo($this->TableName . '.Status', 99);
        // $select->where->equalTo($this->TableName . '.Status', 1);
        $select->where->equalTo($this->TableName . '.Id', $Id);
        $select->where->isNull('event_profile_translations.Language');

        $sql = new Sql($this->Adapter);

        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());

        return $resultSet->current();
    }

    public function getTranslationsById($Id)
    {
        if(!isset($Id) || empty($Id)){
            return false;
        }
        $select = new Select();
        $select->from($this->TableName);
        $select->join('event_profile_translations'
            , new Expression($this->TableName . '.Id = event_profile_translations.ProfileId AND event_profile_translations.Status = 1')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo($this->TableName . '.Status', 99);
        // $select->where->equalTo($this->TableName . '.Status', 1);
        $select->where->equalTo($this->TableName . '.Id', $Id);

        $sql = new Sql($this->Adapter);

        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }

    public function getAllProfilesByLanguage($Language = null)
    {
        $select = new Select();
        $select->from($this->TableName);
        $select->join('event_profile_translations'
            , new Expression($this->TableName . '.Id = event_profile_translations.ProfileId AND event_profile_translations.Status = 1')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo($this->TableName . '.Status', 99);
        // $select->where->equalTo($this->TableName . '.Status', 1);
        if(null == $Language){
            $select->where->isNull('event_profile_translations.Language');
        }else{
            $select->where->equalTo('event_profile_translations.Language', $Language);
        }

        $sql = new Sql($this->Adapter);

        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }

    public function getByIdAndLanguage($Id, $Language)
    {
        if(!isset($Id) || empty($Id)){
            return false;
        }
        $select = new Select();
        $select->from($this->TableName);
        $select->join('event_profile_translations'
            , new Expression($this->TableName . '.Id = event_profile_translations.ProfileId AND event_profile_translations.Status = 1')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo($this->TableName . '.Status', 99);
        // $select->where->equalTo($this->TableName . '.Status', 1);
        $select->where->equalTo($this->TableName . '.Id', $Id);
        $select->where->equalTo('event_profile_translations.Language', $Language);

        $sql = new Sql($this->Adapter);

        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());

        return $resultSet->current();
    }

    public function fetchAll(array $where = array())
    {
        $select = new Select();
        $select->from($this->TableName);
        $select->join('event_profile_translations'
            , new Expression($this->TableName . '.Id = event_profile_translations.ProfileId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        $select->where->notEqualTo($this->TableName . '.Status', 99);
        // $select->where->equalTo($this->TableName . '.Status', 1);
        $select->where->isNull('event_profile_translations.Language');

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

    /**
     * Logical deletion
     *
     * @param  Where|\Closure|string|array $where
     * @return int
     */
    public function delete($Where)
    {
        return $this->update(array('Status' => 99), $Where);
    }
}
