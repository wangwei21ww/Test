<?php

class GDStore
{
    static public $globalData = null;

    static public function getGlobalData()
    {
        if(self::$globalData===null) {
            self::$globalData = new GlobalData\Client($GLOBALS['app_conf']['global_data_service']);
        }
        return self::$globalData;
    }
}