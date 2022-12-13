<?php

/**
 * 递归排序数组中的键
 *
 * @param array $ar
 * @return array
 */
function recursiveKsort(array $ar)
{
  ksort($ar);
  foreach ($ar as $k => $item) {
    if (is_array($item)) {
      $ar[$k] = recursiveKsort($item);
    }
  }
  return $ar;
}

function getIP() {
  return isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
}

function hasToken() {
  return isset($GLOBALS['sdkHeaders']['token']);
}

/**
 * Get the HTTP Request header item
 * @param string The header item name
 * @return string The returned string convert to lower case
 */
function getHeader($name) {
  if (!isset($GLOBALS['sdkHeaders'][$name])) {
    throw new Exception("The request header " . $name . " not exists", 944811);
  }
  return $GLOBALS['sdkHeaders'][$name];
}

/**
 * Get token
 *
 * @return void
 */
function getToken() {
  if(!isset($GLOBALS['sdkHeaders']['token'])) {
    throw new Exception('The token is invalid', 987881);
  }
  return $GLOBALS['sdkHeaders']['token'];
}

function getClientVersion() {
  if(!isset($GLOBALS['sdkHeaders']['clientVersion'])) {
    throw new Exception('The client version is invalid', 987081);
  }
  return str_replace('.', '', $GLOBALS['sdkHeaders']['clientVersion']);
}

/**
 * Get currency from headers
 * @return string
 */
function getCurrency()
{
  $currency = getHeader('currency');
  if (!in_array($currency, ['cny', 'usd'])) {
    throw new Exception("The currency not supported", 391101);
  }
  return $currency;
}

/**
 * Get coin ticker
 *
 * @param [type] $coin
 * @param [type] $currency
 * @return void
 */
function getTicker($coin, $currency) {
  $req = ['symbol'=>$coin, 'currency'=>$currency];
  $res = json_decode((new BitKeepOpenSDK($GLOBALS['app_conf']['bitKeepSdk']))->getTicker($req), true);
  if(isset($res['status']) and $res['status']=='0' and isset($res['data']['all'])) {
    return $res['data']['all'];
  }
  return 0;
}

/**
 * 获取XX对美元汇率
 * @param string $cur 当前货币名称
 */
function getExRate($cur)
{
  $cur = strtoupper($cur);
  if (!isset($GLOBALS['app_conf'][$cur . '_exchange'])) {
    $GLOBALS['app_conf'][$cur . '_exchange'] = getExRateFromRedis($cur);
    $GLOBALS['app_conf'][$cur . '_exchange_time'] = time() + (60 * 60 * 12);
  }

  if (isset($GLOBALS['app_conf'][$cur . '_exchange_time']) and $GLOBALS['app_conf'][$cur . '_exchange_time'] < time()) {
    $GLOBALS['app_conf'][$cur . '_exchange'] = getExRateFromRedis($cur);
    $GLOBALS['app_conf'][$cur . '_exchange_time'] = time() + (60 * 60 * 12);
  }

  if ($cur == 'CNY') {
    $exchange = $GLOBALS['app_conf'][$cur . '_exchange'];
    return !is_numeric($exchange) ? 6.4 : $exchange;
  }
  return 1;
}

function getExRateFromRedis($cur)
{
  $redis = (new RedisStorage)->getRedis();
  return $redis->hget('EXCHANGE:RATE:USD', strtoupper($cur));
}

/**
 * 获取Icon
 */
function getIcon($coin)
{
  $coin = strtolower($coin);
  $schema = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https' : 'http';
  $port = '';
  if (isset($_SERVER['SERVER_PORT']) and !in_array($_SERVER['SERVER_PORT'], ['443', '80'])) {
    $port = $_SERVER['SERVER_PORT'];
  }
  $dest = APP_PATH . '/../web/icons/' . $coin[0] . '/' . $coin . '.png';
  if (!file_exists($dest)) {
    return $schema . '://' . $_SERVER['SERVER_NAME'] . $port . '/icons/eth_default.png';
  }
  return $schema . '://' . $_SERVER['SERVER_NAME'] . $port . '/icons/' . $coin[0] . '/' . $coin . '.png';
}

/**
 * 发送短信
 *
 * @param [type] $phone
 * @param [type] $text
 * @param [type] $cc
 * @return void
 */
function sendSMS($text, $phone, $cc='86') {
  if($phone=='') {
    return;
  }
  $url = $GLOBALS['app_conf']['services']['sms_text']; // TODO 国内自定义短信模板需要申请
  $data = ['countryCode'=>$cc, 'phone'=>$phone, 'text'=>$text];
  return cURL_request_JSON($url, $data, 'POST');
}

/**
 * 验证短信
 *
 * @param [type] $phone
 * @param [type] $text
 * @param [type] $cc
 * @return void
 */
function validateSMS($code, $phone, $cc='86') {
  if($phone=='') {
    return;
  }
  $url = $GLOBALS['app_conf']['services']['sms_verify'];
  $data = ['phone'=>$phone, 'code'=>$code];
  return cURL_request_JSON($url, $data, 'POST');
}

/**
 * 发送邮件提醒
 *
 * @param [type] $phone
 * @param [type] $text
 * @return void
 */
function sendMail($userId, $title, $content) {
  $postData = [
    'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
    'userId' => $userId,
  ];

  $config = $GLOBALS['app_conf']['bitKeepSdk'];

  $result = json_decode((new BitKeepOpenSDK($config))->accountInfo($postData), true);
  if (isset($result['data'])) {
    $mail = $result['data']['email'];
  }
  $url = 'http://127.0.0.1:50131/mail/send';
  $data = ['mail'=> $mail,'title'=>$title, 'content'=>$content];
  return cURL_request_JSON($url, $data, 'POST', getHeaders());
}

/**
 * Request sdk
 *
 * @param [type] $method
 * @param [type] $params
 * @return void
 */
function requestSDK($method, $params, $throw=true) {
  $res = json_decode((new BitKeepOpenSDK($GLOBALS['app_conf']['bitKeepSdk']))->$method($params), true);
  if(isset($res['status']) and $res['status']=='0' and isset($res['data'])) {
      return $res['data'];
  }
  if($throw===true) {
    throw new Exception($res['msg'], $res['status']);
  }
  return $res;
}

/**
 * @return array
 * @throws Exception
 */
function getHeaders()
{
  return  [
    'language:all',
    'currency:cny',
    'token:' . (isset($_SERVER['HTTP_TOKEN']) ? $_SERVER['HTTP_TOKEN'] : '')
  ];
}

/**
 * 网络抛出异常
 * @param int $code
 * @param $message
 * @throws Exception
 */
function NetException($code = 9999, $message = '')
{
  $exceptions = [
    '601' => '参数异常',

    //        '701'=>'矿机入库失败',

    '801' => '矿机入库失败',
    '802' => '矿机冻结失败',
    '803' => '可划转矿机不足',

    '901' => '创建商品失败',
    '902' => '此商品不存在',
    '903' => '商品数量不足',

    '904' => '矿机购买失败',
    '905' => '商品已售罄',
    '906' => '发布委托订单失败',
    '907' => '此委托订单失效',
    '908' => '创建订单失败',

    '1001' => '用户余额不足',

    '9999' => '服务器不支持此请求'
  ];
  if (empty($message)) {
    $message = $exceptions[$code];
  }

  throw new Exception($message, $code);

}
