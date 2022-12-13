<?php

namespace Less\Foundation;

use \GatewayWorker\Lib\Gateway;

class App
{
  public $errors = [];

  public $errorTemplates = [];
  /**
   * cannot override attr
   * @var obj
   */
  public $logger;

  /**
   * The following attrs could be override from config
   */
  public $appName = 'Less';

  public $controllerPrefix = '';
  public $controllerSuffix = 'Controller';
  public $actionPrefix = 'action';
  public $actionSuffix = '';

  public $smartControllers = ['ApiController'];

  public $request;

  public function __construct(array $config)
  {
    foreach ($config as $key => $value) {
      $this->{$key} = $value;
    }
    $this->init();
    \Less::setApp($this);
  }

  /**
   * initialize the app
   */
  public function init()
  {
    $this->logger = LessLogger::getInstance();
  }

  /**
   * running the app, if the app running in normal http service
   * @
   */
  public function run()
  {
    $this->request = new Request;
    $controllerName = $this->controllerNameWrap();
    $actionName = $this->actionNameWrap();
    if (class_exists($controllerName, false) === false) {
      throw new \Exception("The API " . $controllerName . " cannot be found", 404000);
    }
    $controller = new $controllerName;
    (new User)->validateAccess($controller, $actionName); // validate the user identity and request premission
    new \AppBootstrap;

    if (in_array($controllerName, $this->smartControllers)) {
      $results = $this->callRESTfulAction($controller, ucfirst($this->request->query['action']));
    } else {
      $results = $this->callAction($controller, $actionName);
    }
    (new Response)->output($this->wrapMessage($results));
  }

  /**
   * call the action
   * @param object $instance
   * @param string $method
   */
  public function callAction($instance, $method)
  {
    $class = get_class($instance);
    if (method_exists($instance, $method) === false) {
      throw new \Exception("The API address cannot be found", 404001);
    }
    $RM = new \ReflectionMethod($class, $method);
    $requestParams = $this->requestParams();

    $method_params = $RM->getParameters();
    $container = [];
    if (is_array($method_params)) {
      foreach ($method_params as $key => $item) {
        if (isset($requestParams[$item->name])) {
          $container[$item->name] = $requestParams[$item->name];
        }else{
          $container[$item->name] = $item->getDefaultValue();
        }
      }
    }

    if ($RM->getNumberOfRequiredParameters() > count($container)) {
      $methodParams = $this->exportMethodParams($method_params);
      $diff = array_diff($methodParams, array_keys($container));
      throw new \Exception("The request params is invalid, missing " . implode(",", $diff), 40921);
    }
    return call_user_func_array([$instance, $method], $container);
  }

  /**
   * call the restful action
   */
  public function callRESTfulAction($instance, $model)
  {
      // $class = get_class($instance);
      // $controllerName = strtolower($this->controllerPrefix.'Api'.$this->controllerSuffix);
      // if(strtolower($class)!=$controllerName) {
      //   throw new \Exception("The API address cannot be found", 404002);
      // }
    $requestParams = $this->requestParams();
    $method = \Less::getApp()->request->method;

    $paramsMapping = [
      'GET' => ['query'],
      'POST' => ['attrs'],
      'PUT' => ['query', 'attrs'],
      'DELETE' => ['query']
    ];
    if (!isset($paramsMapping[$method])) {
      throw new \Exception("The request method not supported", 405994);
    }
    if ($method == 'GET') {
      $requestParams = $_GET;
    }
    return call_user_func_array([$instance, $model], [$requestParams]);
  }

  /**
   * export the method params with reflaction method
   * @param array $paramObjs
   * @return array
   */
  public function exportMethodParams($paramObjs)
  {
    $contianer = [];
    foreach ($paramObjs as $key => $paramObj) {
      $container[] = $paramObj->name;
    }
    return $container;
  }

  /**
   * Get the request params, from component Request
   * @return array
   */
  public function requestParams()
  {
    return \Less::getApp()->request->request_data;
  }

  /**
   * Add an error to the errors container
   * @param string $msg The message content
   * @param mixed $dump The dump info export to string to debug
   * @param string $type set to public means to client, private means to internal error.
   * @param boolean $exit true to exit the program execute continue, false to keep running.
   */
  public function addError($msg, $code = 0, $type = 'public', $exit = true)
  {
    $transMsg = $this->errorTranslator($msg);
    \Less::getApp()->logger->error($msg);
    $results = $this->errors[] = $this->wrapMessage([], $msg, $code);
    return $results;
  }

  /**
   * Set an error to the errors container
   * @param string $msg The message content
   * @param mixed $dump The dump info export to string to debug
   * @param string $type set to public means to client, private means to internal error.
   * @param boolean $exit true to exit the program execute continue, false to keep running.
   */
  public function setError($code = 0, $bindData = [], $privateMsg = '', $debug = null)
  {
    $messages = $this->loadI18nMessages();
          // TODO set the language, defaults to en, may get the language from client user agent
    $language = 'en';
    if (!isset($messages[$code]) or !isset($messages[$code][$language])) {
      throw new Exception("The message code not exists", 1);
    }
    $msg = $messages[$code][$language];
    $transMsg = $this->errorTranslator($msg);
    \Less::getApp()->logger->error($msg);
    if ($privateMsg != '') {
      \Less::getApp()->logger->error('private:' . $privateMsg);
    }
    if ($debug !== null) {
      \Less::getApp()->logger->error('var_export:' . var_export($debug));
    }
    return $this->wrapMessage([], $msg, $code);
  }

  public function loadI18nMessages()
  {
    return require_once(APP_PATH . 'config/i18n.php');
  }

  /**
   * Wrap the error message
   * @param string $msg
   * @param int $code
   * @return array
   */
  public function wrapMessage($data, $msg = '', $code = 0)
  {
    if(isset($data['wrapData']) and $data['wrapData']===false) {
      unset($data['wrapData']);
      return $data;
    }
    if (isset($data['status']) and isset($data['msg']) and isset($data['data'])) {
      return $data;
    }
    return [
      'status' => $code,
      'msg' => $msg,
      'data' => $data
    ];
  }

  public function errorTranslator($error)
  {
    foreach ($this->errorTemplates as $key => $item) {
      preg_match_all($key, $error, $matched);
      if (isset($matched[1][0])) {
        return $item;
      }
    }
    return $error;
  }

  public function controllerNameWrap()
  {
    return ucfirst($this->controllerPrefix) . ucfirst($this->request->query['controller']) . ucfirst($this->controllerSuffix);
  }

  public function actionNameWrap()
  {
    return lcfirst($this->actionPrefix) . ucfirst($this->request->query['action']) . ucfirst($this->actionSuffix);
  }
}