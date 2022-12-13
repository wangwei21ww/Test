<?php

namespace Less\Foundation;

class Request
{
    public $query = [];
    public $method = 'GET';
    public $request_data = [];

    protected $methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];

    static public $AccessControlAllowCredentials = 'false';
    static public $AccessControlAllowMethods = 'GET, POST, PUT, DELETE, OPTIONS';
    // static public $AccessControlAllowHeaders = '*';
    static public $AccessControlAllowHeaders = 'LESS-APP-ID, LESS-APP-SECRET, LESS-APP-VERSION, X-Requested-With, Authorization, Content-Type, X-Referer,currency,language,token';
    static public $AccessControlExposeHeaders = 'Location,Content-Location,X-Less-Error,Content-Type';

    public function __construct()
    {
        $this->parsePreflightRequest();
        $this->parseRoute();
        $this->parseRequest();
    }

    public function __get($name)
    {
        if (isset($this->query[$name])) {
            return $this->query[$name];
        }
    }

    public function parseRoute()
    {
        $items = [];
        $requestURI = '';
        if (isset($_SERVER['REQUEST_URI']) and trim($_SERVER['REQUEST_URI']) != '') {
            $requestURI = $_SERVER['REQUEST_URI'];
        }

        if (isset($_SERVER['QUERY_STRING']) and trim($_SERVER['QUERY_STRING']) != '') {
            $requestURI = substr($requestURI, 0, strpos($requestURI, '?'));
        }

        if (isset($requestURI[0]) and $requestURI[0] == '/') {
            $requestURI = substr($requestURI, 1);
        }

        $items = explode('/', str_replace('//', '/', $requestURI));

        $items['controller'] = (isset($items[0]) and trim($items[0]) != '') ? $items[0] : \Less::getApp()->defaultController;
        $items['action'] = (isset($items[1]) and trim($items[1]) != '') ? $items[1] : \Less::getApp()->defaultAction;
        $this->query = array_merge($_GET, $items);
    }

    public function parseRequest()
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        if (!in_array($method, $this->methods)) {
            throw new Exception("The request method not supported", 409011);
        }
        $this->method = $method;
        if (isset($_SERVER['CONTENT_TYPE']) and strtolower($_SERVER['CONTENT_TYPE']) == 'application/x-www-form-urlencoded') {
            if ($method == 'POST') {
                $this->request_data = $_POST;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE']) and strtolower($_SERVER['CONTENT_TYPE']) == 'application/json') {
            if (isset($GLOBALS['HTTP_RAW_REQUEST_DATA']) and is_string($GLOBALS['HTTP_RAW_REQUEST_DATA'])) {
                $this->request_data = json_decode($GLOBALS['HTTP_RAW_REQUEST_DATA'], true);
            } else {
                $this->request_data = json_decode(file_get_contents('php://input'), true);
            }
        }
    }

    public function parsePreflightRequest()
    {
        $header = '\Less\Helper\Request\response_header';

        $header('Access-Control-Allow-Origin: *');

        $header('Access-Control-Allow-Credentials: ' . self::$AccessControlAllowCredentials);
        $header('Access-Control-Allow-Methods: ' . self::$AccessControlAllowMethods);
        $header('Access-Control-Allow-Headers: ' . self::$AccessControlAllowHeaders);
        $header('Access-Control-Expose-Headers: ' . self::$AccessControlExposeHeaders); // 暴露那些header
        $header('Access-Control-Max-Age: 3600');
        if (function_exists('header_remove')) {
            // header_remove("X-Powered-By");
        }
        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            \Less\Helper\Request\response_end();
        }
    }
}
