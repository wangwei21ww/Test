<?php

return [
    'defaultController' => 'quick_server',
    'defaultAction' => 'index',
    'TOKEN_PRIVATE_KEY' => '123',
    'redis_uri' => 'tcp://127.0.0.1:6379',
    'bitKeepSdk' => [
        'running_mode' => 'prod',  // support dev or prod
        'dev_url' => 'http://127.0.0.1:9001',
        'prod_url' => 'https://gate.bitkeep.top',
        'ca_cert' => APP_PATH . 'vendor/bk_sdk/certs/ca_cert.pem',
        'apiVersion' => '0.0.1', // API version
        'platform' => 'web', // web ios andorid
        'language' => 'zh', // cn en
        'currency' => 'cny', // support cny usd
        'appId' => 'zkZTD9cpuwbKv81E0AhOVRriBqdlQS6y',
        'apiKey' => 'X137sq-wioScX-AvaM4W-Zpq0TM-NBVowI',
        'apiSecret' => 'RtnMTV1l79ChgzxmMSIBz8NP7pLx5nALri3FSZPkoAeWGavKONbHJw6tGu6XDOw8'
    ],
    'apis' => [
        'api' => '127.0.0.1:40222',
        'rateApi' => '127.0.0.1:53101'
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'db_name' => 'app_quick',
        'username' => 'root',
        'password' => 'Quming1@',
        'options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"),
        'tablePrefix' => '',
    ],
    'bannerUpload' => [
        'uploadDir' => '/data/imgs/web/',
        'browserDomain' => 'https://cpapi.quickmining.co/'
    ],
];
