<?php

namespace Less\Foundation;

class User
{
    // public $defaultGuestRole  = ['guest'];
    // public $defaultMemberRole = ['member'];

    // // begin generate token and verify
    // public $privateKey = 'j9u3r9usyehy98yu38u98y9ys8934y';

    // public function getPrivateKey($data)
    // {
    //     $salt = isset($data['salt']) ? $data['salt'] : '';
    //     return $this->privateKey.$salt;
    // }

    // /**
    //   * issue token if the user success
    //   * @param array $data The user info, must contains userId, salt
    //   */
    // public function issueToken($data)
    // {
    //     $data['issueAt'] = time();
    //     $data['expireAt'] = time()+3600*24;
    //     $data['sign'] = $this->generateToken($data);
    //     return $this->encodeToken($data);
    // }

    // public function generateToken($data, $verify=false)
    // {
    //     if($verify===true) {
    //         unset($data['sign']);
    //     }
    //     ksort($data);
    //     return hash_hmac('sha256', json_encode($data), $this->getPrivateKey($data));
    // }

    // public function encodeToken(array $data)
    // {
    //     return base64_encode(json_encode($data));
    // }

    // public function decodeToken($data)
    // {
    //     return json_decode(base64_decode($data), true);
    // }

    // public function verify($token)
    // {
    //     $data = $this->decodeToken($token);
    //     $sign = '';
    //     if(!isset($data['sign'])){
    //         return false;
    //     }else{
    //         $sign = $data['sign'];
    //     }
    //     return $sign === $this->generateToken($data, true);
    // }
    // // end generate token and verify



    // // begin gateway bind user id 
    // /**
    //   * bind user id
    //   * @param string token
    //   */
    // public function bindUserId($userId, $token, $clientId)
    // {
    //     if($this->verify($token)===true) { // bind user id
    //         \GatewayWorker\Lib\Gateway::bindUid($clientId,$userId);
    //     }else{
    //         // send msg to current client, validate failed
    //     }
    // }

    // /**
    //   * unbind user id, with gateway, when logout
    //   * @param string $token
    //   */
    // public function unbindUserId($userId, $token, $clientId)
    // {
    //     if($this->verify($token)===true) { // unbind user id
    //         \GatewayWorker\Lib\Gateway::unbindUid($clientId,$userId);
    //     }else{
    //         // send msg to current client, logout failed.
    //     }
    // }
    // // end gateway bind user id 





    public $defaultGuestRole  = ['guest'];
    public $defaultMemberRole = ['member'];

    /**
      * 在控制器中设置角色访问权限的方法名称
      * @var string defualts to auth
      */
    public $authMethod = 'auth';

    /**
     * The application was defined user roles.
     */
    protected $userRoles = [];

    /**
     * check the user whether is some one role
     * and get some one property from cookie, if it is exists.
     */
    public function __call($method, $value)
    {
        if (substr($method, 0, 2) === 'is') {
            return in_array(lcfirst(substr($method, 2)), $this->getUserRole());
        }
        if(substr($method, 0, 3) == 'get') {
            $item = lcfirst(substr($method, 3));
            if($this->accessByToken()===true) {
                $userSession = $this->getUserSession()->getUserIdentity();
                return isset($userSession[$item]) ? $userSession[$item] : '';
            }
            if ($this->isGuest() === false) {
                $userSession = $this->getUserSession()->getUserIdentity();
                return isset($userSession[$item]) ? $userSession[$item] : '';
            }
        }
    }

    /**
     * Get the role of the user.
     * @return array
     */
    public function getUserRole()
    {
        if ($this->isGuest()) {
            return $this->defaultGuestRole;
        }
        $userSession = $this->getUserSession()->getUserIdentity();
        return isset($userSession['role']) ? (array) $userSession['role'] : [];
    }

    /**
     * Check whether user is logged in or not.
     * @return boolean if it is a guest return to true otherwise to false
     */
    public function isGuest()
    {
        return !isset($_SERVER['HTTP_AUTHORIZATION']);
    }

