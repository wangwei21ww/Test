<?php

class IdentityToken
{
    /**
     * 颁发签名
     * @param array $data
     * @return string
     */
    public function issue($identity, $nonce, $salt, $realms, $expired)
    {
        $data = ['identity' => $identity, 'nonce' => $nonce, 'salt' => $salt, 'realms' => $realms, 'expired' => $expired];
        if (!is_array($data)) {
            throw new Exception('The sign data must be an array', 3431);
        }
        $data['sign'] = $this->sign($data);
        return base64_encode(json_encode($data));
    }

    /**
     * 验证签名
     * @param string $token
     * @return boolean
     */
    public function validate($token)
    {
        $data = json_decode(base64_decode($token), true);
        if (!isset($data['sign'])) {
            throw new Exception('The sign key doesn\'n exists', 34361);
        }
        if(!isset($data['expired']) or (isset($data['expired']) and time()>$data['expired'])) {
            throw new Exception('The token was expired', 400100);
        }
        $sign = $data['sign'];
        unset($data['sign']);
        return $this->sign($data) == $sign;
    }

    /**
     * Get token data
     *
     * @param [type] $token
     * @return void
     */
    public function getTokenData($token)
    {
        if($this->validate($token)) {
            return json_decode(base64_decode($token), true);
        }
        return false;
    }

    /**
     * 验证签名需要的键是否存在
     * @param array $data
     * @return boolean
     */
    protected function validateItems($data)
    {
        $items = ["identity", "nonce", "salt", "realms", "expired"];
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
    protected function sign($data)
    {
        $this->validateItems($data);
        $data['PK'] = $GLOBALS['app_conf']['TOKEN_PRIVATE_KEY'];
        ksort($data);
        return hash('sha256', hash('sha256', json_encode($data)));
    }
}
