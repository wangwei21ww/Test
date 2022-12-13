<?php

namespace Less\Helper\Request;

function response_header($item) {
    if(isset($_SERVER['SERVER_SOFTWARE']) and strpos($_SERVER['SERVER_SOFTWARE'],'workerman')!==false) {
        \Workerman\Protocols\Http::header($item);
    }else{
        if(headers_sent()===false) {
            header($item);
        }
    }
}

function response_end($val='') {
    if(isset($_SERVER['SERVER_SOFTWARE']) and strpos($_SERVER['SERVER_SOFTWARE'],'workerman')!==false) {
        \Workerman\Protocols\Http::end($val);
    }else{
        exit($val);
    }
}

function header_remove($name) {
    if(isset($_SERVER['SERVER_SOFTWARE']) and strpos($_SERVER['SERVER_SOFTWARE'],'workerman')!==false) {
        \Workerman\Protocols\Http::headerRemove($name);
    }else{
        if(function_exists('header_remove') and headers_sent()===false) {
            header_remove($name);
        }
    }
}