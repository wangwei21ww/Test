<?php

namespace Less\Foundation;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

/**
  * @see https://seldaek.github.io/monolog/doc/01-usage.html
  * @example
  * $logger = Logger::getInstance(...);
  * $logger->info(...);
  */

class LessLogger
{
    /**
      * The logger instance
      * @var object
      */
    protected $logger;

    /**
      * Default log channel, see the monolog
      * @var string
      */
    public $channel = 'Logger';

    /**
      * Default log handler name, Set a name to distinguish the log channel 
      * @var string
      */
    public $channelName = 'Less';


    static public $instance;

    private function __construct($handler, $level, $channel, $name)
    {
        $this->createChannel($channel, $name);
        $this->setLogHandler($name, $handler, $level);
    }

    static public function getInstance($handler='\Monolog\Handler\StreamHandler', $level=100, $channel='\Monolog\Logger', $name='Less')
    {
        if(self::$instance===null) {
            self::$instance = new self($handler, $level, $channel, $name);
        }
        return self::$instance->logger();
    }

    public function logger()
    {
        return $this->logger;
    }

    /**
      * 
      * @param object $channel It should be a object of Monolog channel
      * @param string $name The channel name
      */
    public function createChannel($channel, $name)
    {
        if(class_exists($channel)) {
            $this->logger = new $channel($name);
            return $this->logger;
        }
        throw new HttpException("The logger channel class not found", 1199);
    }

    /**
      * Set the log handler and level
      * @param object $handler The log handler
      * @param integer $level Log level
      */
    public function setLogHandler($name, $handler, $level)
    {
        // TODO 每天每小时使用一个新的文件记录日志
        $this->logger->pushHandler(new $handler(APP_PATH.'logs/'.$name.'.log', $level));
    }
}