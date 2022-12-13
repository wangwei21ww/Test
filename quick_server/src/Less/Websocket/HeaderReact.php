<?php

namespace Less\Websocket;

use Less\Foundation\User;

class HeaderReact
{
    public $autoBindUserId = true;
    
    public function tokenProcess($token)
    {
        if($this->validateUserToken($token)===false) {
            throw new Exception("The access token is invalid", 5011);
        }
    }

    public function validateUserToken($token)
    {
        return (new User)->verify($token);
    }
}