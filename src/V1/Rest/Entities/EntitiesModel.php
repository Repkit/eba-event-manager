<?php
namespace MicroIceEventManager\V1\Rest\Entities;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EntitiesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\Entities\EntitiesEntity';
	protected $TableName 	= 'entities';

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
        $select->join('entity_translations'
            , new Expression('entities.Id = entity_translations.EntityId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo('entities.Status', 99);
        // $select->where->equalTo('entities.Status', 1);
        $select->where->equalTo('entities.Id', $Id);
        $select->where->isNull('entity_translations.Language');

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
        $select->join('entity_translations'
            , new Expression('entities.Id = entity_translations.EntityId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo('entities.Status', 99);
        // $select->where->equalTo('entities.Status', 1);
        $select->where->equalTo('entities.Id', $Id);
        $select->where->equalTo('entity_translations.Language', $Language);

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
        $select->join('entity_translations'
            , new Expression('entities.Id = entity_translations.EntityId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo('entities.Status', 99);
        // $select->where->equalTo('entities.Status', 1);
        if(null == $Language){
            $select->where->isNull('entity_translations.Language');
        }else{
            $select->where->equalTo('entity_translations.Language', $Language);
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
        $select->join('entity_translations'
            , new Expression('entities.Id = entity_translations.EntityId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo('entities.Status', 99);
        // $select->where->equalTo('entities.Status', 1);
        $select->where->isNull('entity_translations.Language');

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
        $select->join('entity_translations'
            , new Expression('entities.Id = entity_translations.EntityId')
            , array('TranslationId'=>'Id', 'Language', 'Name')
            , Select::JOIN_INNER
        );
        // $select->where->notEqualTo('entities.Status', 99);
        // $select->where->equalTo('entities.Status', 1);
        $select->where->equalTo('entities.Id', $Id);

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

        $select->join(array('entities_types' => 'entities_types')
            , new Expression('entities.Id = entities_types.EntityId AND entities_types.Status = 1')
            , array()
            , \Zend\Db\Sql\Select::JOIN_LEFT
        );
        $select->join(array('entity_types' => 'entity_types')
            , new Expression('entity_types.Id = entities_types.TypeId AND entity_types.Status = 1')
            , array()
            , \Zend\Db\Sql\Select::JOIN_LEFT
        );
        $select->join(array('entity_type_translations' => 'entity_type_translations')
            , new Expression('entity_type_translations.TypeId = entity_types.Id AND entity_type_translations.Language IS NULL')
            , array('Type' => new Expression('GROUP_CONCAT(entity_type_translations.Name)'))
            , \Zend\Db\Sql\Select::JOIN_LEFT
        );
        
        $select->where->notEqualTo('entities.Status', 99);

        $select->group('entity_translations.Id');
        
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

    private function preselect(array $Storage = array(),&$Where = null)
    {
        $select = new Select();
        $select
        	->from($this->TableName);

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

    /*public function getById($Id, array $Storage = array())
    {
        if(!isset($Id) || empty($Id)){
            return false;
        }
        $select = $this->preselect($Storage);

        $select->where->equalTo('entities.Id',$Id);
        
        $sql = new Sql($this->Adapter);

        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        if( empty($Storage) ){
            $resultSet->setArrayObjectPrototype(new $this->EntityClass());
        }
        $resultSet->initialize($statement->execute());
        $result = $resultSet->current();

        if($result && is_scalar($Id) && $result->Id)
        {
            $ustmt = $this->Adapter->createStatement();
            $ustmt->prepare("CALL entity_details($Id);");
            $udetailsrs = new ResultSet();
            $udetails = $udetailsrs->initialize($ustmt->execute());

            $result['Details'] = $udetails->current();
        }

        return $result;
    }*/
}