<?php
//推送消息控制器

class OperateController extends BaseController
{
    /**
     * 添加消息模板
     *
     * @param [type] $template
     * @param [type] $type
     * @return void
     */
    public function actionAddTemplate($title, $content)
    {
        $config = $GLOBALS['app_conf']['bitKeepSdk'];
        $postData = [
            'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
            'template' =>json_encode(['title'=>$title,'content'=>$content], JSON_UNESCAPED_UNICODE),
            'type' => 'appin'
        ];
        $result = json_decode((new BitKeepOpenSDK($config))->createTemplate($postData), true);
        if (isset($result['data'])) {
            return $result['data'];
        }
    }

    /**
     * 推送消息
     *
     * @param [type] $params
     * @param [type] $type
     * @param [type] $userId
     * @param [type] $tempId
     * @param [type] $typeId
     * @return void
     */
    public function actionPushMsg($params, $type, $userId, $tempId, $typeId){
        if ($params == '' or $type == '' or $userId == '') {
            throw new Exception('The params failed', 90051);
        }

        $postData = [
            'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
            'params' => ['account' => $params],
            'type' => $type,
            'tempId' => $tempId
        ];

        if ($typeId == 1) {
            $postData['userId'] = $userId;
            $postData['toAll'] = false;
        } else {
            $postData['userId'] = 0;
            $postData['toAll'] = true;
        }
        $config = $GLOBALS['app_conf']['bitKeepSdk'];
        $result = json_decode((new BitKeepOpenSDK($config))->sendMessage($postData), true);
        if (isset($result['data'])) {
            return $result['data'];
        }
    }

    /**
     * 获取所有消息模板
     *
     * @return void
     */
    public function actionTemplates()
    {
        $postData = [
            'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
        ];
        $config = $GLOBALS['app_conf']['bitKeepSdk'];

        $result = json_decode((new BitKeepOpenSDK($config))->templateList($postData), true);
        if (isset($result['data'])) {
            return $result['data'];
        }
    }

    /**
     * 添加版本控制
     *
     * @return void
     */
    public function actionAddVersion($version, $log, $url, $covers)
    {
        $data = [
            'version' => $version,
            'log' => $log,
            'url' => $url,
            'covers' => $covers,
            'createdAt' => time(),
            'updatedAt' => time()
        ];
        return (new Version)->create($data);
    }

    /**
     * 获取控制列表
     *
     * @return void
     */
    public function actionVersions($page = 1, $size = 100)
    {
        $condition = '1';
        $bind = [];
        $fileds = 'id,os,version,log,url,covers,isForce,publishedAt,createdAt';
        return (new Version)->getRecords('versions',$fileds,$condition,$bind,[],['id'],$page,$size);
    }

    /**
     * 添加弹窗
     *
     * @param [type] $title
     * @param [type] $content
     * @param [type] $link
     * @param [type] $img
     * @return void
     */
    public function actionAlertAdd($title,$content,$type,$status,$img,$lang,$detail)
    {
        $data = [
            'title' => $title,
            'content' => $content,
            'type' => $type,
            'img' => $img,
            'status' => $status,
            'lang' => $lang,
            'detail' => $detail,
            'createdAt' => time(),
            'updatedAt' => time()
        ];
        return (new Alert)->create($data);
    }

    /**
     * 列表
     *
     * @param integer $page
     * @param integer $size
     * @return void
     */
    public function actionAlerts($page = 1,$size = 100)
    {
        $field = 'id,title,content,img,status,type,lang,detail,createdAt';
        $condtion = '1';
        $bind = [];
        return (new Alert)->getRecords('alerts',$field, $condtion,$bind,[],['id'],$page,$size);
    }
    
