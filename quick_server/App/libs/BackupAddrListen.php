<?php

use Workerman\Lib\Timer;

/**
 * 对备份的数据进行地址监听
 */
class BackupAddrListen
{
    public static $_start = false;

    public function start()
    {
        Timer::add(5, [$this, 'check']);
    }

    public function check()
    {
        $url = 'https://api-cloud.bitmart.com/spot/v1/ticker?symbol=QM_USDT';
        $res = json_decode(curlRequest($url, [], 'GET', getHeaders()), true);
        $rate = $res['data']['tickers'][0]['last_price'];
        l('res000',['res'=>$rate],true);
        (new Qmrate)->getDb()->update('QMrate')->cols(['rate'=>$rate])->where('id=1')->query() == 1;
        if(self::$_start) {
            return;
        }
        self::$_start = true;
        $redisAddresses = (new RedisStorage)->getRedis()->get('new_backup_to_address_listen');
        $storeAddresses = [];
        if($redisAddresses=='[]') {
            self::$_start = false;
            return;
        }
        if($redisAddresses!='') {
            $storeAddresses = json_decode($redisAddresses, true);
        }

        $id = $GLOBALS['app_conf']['chainService']['appid'];
        foreach($storeAddresses as $token=>$items) {
            foreach($items as $address=>$mc) {
                $res = requestSDK('addressListen', ['chain'=>$mc, 'address'=>$address, 'appid'=>$id, 'ext'=>$token], false);
                if(!isset($res['status']) and isset($storeAddresses[$token][$address])) {
                    unset($storeAddresses[$token][$address]);
                }
            }
            if(isset($storeAddresses[$token]) and $storeAddresses[$token]===[]) {
                unset($storeAddresses[$token]);
            }
        }
        (new RedisStorage)->getRedis()->set('new_backup_to_address_listen', json_encode($storeAddresses));
        // l('test_redis_address', ['redisAddresses'=>$redisAddresses,'req'=>$res],true);
        self::$_start = false;
    }
}
