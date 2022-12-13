<?php

return [
    'name' => 'quick_server',
    'defaultController' => 'api',
    'defaultAction' => 'index',
    'redis_uri' => 'tcp://10.35.46.232:6379',
    'global_data_service' => '127.0.0.1:55000',
    'supportedCoins' => ['BTC', 'ETH'], // just for main chain coin
    'chainNotifyIPs'=>['127.0.0.1'],
    'import' => [
        APP_PATH. 'base/*.php',
        APP_PATH. 'controllers/*.php',
        APP_PATH. 'models/*.php',
        APP_PATH. 'libs/*.php',
        APP_PATH. 'rpc/client/*.php',
        APP_PATH. 'validators/*.php',
        APP_PATH. 'vendor/bk_sdk/*.php',
    ],
    'chainService'=>[
        'appid'=>'', // TODO 找鄢云申请
    ],
    'services'=>[
        'token'=>'http://127.0.0.1:49999/token/issue',
        'mail'=>'http://bcloud1:50131/mail/send',
        'sms'=>'http://bcloud1:50123/SMS/send',
        'sms_text'=>'http://bcloud1:50123/SMS/text',
        'sms_verify'=>'http://bcloud1:50123/SMS/verify',
    ],
];