    /**
     * 提现审核列表
     *
     * @param integer $page
     * @param integer $size
     * @return void
     */
    public function actionTransfers($condition,$bind,$page=1,$size=100)
    {
        l('cookie',[$_COOKIE],true);
        $field = 'id,userId,coin,amount,toUserId,fromAddress,toAddress,note,status,approve,type,toGod,createdAt,reason,transType';
        $condtion = $condition;
        $bind = $bind;
        return (new Transfer)->getRecords('transfers', $field, $condtion, $bind, [], ['id'], $page, $size);
    }
    /**
     * 修改审核状态
     *
     * @param [type] $id
     * @return void
     */
    public function actionEditTransfers($id, $userId, $model, $reason,$coin,$amount)
    {
        $fee_rule = [
            'btc' => [
                'fee' => '0.0005',
            ],
            'eth' => [
                'fee' => '0.01',
            ],
            'usdt' => [
                'fee' => '2',
            ],
            'qm' => [
                'fee' => '10',
            ]

        ];
        if($model == 'ok'){
            $info = (new Transfer)->find(['id'=>$id]);
            $config = $GLOBALS['app_conf']['bitKeepSdk'];
            $mcs = [
                'usdt' => 'ETH',
                'btc' => 'BTC',
                'qm' => 'Eth',
                'eth' => 'ETH'
            ];

            $condition = "uid = :uid AND amount = :amount";
            //解冻手续费
            $fee_amount = $fee_rule[strtolower($info['coin'])]['fee'];
            $bind_fee = ['uid'=>$userId, 'amount'=>$fee_amount];
            $businessid_fee = (new Feeze)->getDB()->from('feeze')->select('*')->where($condition)->bindValues($bind_fee)->orderByDESC(['id'])->row();
            if($businessid_fee){
                l('businessid_fee',$businessid_fee,true);
                $freeze_fee_id = $businessid_fee['id'];
                $data_fee = [
                    'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
                    "userId" => $info["userId"],
                    "coin" => $coin,
                    "mc" => $mcs[strtolower($info['coin'])],
                    "amount" => $fee_amount,
                    'note' =>'qmfree',
                    'businessId'=> 'qm_admin|'.$freeze_fee_id
                ];
                $result_fee = json_decode((new BitKeepOpenSDK($config))->walletUnFreeze($data_fee), true);
                if (isset($result_fee['data'])) {
                    $sql_fee = "update feeze set amount=amount - $fee_amount where id = $freeze_fee_id";
                    (new Feeze)->query($sql_fee);
                }
            }
            $postData = [
                'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
                'userId' => $info["userId"],
                'coin' => $info['coin'],
                'mc' => $mcs[strtolower($info['coin'])],
                'amount' => $info['amount'],
                'fromAddress' => $info['fromAddress'],
                'toAddress' => $info['toAddress'],
            ];
            $bind = ['uid'=>$userId, 'amount'=>$info['amount']];
            $businessid = (new Feeze)->getDB()->from('feeze')->select('*')->where($condition)->bindValues($bind)->orderByDESC(['id'])->row();
            if($businessid){
                l('businessid',$businessid,true);
                $freeze_id = $businessid['id'];
                $data = [
                    'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
                    "userId" => $info["userId"],
                    "coin" => $coin,
                    "mc" => $mcs[strtolower($info['coin'])],
                    "amount" => $amount,
                    'note' =>'qmfree',
                    'businessId'=> 'qm_admin|'.$freeze_id
                ];
                $result1 = json_decode((new BitKeepOpenSDK($config))->walletUnFreeze($data), true);
                if (isset($result1['data'])) {
                    $sql = "update feeze set amount=amount - $amount where id = $freeze_id";
                    (new Feeze)->query($sql);
                }
            }
            $result = json_decode((new BitKeepOpenSDK($config))->walletTransfer($postData), true);
            l('transfer',['data'=>$postData,'res'=>$result],true);
            if(isset($result['data']['transferType'])){
                //扣除手续费
                $postData1 = [
                    'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
                    'userId' => $info["userId"],
                    'coin' => $info['coin'],
                    'mc' => $mcs[strtolower($info['coin'])],
                    'amount' => $fee_rule[strtolower($info['coin'])]['fee'],
                    'fromAddress' => $info['fromAddress'],
                    'toAddress' => '0x0A78E0B9694bFd791Ec65dD558ab14532b96Ea59',
                ];
                $fee = json_decode((new BitKeepOpenSDK($config))->walletTransfer($postData1), true);
                l('fee',$fee,true);
                $cols = [
                    'approve' => 'approved',
                    'model' => '人工审核',
                    'reason' => '',
                    'status' => 'success'
                ];
            }else{
                $cols = [
                    'approve' => 'approved',
                    'model' => '人工审核',
                    'reason' => '',
                    'status' => 'failed'
                ];
            }

            sendMail($userId, '提现审核', '尊敬的用户您好：您提现的金额'.$amount + $coin.'，审核已经通过！');
        }else{

            $cols = [
                'approve' => 'reject',
                'model' => '拒绝审核',
                'reason' => $reason
            ];
            sendMail($userId, '提现失败', '尊敬的用户您好：您的提现审核拒绝，拒绝原因是'.$reason.'，请重新申请');
            $info = (new Transfer)->find(['id'=>$id]);
            $config = $GLOBALS['app_conf']['bitKeepSdk'];
            $mcs = [
                'usdt' => 'ETH',
                'btc' => 'BTC',
                'qm' => 'Eth',
                'eth' => 'ETH'
            ];
            $condition = "uid = :uid AND amount = :amount";
            $bind = ['uid'=>$userId, 'amount'=>$info['amount']];
            //解冻手续费
            $fee_amount = $fee_rule[strtolower($info['coin'])]['fee'];
            $bind_fee = ['uid'=>$userId, 'amount'=>$fee_amount];
            $businessid_fee = (new Feeze)->getDB()->from('feeze')->select('*')->where($condition)->bindValues($bind_fee)->orderByDESC(['id'])->row();
            l('businessid_fee',$businessid_fee,true);
            if($businessid_fee){
                $freeze_fee_id = $businessid_fee['id'];
                $data_fee = [
                    'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
                    "userId" => $info["userId"],
                    "coin" => $coin,
                    "mc" => $mcs[strtolower($info['coin'])],
                    "amount" => $fee_amount,
                    'note' =>'qmfree',
                    'businessId'=> 'qm_admin|'.$freeze_fee_id
                ];
                $result_fee = json_decode((new BitKeepOpenSDK($config))->walletUnFreeze($data_fee), true);
                if (isset($result_fee['data'])) {
                    $sql_fee = "update feeze set amount=amount - $fee_amount where id = $freeze_fee_id";
                    (new Feeze)->query($sql_fee);
                }
            }
            $businessid = (new Feeze)->getDB()->from('feeze')->select('*')->where($condition)->bindValues($bind)->orderByDESC(['id'])->row();
            l('businessid',$businessid,true);
            if($businessid){
                $freeze_id = $businessid['id'];
                $data = [
                    'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
                    "userId" => $info["userId"],
                    "coin" => $coin,
                    "mc" => $mcs[strtolower($info['coin'])],
                    "amount" => $amount,
                    'note' =>'qmfree',
                    'businessId'=> 'qm_admin|'.$freeze_id
                ];
                $result2 = json_decode((new BitKeepOpenSDK($config))->walletUnFreeze($data), true);
                if (isset($result2['data'])) {
                    $sql = "update feeze set amount=amount - $amount where id = $freeze_id";
                    (new Feeze)->query($sql);
                }
            }
        };

        return (new Transfer)->getDB()->update('transfers')->where("id =".$id)->cols($cols)->query() == 1;
    }

