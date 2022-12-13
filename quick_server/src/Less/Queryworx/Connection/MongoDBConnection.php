<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Less\Queryworx\Connection;

class MongoDBConnection extends DbConnection
{
    /**
     * The dsn means Data source name, specifying the database connection parameters.
     * @var string
     */
    public $dsn;
    
    /**
     * Specific connection option of the driver
     * @var array
     */
    public $options = array();
    
    /**
     * The Connection is active
     * @var boolean
     */
    public $active = false;
    
    /**
     * The db instance
     * @var object Statement
     */
    private $dbInstance=null;
    
    /**
     * Checking the Mongo extension is exist or not.
     * @throws \RuntimeException
     */
    public function __construct()
    {
        if(!class_exists('mongo'))
        {
            throw new \RuntimeException("The Mongo extension does not exist.");
        }
    }
    
    /**
     * Returns a MongoDB object
     * @return object
     */
    public function getDbInstance()
    {
        if(!$this->dbInstance instanceof \MongoDB)
        {
            $this->createConnection();
        }
        return $this->dbInstance;
    }
    
    /**
     * Creates a DB connection
     * @return DB Statement
     * @throws MongoConnectionException
     */
    public function createConnection()
    {
        try
        {
            $this->dbInstance = new \MongoClient($this->dsn,$this->options);
            $this->active = true;
        }catch(\MongoConnectionException $e){
            throw new \MongoConnectionException($e->getMessage());
        }
        return $this->dbInstance;
    }
    
    /**
     * Closes a MongoDB connection
     */
    public function closeConnection()
    {
        $this->active = false;
        $this->dbInstance = null;
    }
}