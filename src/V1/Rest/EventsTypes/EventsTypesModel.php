<?php
namespace MicroIceEventManager\V1\Rest\EventsTypes;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class EventsTypesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EventsTypes\EventsTypesEntity';
	protected $TableName 	= 'events_types';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByEventIdAndTypeId($EventId, $TypeId)
    {

        if(!isset($EventId) || empty($EventId)){
            return false;
        }
        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }
        return $this->select(array('EventId' => $EventId, 'TypeId' => $TypeId))->current();

    }

    public function getByTypeIdAndEventId($TypeId, $EventId)
    {
        return $this->getByEventIdAndTypeId($EventId, $TypeId);
    }

    public function getAllEventsByTypeId($TypeId, array $where = array())
    {
        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }

        $select = new Select();
        $select->from(array('events_types' => $this->TableName))
            ->join(array('events' => 'events')
                , 'events_types.EventId = events.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
            )
            ->join(array('event_translations' => 'event_translations')
                , 'event_translations.EventId = events.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
            );
        $select->where(array('events_types.TypeId'=>$TypeId));
        $select->where(array('event_translations.Language'=>null));

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

    public function getAllTypesByEventIdAndLanguage($EventId, $Language = null, array $where = array())
    {
        if(!isset($EventId) || empty($EventId)){
            return false;
        }

        if(null == $Language){
            $langcond = ' IS NULL';
        }else{
            $langcond = " = '$Language' ";
        }

        $select = new Select();
        $select->from(array('events_types' => $this->TableName));

        $select->join(array('event_types' => 'event_types')
                , 'events_types.TypeId = event_types.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
        );

        $select->join(array('event_type_translations' => 'event_type_translations')
            , new Expression('event_type_translations.TypeId = event_types.Id AND event_type_translations.Language ' . $langcond)
            // , array('Type' => new Expression('GROUP_CONCAT(event_type_translations.Name)'))
            , array('Name')
            , \Zend\Db\Sql\Select::JOIN_LEFT
        );

        // $select->columns(array());
        $select->where(array('events_types.EventId'=>$EventId));

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

    public function getAllTypesByEventId($EventId, array $where = array())
    {
        if(!isset($EventId) || empty($EventId)){
            return false;
        }

        $select = new Select();
        $select->from(array('events_types' => $this->TableName));

        $select->join(array('event_types' => 'event_types')
                , 'events_types.TypeId = event_types.Id'
                , array(\Zend\Db\Sql\Select::SQL_STAR)
                , \Zend\Db\Sql\Select::JOIN_INNER
        );

        $select->join(array('event_type_translations' => 'event_type_translations')
            , new Expression('event_type_translations.TypeId = event_types.Id AND event_type_translations.Language IS NULL')
            // , array('Type' => new Expression('GROUP_CONCAT(event_type_translations.Name)'))
            , array('Name')
            , \Zend\Db\Sql\Select::JOIN_INNER
        );

        // $select->columns(array());
        $select->where(array('events_types.EventId'=>$EventId));

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
