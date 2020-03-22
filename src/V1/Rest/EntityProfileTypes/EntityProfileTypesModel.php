<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileTypes;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EntityProfileTypesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntityProfileTypes\EntityProfileTypesEntity';
	protected $TableName 	= 'entity_profile_types';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getById($Id)
    {
        if(!isset($Id) || empty($Id)){
            return false;
        }
        $select = new Select();
        $select->from($this->TableName);
        $select->join('entity_profile_type_translations'
            , new Expression($this->TableName . '.Id = entity_profile_type_translations.TypeId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo($this->TableName . '.Status', 99);
        // $select->where->equalTo($this->TableName . '.Status', 1);
        $select->where->equalTo($this->TableName . '.Id', $Id);
        $select->where->isNull('entity_profile_type_translations.Language');

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
        $select->join('entity_profile_type_translations'
            , new Expression($this->TableName . '.Id = entity_profile_type_translations.TypeId AND entity_profile_type_translations.Status = 1')
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

    public function getAllProfileTypesByLanguage($Language = null)
    {
        $select = new Select();
        $select->from($this->TableName);
        $select->join('entity_profile_type_translations'
            , new Expression($this->TableName . '.Id = entity_profile_type_translations.TypeId AND entity_profile_type_translations.Status = 1')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo($this->TableName . '.Status', 99);
        // $select->where->equalTo($this->TableName . '.Status', 1);
        if(null == $Language){
            $select->where->isNull('entity_profile_type_translations.Language');
        }else{
            $select->where->equalTo('entity_profile_type_translations.Language', $Language);
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
        $select->join('entity_profile_type_translations'
            , new Expression($this->TableName . '.Id = entity_profile_type_translations.TypeId AND entity_profile_type_translations.Status = 1')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo($this->TableName . '.Status', 99);
        // $select->where->equalTo($this->TableName . '.Status', 1);
        $select->where->equalTo($this->TableName . '.Id', $Id);
        $select->where->equalTo('entity_profile_type_translations.Language', $Language);

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
        $select->join('entity_profile_type_translations'
            , new Expression($this->TableName . '.Id = entity_profile_type_translations.TypeId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        $select->where->notEqualTo($this->TableName . '.Status', 99);
        // $select->where->equalTo($this->TableName . '.Status', 1);
        $select->where->isNull('entity_profile_type_translations.Language');

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