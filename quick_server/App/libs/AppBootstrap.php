<?php

class AppBootstrap
{
    static public $nonces = [];

    public function __construct()
    {
        self::setHeaderVars();
    }

    /**
      * validate sign is valid or not
      * @
      */
    static public function validateSign()
    {
        $request = \Less::getApp()->requestParams();
        if(!is_array($request)) {
            throw new Exception("The request data invalid", 484744);
        }
        if(!isset($request['sign']) or !isset($request['nonce'])) {
            throw new Exception("The request signature and nonce must be provided", 191810);
        }
        $appId = $GLOBALS['sdkHeaders']['appId'];
        self::validateNonce($request['nonce']);
        if((new Signer)->validateSign($appId, $request, $request['sign'])===false) {
            throw new Exception("The request sign is invalid", 39112);
        }
    }

    /**
      * Set the sdk header vars
      */
    static public function setHeaderVars()
    {
        $GLOBALS['sdkHeaders'] = [];
        $headers = [
            // 'HTTP_TOKEN'=>'token',                                                          // 用户钱包身份, 表示用户唯一性
            'HTTP_LANGUAGE'=>'language',                                          // 语言
            // 'HTTP_CURRENCY'=>'currency',                                            // 货币
            //'HTTP_APPID'=>'appId',                                                            // 应用id
            // 'HTTP_CLIENTVERSION'=>'clientVersion',                         // 客户端版本
            // 'HTTP_BRAND'=>'brand',                                                         // 手机型号品牌
            // 'HTTP_OS'=>'os',                                                                         // 手机系统
        ];
        $noRequeired = ['token'];
        foreach ($headers as $name => $item) {
            if(!isset($_SERVER[$name]) and !in_array($item, $noRequeired)) {
                throw new Exception("The HTTP Request header ".$item." not exists.", 37851);
            }
            if(isset($_SERVER[$name])) {
                $GLOBALS['sdkHeaders'][$item] = $_SERVER[$name];
            }
        }
        $GLOBALS['sdkHeaders']['appId'] = $GLOBALS['app_conf']['bitKeepSdk']['appId'];
        if(isset($GLOBALS['sdkHeader']['token']) and preg_match('/[^A-Za-z0-9]+/', $GLOBALS['sdkHeader']['token'])===false) {
            throw new Exception('The token is invalid', 989611);
        }
    }

    /**
      * 验证nonce
      * nonce支持客户端签名，
      * 如果客户端时间不一致，
      * 就会导致nonce验证失败（nonce只会接受比上次时间打的时间）
      * @param int $nonce
      */
    static public function validateNonce($nonce)
    {
        $BKUUID = isset($_SERVER['BKUUID']) ? $_SERVER['BKUUID'] : '';
        $appId = $GLOBALS['sdkHeaders']['appId'];
        $appUserId = $GLOBALS['sdkHeaders']['appUserId'];
        $token = $appId.$appUserId.$BKUUID;

        if(isset(self::$nonces[$token]) and self::$nonces[$token]>$nonce) { // validate nonce is valid or not
            throw new Exception("The nonce must be greater then ".self::$nonces[$token], 544531);
        }
        self::$nonces[$token] = $nonce;
    }
}