<?php

function l($type, $data, $force=false)
{
    if(strtolower(RUNNING_MODE)=='production' and $force===false) {
        return;
    }
    ob_start();
    var_dump($data);
    $out = ob_get_contents();
    ob_clean();
    $file = APP_PATH.'../logs/'.date('Y/m/d',time()).'/'.$type.'.log';
    $path = dirname($file);
    if(file_exists($path)===false) {
        mkdir($path, 0755, true);
    }
    file_put_contents($file, '['.date('Y-m-d H:i:s', time()).']'.$out."\n\n\n", FILE_APPEND);
}

/**
  * APP运行日志，非生产模式，或debug模式下自动开启
  * @param $error 表示在发生错误的时候记录日志
  */
function runningLog($error=false)
{
    if(RUNNING_MODE!='production' or DEBUG_MODE or $error) {
        $exeTime = microtime(true)-(isset($GLOBALS['start_time']) ? $GLOBALS['start_time'] : 0);

        $request = \Less::getApp()->requestParams();
        $response = isset($GLOBALS['App_response_contents']) ? $GLOBALS['App_response_contents'] : 'no contents';

        $log = [
            'exeTime'=>$exeTime,
            'server'=>$_SERVER,
            'req'=>$request,
            'res'=>$response,
        ];
        AppLog('request_execute_time', $log);
    }
}

function AppLog($name, $data)
{
    $file = '/data/logs/'.$GLOBALS['app_conf']['name'].'/{date}/app_{hour}.log';
    $placeholders = [
        '{date}'=>date('Y_m_d', time()),
        '{hour}'=>date('H', time())
    ];
    $filename = str_replace(array_keys($placeholders), array_values($placeholders), $file);
    $dir = dirname($filename);
    if(!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    if(file_exists($dir)) {
        file_put_contents($filename, json_encode($data)."\n", FILE_APPEND);
    }else{
        throw new Exception("Cannot create logging path", 811211);
    }
}


function ClientLog($name, $data)
{
    if(isset($_SERVER['REQUEST_URI']) and $_SERVER['REQUEST_URI']=='/log/list') {
        return;
    }
    $file = '/data/logs/'.$GLOBALS['app_conf']['name'].'_client/{date}/app_{hour}.log';
    $placeholders = [
        '{date}'=>date('Y_m_d', time()),
        '{hour}'=>date('H', time())
    ];
    $filename = str_replace(array_keys($placeholders), array_values($placeholders), $file);
    $dir = dirname($filename);
    if(!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    if(file_exists($dir)) {
        file_put_contents($filename, print_r($data, true), FILE_APPEND);
    }else{
        throw new Exception("Cannot create logging path", 811212);
    }
}