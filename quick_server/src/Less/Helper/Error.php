<?php

namespace Less\Helper\Error;

/**
  * Add an error to the errors container
  * @param string $msg The message content
  * @param mixed $dump The dump info export to string to debug
  * @param string $type set to public means to client, private means to internal error.
  * @param boolean $exit true to exit the program execute continue, false to keep running.
  */
function addError($msg, $debug=null, $type='private', $exit=false)
{
    \Less::getApp()->addError($msg, $debug, $type, $exit);
}


function getError($error) {
    $reg = [
        'Integrity constraint violation' => '',
        "Duplicate entry '(.*?)' for key '(.*?)'" => '',
    ];
    foreach ($reg as $key => $item) {
        $result = preg_match_all($item, $error, $matched);
        if($result!==0 and $result!==false) {
            $matched[1][0];
        }
    }
}