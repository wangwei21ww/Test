<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

class RedisStorage
{
    public $servers = array();

    /**
     * The redis connection options
     */
    public $options = array();

    static private $instance = null;

    /**
      * Create a connection for redis
      * @param string $host
      * @param array $options
      * @return mixed
      */
    public function createConn()
    {
        if(!isset($GLOBALS['app_conf']['redis_uri'])) {
            throw new Exception("The redis URI not exists", 309121);
        }
        if(self::$instance===null) {
            self::$instance = new \Predis\Client($GLOBALS['app_conf']['redis_uri']);
        }
        return self::$instance;
    }

    public function getRedis()
    {
        return $this->createConn();
    }

    /**
      * 设置一个hash表内容
      * @param string $table
      * @param array $items 必须是一个包含键值对的array，
      *            其中键用于hash表中的名称，值为hash表中的值
      */
    public function setHash($table, $items)
    {
        if(!is_array($items)) {
          throw new Exception("The method setHash items must be an array", 394811);
        }
        $redis = $this->getRedis();
        foreach ($items as $key => $item) {
          $redis->hset($table, $key, $item);
        }
    }

    /**
      * 从hash表中取出一个键的值
      * @param string $table
      * @param string $index
      */
    public function getHash($table, $index)
    {
        return $this->getRedis()->hget($table, $index);
    }
}