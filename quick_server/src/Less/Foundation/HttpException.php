<?php

namespace Less\Foundation;

class HttpException extends \Exception
{
    public $statusCode;
    public static $headerErrorName = "X-Less-Error";

    public function __construct($message, $code = 0)
    {
        if(!is_string($message)) {
            $message = json_encode($message);
            \Less::getApp()->logger->error($message);
        }
        parent::__construct($message, $code);
    }
}
