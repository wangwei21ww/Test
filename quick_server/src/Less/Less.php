<?php

use \Less\Foundation\App;

class Less
{
    private static $loaded = false;
    private static $application;

    /**
     *  initialize
     *  @access private
     *  @param array $conf
     */
    private static function initialize(array $conf)
    {
        // ob_start();
        // $_ENV['APPLICATION_ENV'] = RUNNING_MODE;
        // if (strtolower(RUNNING_MODE) === 'production') {
        //     ini_set('display_errors', 0);
        //     $errorReporting = 0;
        // } else {
        //     ini_set('display_errors', 1);
        //     $errorReporting = E_ALL;
        // }
        // isset($conf['errorReporting']) ? error_reporting($conf['errorReporting']) : error_reporting($errorReporting);

        // if (isset($conf['errorLog'])) {
        //     ini_set('log_errors', $conf['errorLog'] === true ? 'On' : 'Off');
        //     ini_set('error_log', isset($conf['errorLogFile']) ? $conf['errorLogFile'] : APP_PATH . 'logs/errors.log');
        // }
    }

    /**
     *  check the app is loaded or not
     *  @access public
     *  @return boolean
     */
    public function isLoaded()
    {
        return self::$loaded;
    }

    /**
     *  create and run the application
     *  @access public
     */
    public static function app(array $conf)
    {
        self::initialize($conf);
        if (self::$application === null) {
            new App($conf);
            self::$loaded = true;
        }
        return self::$application;
    }

    /**
     * The alias of the getApplication()
     * @return Application
     */
    public static function getApp()
    {
        return self::$application;
    }

    /**
     *  set the application instance to the property self::$application
     *  @access public
     */
    public static function setApp(App $application)
    {
        if (self::$application === null) {
            self::$application = $application;
        }
    }

    static public function autoload($dir)
    {
        if(is_string($dir)) {
            foreach (glob($dir) as $key => $value) {
                require_once($value);
            }
        }
        if(is_array($dir)) {
            foreach ($dir as $key => $item) {
                self::autoload($item);
            }
        }
    }
}