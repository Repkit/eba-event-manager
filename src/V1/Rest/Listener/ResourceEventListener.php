<?php

namespace MicroIceEventManager\V1\Rest\Listener;

use Zend\EventManager\Event;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

/**
 * Global resource event listener.
 * To handle new events, add new methods that follow the naming convention:
 *      [collectionName][EventType][Pre|Post], eg: eventProfileDetailsDeletePost
 * Pass with trigger whatever variables necessary, such as record Id or model
 */
class ResourceEventListener
{
    /**
     * Not yet implemented callback functions end up here
     */
    public function __call($name, $arguments) {
        return FALSE;
    }
    
    //TODO: maybe collect all query strings and execute only once
    public function eventsDeletePost(Event $Event)
    {
        try
        {
            $queries = [];

            $eid = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            /* @var $adapter \Zend\Db\Adapter\Adapter */
            $sql = new Sql($adapter);

            $select = new Select();
            $select->columns(array('Id')) ->from('event_translations') ->where(array('EventId' => $eid));
            $statement = $sql->prepareStatementForSqlObject($select);
            $translations = new ResultSet();
            $translations->initialize($statement->execute());

            foreach ($translations as $translation) 
            {
                $id = $translation->Id;

                // get array of DataId values that needs to be deleted
                $select = new Select();
                $select->columns(array('DataId')) ->from('events_data') ->where(array('EventId' => $id));
                $statement = $sql->prepareStatementForSqlObject($select);
                $resultSet = new ResultSet();
                $dataId = $resultSet->initialize($statement->execute())->toArray();
                array_walk($dataId, function(&$val){ $val = $val['DataId']; });

                if(!empty($dataId)){

                    // delete related `event_data_fields`
                    $update = new Update();
                    $update->table('event_data_fields')->set(array('Status' => 99))->where(array('DataId' => $dataId));
                    $queries[] = $sql->getSqlStringForSqlObject($update);
                    $sql->prepareStatementForSqlObject($update)->execute();
                    
                    // delete related `event_data`
                    $update = new Update();
                    $update->table('event_data')->set(array('Status' => 99))->where(array('Id' => $dataId));
                    $queries[] = $sql->getSqlStringForSqlObject($update);
                    $sql->prepareStatementForSqlObject($update)->execute();

                }
                
                // delete from related tables by TranslationId column
                $relatedTables = array('events_data', 'events_profiles', 'event_details');
                foreach ($relatedTables as $table) {
                    $update = new Update();
                    $update->table($table)->set(array('Status' => 99))->where(array('EventId' => $id));
                    $queries[] = $sql->getSqlStringForSqlObject($update);
                    $sql->prepareStatementForSqlObject($update)->execute();
                }

                // delete from related tables by EventId column
                $relatedTables = array('event_translations', 'events_types', 'events_services', 'events_entities');
                foreach ($relatedTables as $table) {
                    $update = new Update();
                    $update->table($table)->set(array('Status' => 99))->where(array('EventId' => $eid));
                    $queries[] = $sql->getSqlStringForSqlObject($update);
                    $sql->prepareStatementForSqlObject($update)->execute();
                }
            }

            unset($queries);

            return true;
        }
        catch(\Exception $e)
        {
            // var_dump($queries);exit(__FILE__.'::'.__LINE__);
            throw $e;
        }
    }

     //TODO: maybe collect all query strings and execute only once
    public function entitiesDeletePost(Event $Event)
    {
        try
        {
            $queries = [];

            $eid = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            /* @var $adapter \Zend\Db\Adapter\Adapter */
            $sql = new Sql($adapter);

            $select = new Select();
            $select->columns(array('Id')) ->from('entity_translations') ->where(array('EntityId' => $eid));
            $statement = $sql->prepareStatementForSqlObject($select);
            $translations = new ResultSet();
            $translations->initialize($statement->execute());

            foreach ($translations as $translation) 
            {
                $id = $translation->Id;

                // get array of DataId values that needs to be deleted
                $select = new Select();
                $select->columns(array('DataId')) ->from('entities_data') ->where(array('EntityId' => $id));
                $statement = $sql->prepareStatementForSqlObject($select);
                $resultSet = new ResultSet();
                $dataId = $resultSet->initialize($statement->execute())->toArray();
                array_walk($dataId, function(&$val){ $val = $val['DataId']; });

                if(!empty($dataId)){

                    // delete related `entity_data_fields`
                    $update = new Update();
                    $update->table('entity_data_fields')->set(array('Status' => 99))->where(array('DataId' => $dataId));
                    $queries[] = $sql->getSqlStringForSqlObject($update);
                    $sql->prepareStatementForSqlObject($update)->execute();
                    
                    // delete related `entity_data`
                    $update = new Update();
                    $update->table('entity_data')->set(array('Status' => 99))->where(array('Id' => $dataId));
                    $queries[] = $sql->getSqlStringForSqlObject($update);
                    $sql->prepareStatementForSqlObject($update)->execute();

                }
                
                // delete from related tables by TranslationId column
                $relatedTables = array('entities_data', 'entities_profiles', 'entity_details');
                foreach ($relatedTables as $table) {
                    $update = new Update();
                    $update->table($table)->set(array('Status' => 99))->where(array('EntityId' => $id));
                    $queries[] = $sql->getSqlStringForSqlObject($update);
                    $sql->prepareStatementForSqlObject($update)->execute();
                }

                // delete from related tables by EntityId column
                $relatedTables = array('entity_translations', 'entities_types');
                foreach ($relatedTables as $table) {
                    $update = new Update();
                    $update->table($table)->set(array('Status' => 99))->where(array('EntityId' => $eid));
                    $queries[] = $sql->getSqlStringForSqlObject($update);
                    $sql->prepareStatementForSqlObject($update)->execute();
                }
            }

            unset($queries);

            return true;
        }
        catch(\Exception $e)
        {
            // var_dump($queries);exit(__FILE__.'::'.__LINE__);
            throw $e;
        }
    }

