<?php

namespace Less\Foundation;

use Less\Websocket\HeaderReact;
use Less\Helper\Error;
use \GatewayWorker\Lib\Gateway;

class MessageReactive
{
    public $db;
    public $clientId;

    // TODO validate and check if the db not conn, throw an error
    public function parse($message)
    {
        $this->parseHeaders(isset($message['headers']) ? $message['headers'] : []);
        return $this->parseBody(isset($message['body']) ? $message['body'] : []);
    }

    public function parseHeaders($headers)
    {
        $headerReact = new HeaderReact;
        foreach ($headers as $name => $item) {
            $method = $name.'Process';
            if(method_exists($headerReact, $method)) {
                call_user_func_array([$headerReact, $method], [$item]);
            }
        }
    }

    public function parseBody($body)
    {
        if($this->validateBodyParams($body)) {
            $model = ucfirst($body['model']);
            $method = $body['method'];
            $query = isset($body['query']) ? $body['query'] : [];
            $data = isset($body['data']) ? $body['data'] : [];
            return call_user_func_array([new $model, 'entry'], [$method, $query, $data]);
        }
        return \Less::getApp()->addError('The request params invalid', null, 'public', true);
    }

    protected function validateBodyParams($body)
    {
        if(!isset($body['model']) or !isset($body['method'])) {
            return false;
        }
        if(in_array($body['method'], ['read','update', 'delete']) and !isset($body['query']['$where'])) {
            return false;
        }
        if(in_array($body['method'], ['create','update']) and !isset($body['data'])) {
            return false;
        }
        return true;
    }
}