<?php
class OrderController extends BaseController
{
    /**
     * 商城订单
     *
     * @return void
     */
    public function actionList($condition = '1',$bind = [],$page = 1,$size = 100)
    {
        $fileds = 'id,uid,product_id,count,type,pay_time,create_time,price,status';
        return (new Order)->getRecords('orders',$fileds,$condition,$bind,[],['id'],$page,$size);
    }

    /**
     * 锁仓抢购
     *
     * @param integer $page
     * @param integer $size
     * @return void
     */
    public function actionRobList($page = 1,$size = 100,$condition = '1',$bind = [])
    {
        $fileds = 'id,uid,amount,day,status,createdAt';
        return (new Freezes)->getRecords('freezes', $fileds, $condition, $bind, [], ['id'], $page, $size);
    }

    /**
     * 兑换
     *
     * @param integer $page
     * @param integer $size
     * @param string $condition
     * @param array $bind
     * @return void
     */
    public function actionExchages($page = 1,$size = 100,$condition = '1',$bind = [])
    {
        $fileds = 'id,uid,qm_count,usd_count,createdAt';
        return (new Exchane)->getRecords('exchanes',$fileds,$condition,$bind,[],['id'],$page,$size);
    }

    /**
     * 解冻
     *
     * @param [type] $roid
     * @return void
     */
    public function actionUnfezz($roid)
    {
        $info = (new Freezes)->getDB()->select('*')->from('freezes')->where('id= :id')->bindValues(array('id' => $roid))->row();
        if($info['status'] === 'no'){
            throw new Exception('该账单已是解冻状态', 95112);
        }
        if(isset($info['amount'])){
            $postData = [
                'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
                'userId' => $info['uid'],
                'coin' => 'QM',
                'mc' => 'ETH',
                'amount' => $info['amount'],
                'businessId' => $info['id'],
                'note'=>''
            ];
            $config = $GLOBALS['app_conf']['bitKeepSdk'];
            $result = json_decode((new BitKeepOpenSDK($config))->walletUnFreeze($postData), true);
            l('unfreeze', ['data' => $postData, 'res' => $result], true);
            if(isset($result['data']) and $result['data'] == true){
                return (new Rule)->getDb()->update('freezes')->cols(['status' => 'no',])->where("id = $roid")->query() == 1;
            }
            throw new Exception('解冻失败',95111);
        }
    }
}