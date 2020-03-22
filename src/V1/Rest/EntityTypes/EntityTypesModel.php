<?php
namespace MicroIceEventManager\V1\Rest\EntityTypes;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EntityTypesModel extends Model
{
    protected $EntityClass  = 'MicroIceEventManager\V1\Rest\EntityTypes\EntityTypesEntity';
    protected $TableName    = 'entity_types';

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
        $select->join('entity_type_translations'
            , new Expression('entity_types.Id = entity_type_translations.TypeId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo('entity_types.Status', 99);
        // $select->where->equalTo('entity_types.Status', 1);
        $select->where->equalTo('entity_types.Id', $Id);
        $select->where->isNull('entity_type_translations.Language');

        $sql = new Sql($this->Adapter);
        
        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        
        return $resultSet->current();

    }

    public function getByLanguageAndId($Language, $Id)
    {
        return $this->getByIdAndLanguage($Id, $Language);
    }

    public function getByIdAndLanguage($Id, $Language)
    {

        if(!isset($Id) || empty($Id)){
            return false;
        }
        $select = new Select();
        $select->from($this->TableName);
        $select->join('entity_type_translations'
            , new Expression('entity_types.Id = entity_type_translations.TypeId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo('entity_types.Status', 99);
        // $select->where->equalTo('entity_types.Status', 1);
        $select->where->equalTo('entity_types.Id', $Id);
        $select->where->equalTo('entity_type_translations.Language', $Language);

        $sql = new Sql($this->Adapter);
        
        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        
        return $resultSet->current();

    }

    public function getAllTypesByLanguage($Language = null)
    {
        $select = new Select();
        $select->from($this->TableName);
        $select->join('entity_type_translations'
            , new Expression('entity_types.Id = entity_type_translations.TypeId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo('entity_types.Status', 99);
        // $select->where->equalTo('entity_types.Status', 1);
        if(null == $Language){
            $select->where->isNull('entity_type_translations.Language');
        }else{
            $select->where->equalTo('entity_type_translations.Language', $Language);
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

        $select = new Select();
        $select->from($this->TableName);
        $select->join('entity_type_translations'
            , new Expression('entity_types.Id = entity_type_translations.TypeId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo('entity_types.Status', 99);
        // $select->where->equalTo('entity_types.Status', 1);
        $select->where->isNull('entity_type_translations.Language');

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

    public function getTranslationsById($Id)
    {
        if(!isset($Id) || empty($Id)){
            return false;
        }
        $select = new Select();
        $select->from($this->TableName);
        $select->join('entity_type_translations'
            , new Expression('entity_types.Id = entity_type_translations.TypeId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo('event_types.Status', 99);
        // $select->where->equalTo('event_types.Status', 1);
        $select->where->equalTo('entity_types.Id', $Id);

        $sql = new Sql($this->Adapter);
        
        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }

    public function getAllExtended($Storage = array(), $Where = null, $PostWhere = null)
    {
        $select = $this->preselect($Storage, $Where);

        $select->where->notEqualTo('entity_types.Status', 99);
        
        if(!empty($Where)){
            // $select->where($Where);
            $select = \TBoxDbFilter\DbFilter::withWhere($select, $Where, $this->TableName);
        }

        if(!empty($PostWhere)){
            $select->where($PostWhere);
        }

        $sql = new Sql($this->Adapter);

        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }

    private function preselect(array $Storage = array(), &$Where = null)
    {
        $select = new Select();
        $select
            ->from($this->TableName);
        $select->where->notEqualTo('entity_types.Status', 99);
        
        if( !empty($Storage) )
        {
            $joins = $Storage['joins'];
            // add extra joins if they are defined
            foreach($joins as $type => $joincollection)
            {
                foreach($joincollection as $join)
                {
                    if(!empty($join['table']) && !empty($join['on']) && !empty($join['columns']))
                    {
                        $select->join($join['table']
                            , new Expression($join['on'])
                            , $join['columns']
                            , $type
                        );
                    }
                    
                }
            }

            if( isset($Where) && !empty($Where) 
                && isset($Storage['where']) && !empty($Storage['where']) 
                && isset($Storage['where']['external_columns']) && !empty($Storage['where']['external_columns']) )
            {
                $externalColumns = $Storage['where']['external_columns'];
                $externalWhere = array();
                foreach ($Where as $key => $condition) 
                {
                    $propertyName = $condition['name'];
                    if( isset($externalColumns[$propertyName]) )
                    {
                        $externalWhere[$key] = $condition;
                        $externalWhere[$key]['name'] = $externalColumns[$propertyName];
                        unset($Where[$key]);
                    }
                }
                if( !empty($externalWhere) ){
                    $select = \TBoxDbFilter\DbFilter::withWhere($select, $externalWhere);
                }
            }
        }

        return $select;
    }

    public function validateTypeAndEntityTranslationId($TypeId, $TranslationId)
    {
        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }

        if(!isset($TranslationId) || empty($TranslationId)){
            return false;
        }

        $select = new Select();
        $select->from(array('entity_types' => $this->TableName))
            ->join(array('entities_types' => 'entities_types')
                , new Expression("entities_types.TypeId = entity_types.Id 
                    AND `entities_types`.`Status` = 1")
                , array()
                , \Zend\Db\Sql\Select::JOIN_LEFT
            )
            ->join(array('entity_translations' => 'entity_translations')
                , new Expression("entities_types.EntityId = entity_translations.EntityId 
                    AND `entity_translations`.`Status` = 1
                    AND `entity_translations`.`Id` = $TranslationId")
                , array('Assigned' => 'EntityId')
                , \Zend\Db\Sql\Select::JOIN_INNER
            )
            ->columns(array('Id','Status'));

        $select->where->equalTo('entity_types.Id', $TypeId);
        $select->where->equalTo('entity_types.Status', 1);
        $select->order('entity_translations.EntityId DESC');
        $select->quantifier('DISTINCT');

        $sql = new Sql($this->Adapter);
        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());

        $obj = $resultSet->current();
        
        $obj->EntityId = null;
        if($obj){
            $subselect = new Select();
            $subselect->from(array('entity_translations' => 'entity_translations'));
            $subselect->where->equalTo('entity_translations.Id', $TranslationId);
            $subselect->where->equalTo('entity_translations.Status', 1);
            $subselect->columns(array('EntityId'));
            // echo $sql->getSqlStringForSqlObject($subselect);exit();
            $statement = $sql->prepareStatementForSqlObject($subselect);
            $resultSet = new ResultSet();
            $resultSet->initialize($statement->execute());
            $obja = $resultSet->current();
            $obj->EntityId = $obja->EntityId;
        }

        return $obj;
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