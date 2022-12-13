<?php

class ValidateToken
{
    /**
     * 验证签名需要的键是否存在
     * @param array $data
     * @return boolean
     */
    protected function validateItems($data)
    {
        $items = ["appId", "userId", "nonce", "salt", "realms", "expired", "apiKey"];
        foreach ($items as $item) {
            if (!isset($data[$item])) {
                throw new Exception('The ' . $item . ' doesn\'t exists', 3430);
            }
        }
        return true;
    }

    /**
     * 创建签名
     * @param array $data
     * @return string
     */
    protected function sign($data, $apiKey, $apiSecret)
    {
        $this->validateItems($data);
        $data['apiSecret'] = $apiSecret;
        ksort($data);
        // var_dump($data);
        // exit;
        return hash('sha256', hash('sha256', json_encode($data)));
    }

    /**
     * 验证签名
     * @param string $token
     * @return boolean
     */
    public function validate($token, $apiKey, $apiSecret)
    {
        $data = json_decode(base64_decode($token), true);
        if (!isset($data['sign'])) {
            throw new Exception('The sign key doesn\'n exists', 34361);
        }
        if (!isset($data['expired']) or (isset($data['expired']) and time() > $data['expired'])) {
            throw new Exception('The token was expired', 400100);
        }
        $sign = $data['sign'];
        unset($data['sign']);
        return $this->sign($data, $apiKey, $apiSecret) == $sign;
    }
}
