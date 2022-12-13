<?php

class RPCService
{
    static public $services = [];

    /**
     * Get service instance
     *
     * @param [type] $name The service name
     * @param [type] $class The RPC class
     * @return void
     */
    public function getService($name, $class)
    {
        if (!isset(self::$services[$name]) or self::$services[$name] === null) {
            RpcClient::config(self::getRPCAPIs($name));
            self::$services[$name] = RpcClient::instance($class, self::getPRCPK($name));
        }
        return self::$services[$name];
    }

    /**
     * Get the RPC APIs
     * The APIs contains one or more item, must return an array
     * @param string $name
     * @return array
     */
    static public function getRPCAPIs($name)
    {
        if (!isset($GLOBALS['app_conf']['rpc_services'][$name]['apis'])) {
            throw new Exception('The service ' . $name . ' conf not exists', 843135);
        }
        return $GLOBALS['app_conf']['rpc_services'][$name]['apis'];
    }

    /**
     * Get the RPC private key
     *
     * @param string $name
     * @return string
     */
    static public function getPRCPK($name)
    {
        if (isset($GLOBALS['app_conf']['rpc_services'][$name]['pk'])) {
            return $GLOBALS['app_conf']['rpc_services'][$name]['pk'];
        }
        return '';
    }
}