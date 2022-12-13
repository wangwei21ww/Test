<?php
/**
  * 该类为 BitKeep 开放平台 SDK，用于交互第三方服务端与开放平台的交互
  * @author lijian<lijian@bitkeep.com>
  */

class BitKeepOpenSDK
{
    /**
      * The SDK configure
      * @var array
      */
    protected $config = [];

    /**
      * The API methods, that provided magic methods
      * @var array
      */
    protected $_methods = [
      'accounts' => ['api' => '/account/account/accounts', 'method' => 'POST'],
      'search' => ['api' => '/account/account/search', 'method' => 'POST'],
      'coins' => ['api' => '/app/coin/list', 'method' => 'POST'],
      'coinAdd' => ['api' => '/app/coin/add', 'method' => 'POST'],
      'profiles' => ['api' => '/account/account/profiles', 'method' => 'POST'],
      'profile' => ['api' => '/account/account/profile', 'method' => 'POST'],
      'updateKyc' => ['api' => '/account/account/update', 'method' => 'POST'],
      'accountToken' => ['api' => '/account/token/issue', 'method' => 'POST'],
      'createTemplate' => ['api' => '/message/template/create', 'method' => 'POST'],
      'templateList' => ['api' => '/message/template/list', 'method' => 'POST'],
      'sendMessage' => ['api' => '/message/message/add', 'method' => 'POST'],
      'notifies' => ['api' => '/message/message/list', 'method' => 'POST'],
      'walletList' => ['api' => '/wallet/wallet/list', 'method' => 'POST'],
      'createTemplate' => ['api' => '/message/template/create', 'method' => 'POST'],
      'notifies' => ['api' => '/message/message/list', 'method' => 'POST'],
      'sendMessage' => ['api' => '/message/message/add', 'method' => 'POST'],
      'walletTransfers' => ['api' => '/wallet/wallet/transfers', 'method' => 'POST'],
      'walletTransfer' => ['api' => '/wallet/wallet/transfer', 'method' => 'POST'],
      'walletTransInternal' => ['api' => '/wallet/wallet/transferInternal', 'method' => 'POST'], //内部划转
      'export' => ['api' => '/account/account/export', 'method' => 'POST'], //内部划转,
      'accountInfo' => ['api' => '/account/account/profile', 'method' => 'POST'],
      'addressInfo' => ['api' => '/wallet//wallet/addressInfo', 'method' => 'POST'],
	      'walletUnFreeze' => ['api' => '/wallet/wallet/unfreeze', 'method' => 'POST'],//解冻
      'walletFreeze' => ['api' => '/wallet/wallet/freeze', 'method' => 'POST'],//冻结
    ];

    /**
      * The SDK required header items
      * @var array
      */
    private $_headers = ['appId','appUserId','platform','language','currency'];

    /**
      * Construct method, set the configure argument for BitKeepOpenSDK
      * @param array $config
      */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
      * The magic method, expose api methods for developer
      * @param string $method
      * @param array $params
      * @return mixed
      */
    public function __call($method, array $params)
    {
      if(isset($this->_methods[$method])) {
        $params[1] = isset($params[1]) ? $params[1] : $this->_methods[$method]['api'];
        $params[2] = isset($params[2]) ? $params[2] : $this->_methods[$method]['method'];
        return call_user_func_array([$this, 'postData'], $params);
      }
      throw new Exception("The method not exists", 490011);
    }

    /**
      * Split headers from post data
      * @param array $headers
      * @return array
      */
    public function splitHeaders(array $data)
    {
        $headers = [];
        foreach ($this->_headers as $key => $item) {
          if(isset($data[$item])) {
            $headers[$item] = $data[$item];
          }
        }
        return $headers;
    }

    /**
      * Post data to API service
      * @param string $api The API url must be starting with a slash
      * @param array $data The Post data must be an array
      * @param string $method The method defaults to POST
      */
    public function postData(array $data, $api, $method='POST')
    {
        $defaultHeaders = [
          'appId'=>$this->config['appId'],
          'apiKey'=>$this->config['apiKey'],
          'platform'=>$this->config['platform'],
          'currency'=>$this->config['currency'],
          'language'=>$this->config['language'],
          'apiVersion'=>$this->config['apiVersion']
        ];
        $headers = $this->splitHeaders($data);
        $headers = array_merge($defaultHeaders, $headers);

        $data['nonce'] = $this->makeNonce();
        $data['sign'] = $this->signData($data);
        $api = $this->getAPIHost().$api;
        $res = $this->cURLRequestJSON($api, $data, $method, $headers);
        l('sdk_request_log', ['url'=>$api, 'headers'=>$headers, 'req'=>$data, 'res'=>$res], true);
        return $res;
    }

    /**
      * Generate nonce for sign
      * @return string
      */
    public function makeNonce()
    {
        $nonce = str_replace('.', '', microtime(true));
        if(strlen($nonce)>=13) {
          $nonce = substr($nonce, 0, 13);
        }else{
          $nonce = str_pad($nonce, 13, '0');
        }
        return $nonce;
    }

    /**
      * make an request with curl
      * @param string $url
      * @param array $data
      * @param string $method POST or GET
      */
    public function cURLRequestJSON($url, $data, $method='POST', $headers=[])
    {
        $defaultHeaders = ["cache-control: no-cache","content-type: application/json"];
        $headers = $this->processHeaders($headers);
        $headers = array_merge($defaultHeaders, $headers);
        
        $curl = curl_init();
        curl_setopt_array($curl, [
          CURLOPT_PORT => parse_url($url, PHP_URL_PORT),
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_SSL_VERIFYPEER=>true,
          CURLOPT_CAINFO=>$this->config['ca_cert'],
          CURLOPT_SSL_VERIFYHOST=>2,
CURLOPT_SSL_VERIFYPEER => false,
CURLOPT_SSL_VERIFYHOST => 0,
          CURLOPT_CUSTOMREQUEST => $method,
          CURLOPT_POSTFIELDS => is_array($data) ? json_encode($data) : $data,
          CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if($err!=''){
            throw new Exception('The request failed '.$err, 912);
        }
        return $response;
    }

    /**
      * Get host name based on different env
      * @return string
      */
    public function getAPIHost()
    {
        $env = isset($this->config['running_mode']) ? $this->config['running_mode'] : 'prod';
        return $this->config[$env.'_url'];
    }

    /**
      * convert header items to string item
      * @param array $headers
      * @return array
      */
    public function processHeaders($headers)
    {
        $container = [];
        foreach ($headers as $key => $item) {
          $container[] = $key.': '.$item;
        }
        return $container;
    }

    /**
      * Generate a sign with post data
      * @param array $data
      * @return string
      */
    public function signData($data)
    {
        $data['apiSecret'] = $this->config['apiSecret'];
        ksort($data);
        return hash('sha256', strtolower(http_build_query($data)));
    }

    /**
      * Validate the sign is correct or not
      * @param array $data
      * @return string $sign
      * @return boolean
      */
    public function validateSign($data, $sign)
    {
        unset($data['sign']);
        return $this->signData($data)==strtolower($sign);
    }
}
