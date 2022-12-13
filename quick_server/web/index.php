<?php

$GLOBALS['start_time'] = microtime(true);
$errors = [];

try{
    Less::app($GLOBALS['app_conf'])->run();
}catch(Exception $e){
    if(strpos(strtoupper($e->getMessage()), 'SQL')!==false) {
    // if($e instanceof PDOException) {
        $errors = ['status'=>57777,'msg'=>'DB Process failed', 'data'=>[]];
        l('db_exception', [$e->getCode(), $e->getMessage()], true); // save to log
    }else{
        $errors = ['status'=>$e->getCode(),'msg'=>__($e->getMessage()), 'data'=>[]];
    }
    if($errors['msg']!='' and $errors['status']==0) {
        $errors['status'] = 500000;
    }
    $exception = json_encode($errors, JSON_FORCE_OBJECT);
    $GLOBALS['App_response_contents'] = $exception;
    (new \Less\Foundation\Response)->output($exception);
}

runningLog(true);