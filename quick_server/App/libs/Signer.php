<?php

class Signer
{
    /**
      * Make a sign with data and appid appSecret
      * @param string $appId
      * @param string $appSecret
      * @param array $data
      * @return string
      */
    public function makeSign($appSecret, $data)
    {
        $data['appSecret'] = $appSecret;
        ksort($data);
        return hash('sha256', strtolower(http_build_query($data)));
    }

    /**
      * Validate the sign is valid or not.
      * @param string $appId
      * @param string $appSecret
      * @param array $data
      * @param string $sign
      * @return boolean
      */
    public function validateSign($appId, $data, $sign)
    {
        $appSecret = (new App)->getAppSecret($appId);
        $data['appSecret'] = $appSecret;
        unset($data['sign']);
        return $this->makeSign($appSecret, $data) == strtolower($sign);
    }
}