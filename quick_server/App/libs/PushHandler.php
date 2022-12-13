<?php

class PushHandler
{
    public $pushText = '';

    public function setPushText($template, $placeholders)
    {
        $this->pushText = str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }

    public function getPushText()
    {
        return $this->pushText;
    }

    public function sendPush($token)
    {
        $pushText = $this->getPushText();
        $log_path = APP_PATH.'../logs/'.date('Y/m/d/',time()).'push.log';
        if($pushText=='') {
            throw new Exception('The push text is empty', 98815);
        }
        $app_key = $GLOBALS['app_conf']['jiguang_push']['app_key'];
        $master_secret = $GLOBALS['app_conf']['jiguang_push']['master_secret'];
        $client = new \JPush\Client($app_key, $master_secret, $log_path);
    }
}
