<?php

class BaseController
{
    /**
      * Get the app nonce
      * @return string
      */
    public function getAppNonce()
    {
        $BKUUID = isset($_SERVER['BKUUID']) ? $_SERVER['BKUUID'] : '';
        $appId = $GLOBALS['sdkHeaders']['appId'];
        $appUserId = $GLOBALS['sdkHeaders']['appUserId'];
        $token = $appId.$appUserId.$BKUUID;
        if(!isset(AppBootstrap::$nonces[$token])) {
          throw new Exception("The request nonce not exists", 391141);
        }
        return AppBootstrap::$nonces[$token];
    }
    
    /**
      * Get the userId
      * @return string
      */
    public function getUserId()
    {
        return hash('sha256', strtolower($this->getAppId().$this->getAppUserId()));
    }

    /**
      * Get the appId from HTTP request token
      * @return string
      */
    public function getAppId()
    {
        if(!isset($GLOBALS['sdkHeaders']['appId'])) {
            throw new Exception("The appId not in HTTP Request headers", 40313);
        }
        return strtolower(trim($GLOBALS['sdkHeaders']['appId']));
    }

    /**
      * Get the appUserId from HTTP request token
      * @return string
      */
    public function getAppUserId()
    {
        if(!isset($GLOBALS['sdkHeaders']['appUserId'])) {
            throw new Exception("The appUserId not in HTTP Request headers", 40314);
        }
        return strtolower(trim($GLOBALS['sdkHeaders']['appUserId']));
    }

  /**
   * @param $key
   * @param $value
   * @param int $code
   * @return mixed
   * @throws Exception
   */
  function assertParam($key, $value, $code = 601)
  {
    if (empty($value)) {
      NetException($code, 'Params exist ' . $key);
    }
    return $value;
  }
}