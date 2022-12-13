<?php
/**
 * 资产类
 */
class TransferController extends BaseController
{
    /**
     * 转账记录
     *
     * @param [type] $userId
     * @param integer $page
     * @param integer $size
     * @return void
     */
    public function actionList($userId,$page = 1,$size = 100)
    {
        $data = [
            'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
            "userId" => $userId,
            // "coin" => "",
            "type" => "all",
            "transType" => "all",
            "page" => $page,
            "size" => $size
        ];
        $config = $GLOBALS['app_conf']['bitKeepSdk'];
        $result = json_decode((new BitKeepOpenSDK($config))->walletTransfers($data), true);
        if (isset($result['data'])) {
            l('list',$result,true);
            return $result['data'];
        }
    }

    //冻结
    public function actionFree($userId,$coin,$amount){
        $coins = [
            'btc'=>'btc',
            'usdt'=>'eth',
            'qm'=>'eth'
        ];
        $insertRe = (new Feeze)->create([
            'uid'=>$userId,
            'coin'=>$coin,
            'amount'=>$amount,
            'createdAt'=>time()
        ]);
        $data = [
            'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
            "userId" => $userId,
            "coin" => $coin,
            "mc" => $coins[$coin],
            "amount" => $amount,
            'businessId'=> 'qm_admin|'.$insertRe['id'],
            'note' =>'qmfree'
        ];
        $config = $GLOBALS['app_conf']['bitKeepSdk'];
        $result = json_decode((new BitKeepOpenSDK($config))->walletFreeze($data), true);
        if (isset($result['data'])) {
            return $result['data'];
        }
    }

    public function actionGetfree($uid,$page=1,$pageSize=100){
        $condition = 'uid = :uid';
        $bind = ['uid'=>$uid];
        $filed = '*';
        return (new Feeze)->getRecords('feeze',$filed,$condition,$bind,[],['id'],$page,$pageSize);
    }

    //解冻
    public function actionUnFree($id,$userId,$coin,$amount){
        $coins = [
            'btc'=>'btc',
            'usdt'=>'eth',
            'qm'=>'eth'
        ];
        $data = [
            'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
            "userId" => $userId,
            "coin" => $coin,
            "mc" => $coins[$coin],
            "amount" => $amount,
            'note' =>'qmfree',
	    'businessId'=> 'qm_admin|'.$id
        ];
        $config = $GLOBALS['app_conf']['bitKeepSdk'];
        $result = json_decode((new BitKeepOpenSDK($config))->walletUnFreeze($data), true);
        if (isset($result['data'])) {
            $sql = "update feeze set amount=amount - $amount where id = $id";
            (new Feeze)->query($sql);
            return $result['data'];
        }
    }
    
    public function actionTestFree(){
        $data = [
            'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
            "userId" => 34,
            "coin" => 'btc',
            "mc" => 'btc',
            "amount" => 0.09883755,
            'businessId'=> '2020-07',
            'note' =>'解冻'
        ];
        $config = $GLOBALS['app_conf']['bitKeepSdk'];
        $result = json_decode((new BitKeepOpenSDK($config))->walletUnFreeze($data), true);
        return $result;
    }
}
