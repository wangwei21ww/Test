<?php

namespace Less\Foundation;

class Response
{
    public $headers = [];
    public function output($content)
    {
        $this->headers[] = 'Server: Less-Nest';
        $this->headers[] = 'Content-Type: application/json';

        $this->sendHeader();
        if(is_array($content)) {
            if(isset($content['data']) and $content['data']===[]) {
                $content = json_encode($content, JSON_FORCE_OBJECT);
            }else{
                $content = json_encode($content);
            }
        }
        $GLOBALS['App_response_contents'] = $content;
        echo $content;
    }

    public function sendHeader()
    {
        foreach ($this->headers as $key => $value) {
            $item = is_string($key) ? $key . ': ' . $value : $value;
            self::response_header($item);
        }
    }

    static public function response_header($item) {
        \Workerman\Protocols\Http::header($item);
    }

    static public function response_end($val='') {
        \Workerman\Protocols\Http::end($val);
    }

    static public function header_remove($name) {
        \Workerman\Protocols\Http::headerRemove($name);
    }
}
