<?php

namespace MicroIceEventManager\V1\Rest;

use Zend\Db\Adapter\Adapter,
    Zend\Db\ResultSet\ResultSet,
    Zend\Db\TableGateway\TableGateway;

abstract class AbstractModel extends TableGateway
{

    //-------------------------------------------//
    // copy-paste from DbManager/Model [20171216]
    //-------------------------------------------//
    protected $Table;
    protected $EntityClass = 'MicroIceEventManager\V1\Rest\AbstractEntity';
    protected $TableName = null;
    protected $PrimaryKey = 'Id';
    protected $PrimaryKeys = array ();
    protected $ForeignKeys = null;
    protected $AutoIncrementColumns = array('Id');
    protected $Entities = array ();
    protected $LocalEntities = array ();
    protected $Cache;
    protected $Columns;
    protected $ColumnNames;
    protected $Constraints;
    protected $MandatoryFields;
    protected $MandatoryFieldNames;
    protected $InsertValues = array ();
    protected $UpdateValues = array ();
    protected $Foreigners = array (); //not used yet
    protected $Events;

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = array())
    {
        if(is_array($Options))
        {
            // overwrite table name
            if(!empty($Options['tableName'])){
                $this->TableName = $Options['tableName'];
            }

            // overwrite primaryKey
            if(!empty($Options['primaryKey'])){
                $this->PrimaryKey = $Options['primaryKey'];
            }
        }

    	// create result set prototype
        $entityClass = $this->EntityClass;
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new $entityClass());

        parent::__construct($this->TableName, $Adapter, null, $resultSetPrototype);
    }

    public function getById($Id)
    {

        if(!isset($Id) || empty($Id)){
            return false;
        }
        return $this->select(array('Id' => $Id))->current();

    }

    public function getByName($Name)
    {

    	if(!isset($Name) || empty($Name)){
    		return false;
    	}
    	return $this->select(array('Name' => $Name))->current();

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

    public function getAll()
    {
        return $this->fetchAll(array());
    }

	/**
     * Logical deletion
     *
     * @param  Where|\Closure|string|array $where
     * @return int
     */
    // public function delete($Where)
    // {
    //     return $this->update(array('Status' => 99), $Where);
    // }

    public function getEntityClass()
    {
    	return $this->EntityClass;
    }

    public function getMandatoryFieldNames()
    {
        return $this->MandatoryFieldNames;
    }

    /**
     * Get column names
     *
     * @return array
     */
    public function getColumnNames()
    {
        return $this->ColumnNames;
    }

    /**
     * Check if an Entity is valid for save into table
     * !!!IMPORTANT: USE === true to check if and entity is valid for save!!!
     * @param object $Entity
     * @return array on fail; (bool)TRUE on success
     */
    public function validateEntity(AbstractEntity $Entity)
    {
        $mandatoryFields = $this->getMandatoryFieldNames();
        if(!isset($mandatoryFields) || empty($mandatoryFields))
        {
            return true;
        }
        $entityFields = array();
        // $entity = $Entity->castToArray();
        $entity = $Entity->toArray();
        foreach ($entity as $prop => $value) 
        {
            if(isset($value)){
                $entityFields[$prop] = true; //the key is important
            }
        }
        $difference = array_diff_key(array_flip($mandatoryFields), $entityFields);
        if(count($difference) > 0)
        {
            return $difference;
        }
        else
        {
            return true;
        }
    }

    //-------------------------------------------//
    // copy-paste from DbManager/Model [20171216]
    //-------------------------------------------//

    /**
     *
     * @param  $Entity
     * @return array
     */
    public function createBulkInsert($Entity)
    {
        // try
        // {
            /*if(!\Application\Utils\AppObject::isIterable($Entity))
            {
                if($Entity instanceof $this->EntityClass)
                {
                    $Entity = array($Entity);
                }
            }
            else
            {
                if($Entity instanceof $this->EntityClass)
                {
                    $Entity = array($Entity);
                }
            }*/
            $isInstance = false;
            if($Entity instanceof $this->EntityClass)
            {
                $isInstance = true;
                $Entity = array($Entity);
            }
            $Entities = $Entity;
            foreach($Entities as $Entity)
            {
                if($isInstance || $Entity instanceof $this->EntityClass)
                {
                    if($this->validateEntity($Entity) === true)
                    {
                        //if mysql extension then use INSERT statements with multiple VALUES lists to insert several rows at a time.
                        //@link: https://dev.mysql.com/doc/refman/5.0/en/insert-speed.html
                        // if(strpos(strtolower($this->getDriverName()),'mysql') !== FALSE)
                        // {
                            $columns = array_flip($this->getColumnNames());
                            if(!isset($columns) || empty($columns)){
                                throw new \Exception("Columns definition is required for bulk actions", 1);
                            }
                            $intersection = array_intersect_key($Entity->toArray(), $columns);
                            $insert = array_intersect_key(array_replace($columns, $intersection), $intersection);
                            // $this->InsertValues[] = "('" . implode("','", array_map('addslashes', $insert)) . "')";
                            
                            $prepared = join(', ', array_map(function ($value) {
                                return $value === null ? 'NULL' : "'".addslashes($value)."'";
                            }, $insert));
                            
                            $this->InsertValues[] = "( $prepared )";
                        // }
                        // else
                        // {
                        //     $insert = $this->getSql()->insert();
                        //     $insert->columns($Entity->expose());
                        //     $insert->values($Entity->toArray());
                        //     $this->InsertValues[] = $this->getSql()->getSqlStringForSqlObject($insert);
                        // }
                    }
                    else
                    {
                        continue;
                    }
                }
                else
                {
                    continue;
                }
            }
            
            return $this->InsertValues;
            /*if($Entity instanceof $this->EntityClass)
            {
                if($this->validateEntity($Entity) === true)
                {
                    //if mysql extension then use INSERT statements with multiple VALUES lists to insert several rows at a time.
                    //@link: https://dev.mysql.com/doc/refman/5.0/en/insert-speed.html
                    if(strpos(strtolower($this->getDriverName()),'mysql') !== FALSE)
                    {
                        $columns = array_flip($this->getColumnNames());
                        $intersection = array_intersect_key($Entity->toArray(), $columns);
                        $insert = array_intersect_key(array_replace($columns, $intersection), $intersection);
                        $this->InsertValues[] = "('" . implode("','", array_map('addslashes', $insert)) . "')";
                    }
                    else
                    {
                        $insert = $this->getSql()->insert();
                        $insert->columns($Entity->expose());
                        $insert->values($Entity->toArray());
                        $this->InsertValues[] = $this->getSql()->getSqlStringForSqlObject($insert);
                    }
                    return $this->InsertValues;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }*/
        // }
        // catch (\Exception $e)
        // {
        //     return false;
        // }
    }

    /**
     * Dump $this->InsertValues into table
     * @param type $TruncateFirst bool [true]
     * @return $Sql FALSE on fail
     */
    public function runBulkInsert($TruncateFirst = true)
    {
        // try
        // {
            if($TruncateFirst)
            {
                if(($this->truncate()) == FALSE)
                {
                    return false;
                }
            }
            if(isset($this->InsertValues) && (count($this->InsertValues) > 0))
            {
                //if mysql extension then use INSERT statements with multiple VALUES lists to insert several rows at a time.
                //@link: https://dev.mysql.com/doc/refman/5.0/en/insert-speed.html
                // if(strpos(strtolower($this->getDriverName()),'mysql') !== FALSE)
                // {
                    // $columns = array_flip($this->getColumnNames());
                    $columns = $this->getColumnNames();
                    // var_dump($columns);exit(__FILE__.'::'.__LINE__);
                    if(!isset($columns) || empty($columns)){
                        throw new \Exception("Columns definition is required for bulk actions", 1);
                    }
                    $strColumns = '`' . implode("`,`", $columns) . '`';
                    $values = implode(' , ', $this->InsertValues);
                    $query = "  INSERT INTO {$this->TableName} ({$strColumns}) VALUES {$values};";
                // }
                // else
                // {
                //     $query = $this->InsertValues;
                // }
                //reset container
                $this->InsertValues = array ();
                
                return $this->execute($query);
            }
            else
            {
                throw new \Exception("Nothing to insert", 1);
            }
        // }
        // catch (\Exception $e)
        // {
        //     return false;
        // }
    }

    /**
     * Truncate this table
     * @return boolean
     */
    public function truncate()
    {
        try
        {
            $query = "TRUNCATE TABLE {$this->TableName}";
            $result = $this->execute($query);

            //trigger update event
            //$this->getEventManager()->trigger(__NAMESPACE__ . __FUNCTION__, $this, array ('where' => $where));

            return $result;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    /**
     * Run Sql query
     * @param string/array/iterator $Sql
     * @return $Sql FALSE on fail
     */
    public function execute($Sql)
    {
        // var_dump($Sql);exit(__FILE__.'::'.__LINE__);
        // try
        // {
            $adapter = $this->getAdapter();
            if(is_string($Sql))
            {
                $sql = $Sql;
            }
            elseif(is_array($Sql))
            {
                $sql = implode(';',$Sql);
            }
            elseif($Sql instanceof \Traversable)
            {
                //note JB [20171216 00:01] - this is not optimized but is late night -_-
                $array = array();
                foreach ($Sql as $key => $value)
                {
                    $array[$key] = $value;
                }
                $sql = implode(';', $array);
            }
            else
            {
                throw new \Exception('Sql parameter must be type of string, array or iterator!');
            }
            $Sql = null; //free up some memory
            $sql = str_replace(';;', ';', $sql);
            $adapter->query($sql, $adapter::QUERY_MODE_EXECUTE);
            return $sql;
        // }
        // catch (\Exception $e)
        // {
        //     return false;
        // }
    }

    /**
     *
     * @return array
     */
    public function getInsertValues()
    {
        return $this->InsertValues;
    }

    /**
     *
     * @param array $InsertValues
     */
    public function setInsertValues($InsertValues)
    {
        $this->InsertValues = $InsertValues;
    }

}