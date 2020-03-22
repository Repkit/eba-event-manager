<?php
namespace MicroIceEventManager\V1\Rest\EventsEntities;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EventsEntitiesModel extends Model
{
    protected $EntityClass  = 'MicroIceEventManager\V1\Rest\EventsEntities\EventsEntitiesEntity';
    protected $TableName    = 'events_entities';

    public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByEventIdAndEntityId($EventId, $EntityId)
    {

        if(!isset($EventId) || empty($EventId)){
            return false;
        }
        if(!isset($EntityId) || empty($EntityId)){
            return false;
        }
        return $this->select(array('EventId' => $EventId, 'EntityId' => $EntityId))->current();

    }

    public function getByEntityIdAndEventId($EntityId, $EventId)
    {
        return $this->getByEventIdAndEntityId($EventId, $EntityId);
    }

    public function getAllEventsByEntityId($EntityId, array $where = array())
    {
        if(!isset($EntityId) || empty($EntityId)){
            return false;
        }

        $select = new Select();
        $select->from(array('events_entities' => $this->TableName))
            ->join(array('events' => 'events')
                , 'events_entities.EventId = events.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
            );
        $select->where(array('events_entities.EntityId'=>$EntityId));

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

    public function getAllEntitiesByEventId($EventId, array $where = array())
    {
        if(!isset($EventId) || empty($EventId)){
            return false;
        }

        $select = new Select();
        $select->from(array('events_entities' => $this->TableName))
            ->join(array('entities' => 'entities')
                , 'events_entities.EntityId = entities.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
            );
        // $select->columns(array());
        $select->where(array('events_entities.EventId'=>$EventId));

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

    public function getAllExtended($Storage = array(), $Where = null, $EventId = null)
    {
        $select = $this->preselect($Storage, $Where);

        $select->columns(array('EventId','EntityId'));

        $select->join(array('entities' => 'entities')
            , 'events_entities.EntityId = entities.Id'
            , array(\Zend\Db\Sql\Select::SQL_STAR)
            , \Zend\Db\Sql\Select::JOIN_INNER
        );
        $select->join(array('entity_translations' => 'entity_translations')
            , new Expression('events_entities.EntityId = entity_translations.EntityId AND entity_translations.Language IS NULL')
            , array('Name')
            , \Zend\Db\Sql\Select::JOIN_INNER
        );
        $select->join(array('entities_types' => 'entities_types')
            , 'events_entities.EntityId = entities_types.EntityId'
            , array('EntityTypeId' => new Expression('GROUP_CONCAT(TypeId)'))
            , \Zend\Db\Sql\Select::JOIN_LEFT
        );
        $select->group('events_entities.EntityId');
       
        $select->where(array('events_entities.Status' => array(0,1)));
        if(!empty($EventId)){
            $select->where(array('events_entities.EventId' => $EventId));
        }

        // var_dump($Where);exit(__FILE__.'::'.__LINE__);
        if(!empty($Where)){
            // $select->where($Where);
            $select = \TBoxDbFilter\DbFilter::withWhere($select, $Where);
        }   

        $sql = new Sql($this->Adapter);

        // echo $sql->getSqlStringForSqlObject($select);exit();
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        $resultSet->setArrayObjectPrototype(new $this->EntityClass);

        return $resultSet;
    }

    private function preselect(array $Storage = array(),&$Where = null)
    {
        $select = new Select();
        $select
            ->from($this->TableName);
        $select->where->notEqualTo('events_entities.Status', 99);
        
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
}