    /**
      * The request is access by token or not.
      * @return boolean
      */
    public function accessByToken()
    {
        return isset($_SERVER['HTTP_AUTHORIZATION']);
    }

    /**
     * Get the UserSession instance.
     */
    public function getUserSession()
    {
        // TODO use the default cookie or JWT
        if($this->accessByToken()) {
            $jwt = new JsonWebToken('lesscloud','app');
            return $jwt;
        }
    }

    public function getUserToken($claim)
    {
        $jwt = new JsonWebToken('lesscloud','app'); // TODO temp code
        $token = $jwt->getToken();
        $parse = $jwt->parseToken($token);
        return $parse->getClaim($claim);
    }

    /**
     * Validate the user whether have permission to access an action or not.
     */
    public function validateAccess($controller, $action)
    {
        $this->isValid($controller, $action, $this->getUserRole());
    }

    /**
     * Get user allowed actions
     * @param array $authRules
     * @return array if the key actions not exists,that to return an empty array
     */
    public function getUserAllowedActions($role, $authRules)
    {
        return isset($authRules[$role]['allowedActions']) ? (array) $authRules[$role]['allowedActions'] : [];
    }

    public function getUserDeniedActions($role, $authRules)
    {
        return isset($authRules[$role]['deniedActions']) ? (array) $authRules[$role]['deniedActions'] : [];
    }

    public function getAccessRules($controller)
    {
        $authRules = [];

        if (method_exists($controller, $this->authMethod)) {
            $authRules = $controller->{$this->authMethod}();
        }

        if (!is_array($authRules)) {
            throw new \RuntimeException("The method auth must be return an array");
        }
        return $authRules;
    }

    /**
     * Validation the user access rules.
     * @param Controller $controller
     * @param string $action
     * @param array $userRoles
     */
    public function isValid($controller, $action, array $userRoles = [])
    {
        $action = strtolower($action);

        $authRules = $this->getAccessRules($controller);

        if ($authRules === []) {
            return;
        }

        if (isset($authRules['*'])) {
            array_push($userRoles, '*');
        }

        $message = "Access to the requested resource has been denied.";

        foreach ($userRoles as $role) {

            if (!isset($authRules[$role])) {
                continue;
            }

            if ($this->validateAllowedActions($role, $authRules, $action) === true) {
                return;
            }

            if ($this->validateDeniedActions($role, $authRules, $action) === true) {
                return;
            }
        }
        throw new HttpException($message, 403);
    }

    public static $requestMethod;

    public static function getRequestMethod()
    {
        if (self::$requestMethod === null) {
            self::$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
        }
        return self::$requestMethod;
    }

    public function validateAllowedActions($role, $authRules, $action)
    {
        $userAllowedActions = $this->getUserAllowedActions($role, $authRules);

        if ($userAllowedActions !== []) {
            $func = "\Less\Helper\ArrayHelper\iin_array";
            if (in_array("*", $userAllowedActions) or $func($action, $userAllowedActions)) {
                return true;
            }
            if ($this->validateRequestMethodRules($action, $userAllowedActions, $func)) {
                return true;
            }
            return false;
        }
    }

    public function validateDeniedActions($role, $authRules, $action)
    {
        $userDeniedActions = $this->getUserDeniedActions($role, $authRules);

        if ($userDeniedActions !== []) {
            $func = "\Less\Helper\ArrayHelper\iin_array";
            if (in_array("*", $userDeniedActions) or $func($action, $userDeniedActions)) {
                return false;
            }
            if ($this->validateRequestMethodRules($action, $userDeniedActions, $func)) {
                return false;
            }
            return true;
        }
    }

    public function validateRequestMethodRules($action, $userRuleActions, $func)
    {
        $requestMethod = self::getRequestMethod();
        foreach ($userRuleActions as $actionName => $methods) {
            if (is_string($actionName) and lcfirst($actionName) == lcfirst($action) and is_array($methods) and $func($requestMethod, $methods)) {
                return true;
            }
        }
        return false;
    }
}