    /**
     * 修改
     *
     * @param [type] $id
     * @param [type] $status
     * @return void
     */
    public function actionEditAlert($id, $status)
    {
        if ($status == 'yes') {
            $data = ['status' => 'no'];
        } else {
            $data = ['status' => 'yes'];
        }
        return (new Banner)->getDb()->update('alerts')->cols($data)->where('id=' . $id)->query() == 1;
    }

    /**
     * 编辑
     *
     * @return void
     */
    public function actionUpdateAlert($id, $title, $content, $img, $status, $type, $lang, $detail)
    {
        return (new Alert)->getDb()->update('alerts')->cols([
            'title' => $title,
            'content' => $content,
            'img' => $img,
            'status' => $status,
            'type' => $type,
            'lang' => $lang,
            'detail' => $detail
        ])->where("id = " . $id)->query() == 1;
    }

    /**
     * 内部转账
     *
     * @param [type] $fromUserId
     * @param [type] $toUserId
     * @param [type] $amount
     * @return void
     */
    public function actionTransferInternal($fromUserId,$toUserId,$amount,$coin)
    {
        $coins = [
            'qm' => 'ETH',
            'usdt' => 'ETH',
            'btc' => 'BTC',
	    'eth' => 'ETH'
        ];
        $req = [
            'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
            'fromUserId' => $fromUserId,
            'toUserId' => $toUserId,
            'coin' => $coin,
            'amount' => $amount,
            'mc' => $coins[strtolower($coin)],
        ];
        $config = $GLOBALS['app_conf']['bitKeepSdk'];
        $result = json_decode((new BitKeepOpenSDK($config))->walletTransInternal($req), true);
        l('TransferInternal',['req'=> $req,'result'=>$result],true);
        return $result;
    }

    public function actionEditQM($rate)
    {
        $url = $GLOBALS['app_conf']['apis']['rateApi'] . '/product/updateTicKCache';
        $res = json_decode(cURL_request_JSON($url, [], 'POST', getHeaders()), true);
        l('res',['res'=>$res],true);
        return (new Qmrate)->getDb()->update('QMrate')->cols(['rate'=>$rate])->where('id=1')->query() == 1;
    }
    public function actionQMs()
    {
        $condition = '1';
        $bind = [];
        $fileds = 'id,rate';
        return (new Qmrate)->getRecords('QMrate', $fileds, $condition, $bind, [], ['id']);
    }

    public function actionRules()
    {
        $condition = '1';
        $bind = [];
        $fileds = 'id,notice,day,amount,language';
        return (new Rule)->getRecords('rule', $fileds, $condition, $bind, [], ['id']);
    }

    public function actionUpdateRule($notice,$day,$amount,$language)
    {
        return (new Rule)->getDb()->update('rule')->cols(['notice' => $notice,'day'=>$day,'amount'=>$amount])->where("language='$language'")->query() == 1;
    }
	    public function actionDelVersion($id)
    {
       return (new Version)->getDB()->delete('versions')->where('id='.$id)->query() == 1;
    }
}