    public function eventTypesDeletePost(Event $Event)
    {
        try
        {
            $queries = [];

            $eid = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            /* @var $adapter \Zend\Db\Adapter\Adapter */
            $sql = new Sql($adapter);

            $relatedTables = array('event_type_translations', 'events_types');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('TypeId' => $eid));
                $queries[] = $sql->getSqlStringForSqlObject($update);
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            $update = new Update();
            $update->table('event_types_data_types')->set(array('Status' => 99))->where(array('EventTypeId' => $eid));
            $queries[] = $sql->getSqlStringForSqlObject($update);
            $sql->prepareStatementForSqlObject($update)->execute();

            unset($queries);

            return true;
        }
        catch(\Exception $e)
        {
            // var_dump($queries);exit(__FILE__.'::'.__LINE__);
            throw $e;
        }
    }

    public function entityTypesDeletePost(Event $Event)
    {
        try
        {
            $queries = [];

            $eid = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            /* @var $adapter \Zend\Db\Adapter\Adapter */
            $sql = new Sql($adapter);

            $relatedTables = array('entity_type_translations', 'entities_types');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('TypeId' => $eid));
                $queries[] = $sql->getSqlStringForSqlObject($update);
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            $update = new Update();
            $update->table('entity_types_data_types')->set(array('Status' => 99))->where(array('EntityTypeId' => $eid));
            $queries[] = $sql->getSqlStringForSqlObject($update);
            $sql->prepareStatementForSqlObject($update)->execute();

            unset($queries);

            return true;
        }
        catch(\Exception $e)
        {
            // var_dump($queries);exit(__FILE__.'::'.__LINE__);
            throw $e;
        }
    }

    public function entityProfileTypesDeletePost(Event $Event)
    {
        try
        {
            $eId = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            $sql = new Sql($adapter);

            $relatedTables = array('entity_profile_type_translations', 'entity_profile_detail_fields', 'entity_profile_details', 'entity_profiles_types');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('TypeId' => $eId));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            return true;
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    public function entityProfilesDeletePost(Event $Event)
    {
        try
        {
            $eId = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            $sql = new Sql($adapter);

            $relatedTables = array('entity_profile_translations', 'entities_profiles', 'entity_profiles_types', 'entity_profile_preferences');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('ProfileId' => $eId));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            return true;
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    public function entityProfileDetailFieldsDeletePost(Event $Event)
    {
        try
        {
            $eId = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            $sql = new Sql($adapter);

            $select = new Select();
            $select->from('entity_profile_detail_fields');
            $select->where->equalTo('Id', $eId);
            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet();
            $resultSet->initialize($statement->execute());
            $result = $resultSet->current();

            $relatedTables = array('entity_profile_details');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('Field' => $result->Field));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            return true;
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    public function entityProfileDataTypesDeletePost(Event $Event)
    {
        try
        {
            $eId = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            $sql = new Sql($adapter);

            // entity_profile_data_field_config
            $select = new Select();
            $select->from('entity_profile_data_field_config');
            $select->where->equalTo('TypeId', $eId);
            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet();
            $resultSet->initialize($statement->execute());
            $result = $resultSet->current();

            $update = new Update();
            $update->table('entity_profile_data_field_config')->set(array('Status' => 99))->where(array('TypeId' => $eId));
            $sql->prepareStatementForSqlObject($update)->execute();

            // delete from related tables by Field column
            $relatedTables = array('entity_profile_data_fields');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('Field' => $result->Field));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            // entity_profile_data
            $select = new Select();
            $select->from('entity_profile_data');
            $select->where->equalTo('TypeId', $eId);
            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet();
            $resultSet->initialize($statement->execute());
            $result = $resultSet->current();

            $update = new Update();
            $update->table('entity_profile_data')->set(array('Status' => 99))->where(array('TypeId' => $eId));
            $sql->prepareStatementForSqlObject($update)->execute();

            // delete from related tables by DataId column
            $relatedTables = array('entity_profiles_data', 'entity_profile_data_fields', 'entity_profile_data_preferences');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('DataId' => $result->Id));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            return true;
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    public function entityProfileDataDeletePost(Event $Event)
    {
        try
        {
            $eId = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            $sql = new Sql($adapter);

            $relatedTables = array('entity_profiles_data', 'entity_profile_data_fields', 'entity_profile_data_preferences');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('DataId' => $eId));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            return true;
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    public function entityProfileDataFieldConfigDeletePost(Event $Event)
    {
        try
        {
            $eId = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            $sql = new Sql($adapter);

            $select = new Select();
            $select->from('entity_profile_data_field_config');
            $select->where->equalTo('Id', $eId);
            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet();
            $resultSet->initialize($statement->execute());
            $result = $resultSet->current();

            $relatedTables = array('entity_profile_data_fields');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('Field' => $result->Field));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            return true;
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    public function eventProfileTypesDeletePost(Event $Event)
    {
        try
        {
            $eId = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            $sql = new Sql($adapter);

            $relatedTables = array('event_profile_type_translations', 'event_profile_detail_fields', 'event_profile_details', 'event_profiles_types');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('TypeId' => $eId));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            return true;
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    public function eventProfilesDeletePost(Event $Event)
    {
        try
        {
            $eId = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            $sql = new Sql($adapter);

            $relatedTables = array('event_profile_translations', 'events_profiles', 'event_profiles_types', 'event_profile_preferences');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('ProfileId' => $eId));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            return true;
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    public function eventProfileDetailFieldsDeletePost(Event $Event)
    {
        try
        {
            $eId = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            $sql = new Sql($adapter);

            $select = new Select();
            $select->from('event_profile_detail_fields');
            $select->where->equalTo('Id', $eId);
            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet();
            $resultSet->initialize($statement->execute());
            $result = $resultSet->current();

            $relatedTables = array('event_profile_details');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('Field' => $result->Field));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            return true;
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    public function eventProfileDataTypesDeletePost(Event $Event)
    {
        try
        {
            $eId = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            $sql = new Sql($adapter);

            // event_profile_data_field_config
            $select = new Select();
            $select->from('event_profile_data_field_config');
            $select->where->equalTo('TypeId', $eId);
            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet();
            $resultSet->initialize($statement->execute());
            $result = $resultSet->current();

            $update = new Update();
            $update->table('event_profile_data_field_config')->set(array('Status' => 99))->where(array('TypeId' => $eId));
            $sql->prepareStatementForSqlObject($update)->execute();

            // delete from related tables by Field column
            $relatedTables = array('event_profile_data_fields');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('Field' => $result->Field));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            // event_profile_data
            $select = new Select();
            $select->from('event_profile_data');
            $select->where->equalTo('TypeId', $eId);
            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet();
            $resultSet->initialize($statement->execute());
            $result = $resultSet->current();

            $update = new Update();
            $update->table('event_profile_data')->set(array('Status' => 99))->where(array('TypeId' => $eId));
            $sql->prepareStatementForSqlObject($update)->execute();

            // delete from related tables by DataId column
            $relatedTables = array('event_profiles_data', 'event_profile_data_fields', 'event_profile_data_preferences');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('DataId' => $result->Id));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            return true;
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    public function eventProfileDataDeletePost(Event $Event)
    {
        try
        {
            $eId = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            $sql = new Sql($adapter);

            $relatedTables = array('event_profiles_data', 'event_profile_data_fields', 'event_profile_data_preferences');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('DataId' => $eId));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            return true;
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    public function eventProfileDataFieldConfigDeletePost(Event $Event)
    {
        try
        {
            $eId = $Event->getParam('id');
            $adapter = $Event->getParam('adapter');
            $sql = new Sql($adapter);

            $select = new Select();
            $select->from('event_profile_data_field_config');
            $select->where->equalTo('Id', $eId);
            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet();
            $resultSet->initialize($statement->execute());
            $result = $resultSet->current();

            $relatedTables = array('event_profile_data_fields');
            foreach ($relatedTables as $table) {
                $update = new Update();
                $update->table($table)->set(array('Status' => 99))->where(array('Field' => $result->Field));
                $sql->prepareStatementForSqlObject($update)->execute();
            }

            return true;
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }
}