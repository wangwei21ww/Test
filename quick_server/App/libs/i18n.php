<?php

function __($key) {
    $language = isset($GLOBALS['sdkHeaders']['language']) ? $GLOBALS['sdkHeaders']['language'] : 'cn';
    $file = APP_PATH.'languages/'.$language.'.php'; 
    if(!file_exists($file)) {
        throw new Exception("The language file not exists", 37716);
    }
    $items = require_once($file);
    if(!isset($items[$key])) {
        return $key;
    }else{
        return $items[$key];
    }
}