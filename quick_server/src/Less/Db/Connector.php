<?php

namespace Less\Db;

class Connector
{
    public $settings;
    public $pdo;

    static public $connPool = [];

    public function __construct($host, $port, $user, $password, $dbName, $charset = 'utf8')
    {
        $this->settings = [
            'host' => $host,
            'port' => $port,
            'user' => $user,
            'password' => $password,
            'dbname' => $dbName,
            'charset' => $charset
        ];
    }

    /**
     * 创建 PDO 实例
     * @param string $name
     * @param array $options The array options
     * @return $db
     */
    protected function connect($name, $options=[])
    {
        $dsn = 'mysql:dbname={dbname};host={host};port={port}';
        $options = [
            PDO::ATTR_PERSISTENT=>true,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_STRINGIFY_FETCHES=>false,
            PDO::ATTR_EMULATE_PREPARES=>false
        ];
        $db = new PDO($dsn, $this->settings["user"], $this->settings["password"], $options);
        $db = $this->setPdoOptions($db, $options);
        return $db;
    }

    /**
      * Set the PDO options
      * @param object $instance
      * @param array $options
      */
    public function setPdoOptions($instance, $options)
    {
        foreach ($options as $key => $item) {
            $instance->setAttribute($key, $item);
        }
        return $instance;
    }

    /**
      * Create a DB connection
      * @param string $name
      */
    public function createConn($name='default')
    {
        self::$connPool[$name] = $this->connect($name);
    }

    /**
      * Get db instance
      * @param string $name
      * @return object
      */
    public function getDB($name='default')
    {
        if($name=='') {
            throw new \Exception("The db name cannot be empty", 12911);
        }
        if(isset(self::$connPool[$name])) {
            return self::$connPool[$name];
        }
    }

   /**
    * close db conn
    * @param string $name The db link name
    */
    public function closeConnection($name)
    {
        if(isset(self::$connPool[$name])) {
            self::$connPool[$name] = null;
        }
    }
}