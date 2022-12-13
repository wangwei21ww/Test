<?php

class IncomeController extends BaseController
{
    /**
     * 添加收益
     *
     * @param [type] $totalAmount
     * @param [type] $incomeDate
     * @param [type] $productId
     * @return void
     */
    public function actionAdd($totalAmount, $incomeDate, $productId)
    {
        $data = [
            'totalAmount' => $totalAmount,
            'incomeDate' => $incomeDate,
            'productId' => $productId,
        ];
        $url = $GLOBALS['app_conf']['apis']['api'] . '/income/add';
        return json_decode(cURL_request_JSON($url, $data, 'POST', getHeaders()), true);
    }

    /**
     * 收益列表
     *
     * @return void
     */
    public function actionList($condition = '1',$bind = [],$page = 1,$size = 50)
    {
        $fileds = 'id,userId,amount,power,manage,insurance,productId,sendStatus,incomeDate,createdAt';
        $data = (new Income)->getRecords('incomes',$fileds,$condition,$bind,[],['id'],$page,$size);
        foreach($data['items'] as $key=>$value){
            $data['items'][$key]['createdAt'] = date('Y-m-d',$value['createdAt']);
        }
        return $data;
    }

    /**
     * 获取可以派发收益的产品列表
     *
     * @return void
     */
    public function actionGetProducts()
    {
	$condition = 'Trusteeship_at != :Trusteeship_at';
        $bind = ['Trusteeship_at'=>0];
        return (new Product)->getDB()->from('products')->select('id,name,type')
            ->where($condition)->bindValues($bind)->query();
    }
    

       public function actionGetProductsI()
    {
        $condition = '1=1';
        $bind = [];
        return (new Product)->getDB()->from('products')->select('id,name,type')
            ->where($condition)->bindValues($bind)->query();
    }    




        /**
     * 激励池充值记录
     *
     * @return void
     */
    public function actionPools($page = 1, $size = 50)
    {
        $url = $GLOBALS['app_conf']['apis']['api'] . '/income/Pools';
        return json_decode(cURL_request_JSON($url, ['page' => $page,'size'=>$size], 'POST', getHeaders()), true);
    }

    /**
     * 产生总收益
     *
     * @param string $date
     * @return void
     */
    public function actionTotalIncome($date='')
    {
        if($date == ''){
            $date = date('Y-m-d',time());
        }
        $url = $GLOBALS['app_conf']['apis']['api'] . '/Stored/ProfitAll';
        return json_decode(cURL_request_JSON($url, ['date' => $date,'coin'=>'qm'], 'POST', getHeaders()), true);
    }

    /**
     * 推广人收益
     *
     * @param [type] $userId
     * @param [type] $start
     * @param [type] $end
     * @return void
     */
    public function actionPusherIncome($userId,$start,$end)
    {
        $data  = [
            'userId'=>$userId,
            'start'=> $start,
            'end'=> $end,
        ];
        $url = $GLOBALS['app_conf']['apis']['rateApi'] . '/profit/pusher';
        return json_decode(cURL_request_JSON($url, $data, 'POST', getHeaders()), true);
    }
}
