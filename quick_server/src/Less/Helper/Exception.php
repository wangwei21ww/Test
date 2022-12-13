<?php

namespace Less\Helper\Exception;

function exception_handler($e) {
    \Less\Helper\Request\response_header('Content-Type: application/json');

    \Less\Helper\Exception\send_http_status_code($e);

    $message = $e->getMessage();

    $messageContainer = \Less\Helper\Exception\wrapMessage($message);

    if(is_array($messageContainer)) {
        echo json_encode($messageContainer);
    }else{
        echo '{"errors": [{"message": "Internal unknown error message"}]}';
    }
    
    $info = [];
    if($e instanceof \Exception) {
        $info['message'] = $message;
        $info['file'] = $e->getFile();
        $info['code'] = $e->getCode();
        $info['line'] = $e->getLine();
        $info['trace'] = $e->getTraceAsString();
    }
    if($info!==[]) {
        file_put_contents(APP_PATH.'log/exceptions.log', json_encode($info), FILE_APPEND);
    }
}

function send_http_status_code($e) {
    $header = '\Less\Component\HttpFoundation\Header';
    $protocol = isset($_SERVER["SERVER_PROTOCOL"]) ? $_SERVER["SERVER_PROTOCOL"] : 'HTTP/1.1';
    $code = isset($header::$status[$e->getCode()]) ? $header::$status[$e->getCode()] : $header::$status['500'];
    if($code!='') {
        \Less\Helper\Request\response_header($protocol.' '.$code);
    }
}

function wrapMessage($message)
{
    $container = [];
    if(is_string($message)) {
        $temp = $message;
        $message = [$temp];
    }
    if (is_array($message)) {
        foreach ($message as $key => $item) {
            $container['errors'][] = ['message'=>$item];
        }
    }
    return $container;
}