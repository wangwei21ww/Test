<?php

class ProductController extends BaseController
{
    /**
     * 创建商品
     *
     * @param [type] $type
     * @param [type] $price
     * @param [type] $image
     * @param [type] $coin
     * @param [type] $name
     * @param [type] $quantity
     * @param [type] $min_count
     * @param [type] $max_count
     * @param [type] $status
     * @param [type] $income
     * @param [type] $hashRate
     * @param [type] $period
     * @param [type] $details
     * @return void
     */
    public function actionAdd($name,$price,$coin,$quantity,$min_count,$max_count,
        $image,$hashRate,$period,$status,$income,$type,$is_hot,$powerConsume = '',
        $power = '',$manage ='',$insurance='',$tag='',$sort=0,$intro='',$cost='',$notice='',$ko,$cny,$ja,$lang)
    {
        //商品信息
        $data = [
            'name' => $name,
            'price' => $price,
            'coin' => $coin,
            'coin' => $coin,
            'quantity' => $quantity,
            'min_count' => $min_count,
            'max_count' => $max_count,
            'image' => $image,
            'hashRate' => $hashRate,
            'period' => $period,
            'status' => $status,
            'income' => $income,
            'type' => $type,
            'is_hot' => $is_hot,
            'powerConsume' => $powerConsume,
            'power' => $power,
            'insurance' => $insurance,
            'manage' => $manage,
            'tag' => $tag,
            'sort' => $sort,
            'intro' => $intro,
            'cost' => $cost,
            'notice' => $notice,
            'ko' => $ko,
            'ja' => $ja,
            'cny' => $cny,
            'lang' => $lang,
            'createdAt' => time(),
        ];
        return (new Product)->create($data);
    }

    /**
     * 列表
     *
     * @param string $type
     * @param string $coin
     * @param string $sort
     * @param integer $page
     * @param integer $size
     * @return void
     */
    public function actionList($type = '', $page = 1, $size = 30)
    {
        $condition = 'type = :type';
        $bind = ['type'=>$type];
        $fileds = "*";
        return (new Product)->getRecords('products',$fileds,$condition,$bind,[],['sort'],$page,$size);
    }

    /**
     * 详情
     *
     * @param [type] $id
     * @return void
     */
    public function actionDetail($id)
    {
        $url = $GLOBALS['app_conf']['apis']['api'] . '/Product/Detail';
        return json_decode(cURL_request_JSON($url, ['id'=>$id], 'POST', getHeaders()), true);
    }

    /**
     * 更新产品
     *
     * @param [type] $data
     * @return void
     */
    public function actionEdit($data)
    {
        if(isset($data['id'])){
            $id = $data['id'];
        }
        $profiles['data'] = json_encode($data['data']);
        unset($data['id']);
        unset($data['data']);
        unset($data['_index']);
        unset($data['_rowKey']);
        return (new Product)->getDb()->update('products')->cols($data)->where('id='.$id)->query() == 1;
    }

    /**
     * 修改产品状态
     *
     * @param [type] $data
     * @return void
     */
    public function actionUpdateProduct($data)
    {
        if (isset($data['id'])) {
            $id = $data['id'];
        }
        unset($data['id']);
        if(isset($data['Trusteeship_at']) and $data['Trusteeship_at'] == 'yes'){
            $data['Trusteeship_at'] = time();
        }
        return (new Product)->getDb()->update('products')->cols($data)->where('id=' . $id)->query() == 1;
    }

    /**
     * 添加滚动新闻
     *
     * @param [type] $title
     * @param [type] $link
     * @return void
     */
    public function actionAddTitle($title,$status,$lang)
    {
        return (new Title)->create(['title'=>$title, 'status'=> $status,'lang'=>$lang,'createdAt'=>time()]);
    }

    /**
     * 滚动列表
     *
     * @param integer $page
     * @param integer $size
     * @return void
     */
    public function actionTitleList($page=1,$size=100)
    {
        $fileds = 'id,title,status,lang';
        $condition = '1';
        $bind = [];
        return (new Title)->getRecords('shop_news',$fileds,$condition,$bind,[],['id'],$page,$size);
    }

    /**
     * 修改
     *
     * @param [type] $id
     * @param [type] $status
     * @return void
     */
    public function actionEditTitle($id, $status)
    {
        if ($status == 'yes') {
            $data = ['status' => 'no'];
        } else {
            $data = ['status' => 'yes'];
        }
        return (new Title)->getDb()->update('shop_news')->cols($data)->where('id=' . $id)->query() == 1;
    }

    public function actionDeleteTitle($id)
    {
        return (new Title)->getDb()->delete('shop_news')->where('id=' . $id)->query() == 1;
    }
}