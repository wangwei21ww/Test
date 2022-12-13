<?php

return [
    'defaultController' => 'quick_server',
    'defaultAction' => 'index',
    'TOKEN_PRIVATE_KEY'=>'123',
    'redis_uri' => 'tcp://dev.redis:6379',
    'bitKeepSdk' => [
        'running_mode' => 'dev',  // support dev or prod
        'dev_url' => 'http://dev.bitkeep.com:9001',
        'prod_url' => 'http://dev.bitkeep.com:9001',
        'ca_cert' => APP_PATH . 'vendor/bk_sdk/certs/ca_cert.pem',
        'apiVersion' => '0.0.1', // API version
        'platform' => 'web', // web ios andorid
        'language' => 'zh_cn', // cn en
        'currency' => 'cny', // support cny usd
        'appId' => 'ie4N3tRMsTmycuxkj92gH8CEKpPVr6dz',
        'apiKey' => 'Ix2SX1-Ik7gm5-eZ7dqr-5BQUDE-KziIxS',
        'apiSecret' => 'iGsaXR2MuDhQxalg7lTI5JoVEvqSWKAxLF8Wtz1OCP4XkfnosYYdT7UOudjw830D'
    ],
    'apis' => [
        'api' => '127.0.0.1:40222',
        'rateApi' => '127.0.0.1:53101'
    ],
    'db' => [
        'host' => 'dev.mysql',
        'port' => 3306,
        'db_name' => 'app_quick',
        'username' => 'root',
        'password' => '6yhnmju7@cc',
        'options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"),
        'tablePrefix' => '',
    ],
    'bannerUpload' => [
        'uploadDir' => '/data/devenv/huobiao/quick_server/web/',
        'browserDomain' => 'http://dev.bitkeep.com:40444/'
    ],
];
