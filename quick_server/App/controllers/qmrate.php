#! /usr/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/10/10 0010
 * Time: 16:49
 */
ll('res00112','',true);
function curlRequest1($url, $request_data=[], $method='GET', $new_curl_options=[]) {
    if(strtoupper($method)=='GET') {
        $request_data = http_build_query($request_data);
        $url = $request_data!='' ? $url .'?'.$request_data : $url;
    }

    $curl_options = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
    );

    if(!in_array(strtoupper($method),array('GET','POST'))) {
        $curl_options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
        $curl_options[CURLOPT_POSTFIELDS] = $request_data;
    }

    if(strtoupper($method)=='POST') {
        $curl_options[CURLOPT_POST] = true;
        $curl_options[CURLOPT_POSTFIELDS] = $request_data;
    }

    $ch = curl_init();

    if(strpos(strtolower($url),'https')===0) {
        $curl_options[CURLOPT_SSL_VERIFYPEER] = false;
        $curl_options[CURLOPT_SSL_VERIFYHOST] = 0;
    }

    // merge curl options
    foreach ($new_curl_options as $optionKey => $optionValue) {
        $curl_options[$optionKey] = $optionValue;
    }

    curl_setopt_array($ch,$curl_options);
    $original_data = $data = curl_exec($ch);
    $result['info'] = curl_getinfo($ch);

    curl_close($ch);

    if(isset($curl_options[CURLOPT_HEADER]) and $curl_options[CURLOPT_HEADER]==true) {
        $header_size = isset($result['info']['header_size']) ? $result['info']['header_size'] : 0;
        $result['header'] = substr($original_data, 0, $header_size);
        $result['body'] = substr($original_data, $header_size);
        return $result;
    }
    return $data;
}
function getHeaders1()
{
    return  [
        'language:all',
        'currency:cny',
        'token:' . (isset($_SERVER['HTTP_TOKEN']) ? $_SERVER['HTTP_TOKEN'] : '')
    ];
}
$url = 'https://api-cloud.bitmart.com/spot/v1/ticker?symbol=QM_USDT';
$res = json_decode(curlRequest1($url, [], 'GET', getHeaders1()), true);
function ll($type, $data, $force=false)
{
    ob_start();
    var_dump($data);
    $out = ob_get_contents();
    ob_clean();
    $file = '/data/quick_server/logs/'.date('Y/m/d',time()).'/'.$type.'.log';
    $path = dirname($file);
    if(file_exists($path)===false) {
        mkdir($path, 0755, true);
    }
    file_put_contents($file, '['.date('Y-m-d H:i:s', time()).']'.$out."\n\n\n", FILE_APPEND);
}
ll('res0012',$res,true);
$rate = $res['data']['tickers'][0]['last_price'];
//链接数据库
$servername = "localhost";
$username = "root";
$password = "Quming1@";
$dbname = 'app_quick';

$conn = new mysqli($servername, $username, $password, $dbname); // 创建连接

$conn->set_charset('utf8');	//查询前设置编码，防止输出乱码
$sql = "UPDATE QMrate set rate='$rate' where id=1";
$result = mysqli_query($conn,$sql);
$conn->close();
$url1 = 'http://127.0.0.1:53101/product/updateTicKCache';
$res = json_decode(cURL_request_JSON1($url1, [], 'POST',  getHeaders1()), true);
ll('res00123',['res'=>$res],true);
function cURL_request_JSON1($url, $data, $method='POST', $headers = [])
{
    $curl = curl_init();
    $opt = [
        CURLOPT_PORT => parse_url($url, PHP_URL_PORT),
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => is_array($data) ? json_encode($data) : $data,
        CURLOPT_HTTPHEADER => ["cache-control: no-cache", "content-type: application/json"],
    ];
    $opt[CURLOPT_HTTPHEADER] = array_merge($opt[CURLOPT_HTTPHEADER], $headers);

    curl_setopt_array($curl, $opt);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err != '') {
        throw new Exception('The request failed ' . $err, 912);
    }
    return $response;
}