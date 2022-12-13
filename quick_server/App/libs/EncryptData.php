<?php

class EncryptData
{
    private $_iv;
    private $_secret;

    private $_method = 'AES-256-CBC';

    public function __construct($secret, $iv='')
    {
        $this->_iv = substr($iv.'0000000000000000', 0,16);//可以忽略这一步，只要你保证iv长度是16
        $this->_secret = hash('md5',$secret,true);
    }

    /**
      * Encrypt the data
      * @return string
      */
    public function encrypt($data)
    {
        return base64_encode(openssl_encrypt($data, $this->_method, $this->_secret, false, $this->_iv));
    }

    /**
      * Decrypt the data
      * @return string
      */
    public function decrypt($secretData)
    {
        return openssl_decrypt(base64_decode($secretData), $this->_method, $this->_secret, false, $this->_iv);
    }
}