<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Less\Queryworx\Base;

use Less\Queryworx\Connection\DbConnection;
use Less\Queryworx\Schema\DbSchema;

class ObjectModel extends Model
{
    /**
     * Database connection instance
     * @var object
     */
    private $_connection;

    static private $_models;

    protected $_dataOptionName;

    /**
     * The data operation scenario.
     */
    private $_scenario;
    
    protected $connectionParams;
    protected $_dbInstance;
    protected $_collection;
    protected $_isNewRecord;
    
    public function __construct($scenario='insert')
    {
        if($scenario===null){return;}
        $this->setScenario($scenario);
        $this->setIsNewRecord(true);
        
        if($this->_dataOptionName=='')
        {
            throw new \RuntimeException('The _dataOptionName should be provided.');
        }

        if(!isset(\Lightworx::getApplication()->dataOptions[$this->_dataOptionName]))
        {
            throw new \RuntimeException('The specify _dataOptionName cannot be found.');
        }
        $this->connectionParams = \Lightworx::getApplication()->dataOptions[$this->_dataOptionName];
        $this->init();
    }

    public function init()
    {
        $this->createConnection();
    }

    public function getMetaData(){}

    public function getIsNewRecord()
    {
        return $this->_isNewRecord;
    }
    
    public function setIsNewRecord($isNewRecord)
    {
        $this->_isNewRecord = $isNewRecord;
    }

    public function setAttribute($name,$value)
    {
        if(property_exists($this,$name))
        {
            $this->{$name} = $value;
        }
        $this->_attributes[$name] = $value;
        return true;
    }

    protected function relations()
    {
        return array();
    }

    public function getAttributes($returnName=true)
    {
        if($returnName===true)
        {
            return array_keys($this->_attributes);
        }
        return $this->_attributes;
    }

    public function setAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
    }

    protected function beforeFind(){return true;}

    protected function afterFind(){}

    /**
     * Gets a attribute if it is exists.
     * @param string $name
     */
    public function __get($name)
    {
        if(isset($this->_attributes[$name]))
        {
            return $this->_attributes[$name];
        }
        
        $relations = $this->relations();
        if(isset($relations[$name]))
        {
            if(!isset($this->_relationModels[$name]))
            {
                $this->_relationModels[$name] = $this->createRelationModel($relations[$name]);
            }
            return $this->_relationModels[$name];
        }
        
        if(isset($this->getMetaData()->columns[$name]))
        {
            return null;
        }
        return parent::__get($name);
    }
    
    /**
     * PHP magic method, sets a property for current model.
     * @param string $name
     * @param mixed $value
     */
    public function __set($name,$value)
    {
        if($this->setAttribute($name,$value)===false)
        {
            if(isset($this->getMetadata()->relations[$name]))
            {
                $this->_related[$name]=$value;
            }
            parent::__set($name,$value);
        }
        return parent::__set($name,$value);
    }

    protected function instantiate($attributes)
    {
        $class=get_class($this);
        $model=new $class(null);
        return $model;
    }

    /**
     * The method responsible for for creating a database connection
     */
    public function initializeConnection()
    {
        $connection = $this->getConnector();
        
        foreach($this->connectionParams as $property=>$value)
        {
            $connection->{$property} = $value;
        }
        
        $this->setConnection($connection);
    }
    
    /**
     * Returns a DbConnection instance
     * @return object
     */
    public function getConnection()
    {
        if($this->_connection===null)
        {
            $this->initializeConnection();
        }
        return $this->_connection;
    }
    
    /**
     * Sets a connectio to property _connection
     * @param DbConnection $connection
     */
    public function setConnection(DbConnection $connection)
    {
        $this->_connection = $connection;
    }
    
    /**
     * Instances a connector, and same time that will unset
     * the connector of property connectionParams
     * @return DbConnector return the connector instance
     * @throws RuntimeException when cannot found the connector, 
     *                                               that will be throw an exception.
     */
    public function getConnector()
    {
        if(isset($this->connectionParams['connector']))
        {
            $connector = "\\Less\\Queryworx\\Connection\\".$this->connectionParams['connector'];
            unset($this->connectionParams['connector']);
            return new $connector;
        }
        throw new \RuntimeException("The connector have no setting");
    }
    
    /**
     * Get current model scenario
     */
    public function getScenario()
    {
        return $this->_scenario;
    }
    
    /**
     * Set current model scenario
     */
    public function setScenario($scenario)
    {
        $this->_scenario = $scenario;
    }
    
    /**
     * Return database driver name
     * @return string
     */
    public function getDriverName()
    {
        return $this->getConnection()->getDriverName();
    }
    
    /**
     * Creates an model object
     * @param string $className
     * @return ObjectModel
     */
    public static function model($className=__CLASS__)
    {
        if(isset(self::$_models[$className]))
        {
            return self::$_models[$className];
        }else{
            return self::$_models[$className]=new $className(null);
        }
    }

    public function getPrimaryKey()
    {
        return $this->_id;
    }

    public function getPrimaryKeyName()
    {
        return '_id';
    }

    /**
     * Creates an active record with the given attributes.
     * @param array $attributes
     * @param boolean callAfterFind defaults to true
     */
    public function populateRecord(array $attributes,$callAfterFind=true)
    {
        if($attributes!==false)
        {
            $record=$this->instantiate($attributes);
            $record->setScenario('update');
            $record->init();
            $md=$record->getMetaData();
            foreach($attributes as $name=>$value)
            {
                if(is_string($value) and strpos($value,'{"')===0)
                {
                    $record->$name = $record->_oldAttributes[$name] = $record->_attributes[$name] = json_decode($value,true);
                }else{
                    $record->$name = $record->_oldAttributes[$name] = $record->_attributes[$name] = $value;
                }
            }

            $record->pk=$record->getPrimaryKey();

            if($callAfterFind)
                $record->afterFind();
            return $record;
        }
        return null;
    }

    protected function createRecordToken($record)
    {
        $pk = $record->{$record->getPrimaryKeyName()};
        $state = \Lightworx::getApplication()->getState($this->_stateName);
        $record->{$this->_tokenName} = md5($pk.$state);
        return $record;
    }

    /**
     * Creates a list of active records based on the input data.
     */
    public function populateRecords($data,$callAfterFind=true,$index=null)
    {
        $records=array();
        foreach($data as $attributes)
        {
            if(($record=$this->populateRecord($attributes,$callAfterFind))!==null)
            {
                if($index===null)
                {
                    $records[]=$record;
                }else{
                    $records[$record->$index]=$record;
                }
            }
        }
        return $records;
    }

    public function createConnection()
    {
        if($this->_dbInstance===null)
        {
            $this->_dbInstance = $this->getConnection()->createConnection();
            $this->_collection = $this->_dbInstance->selectCollection($this->connectionParams['dbName'],$this->connectionParams['collectionName']);
        }
        return $this->_dbInstance;
    }
}