<?php
class BannerController extends BaseController
{
    /**
     * 添加图片
     *
     * @param [type] $status
     * @param [type] $img_url
     * @return void
     */
    public function actionAdd($status,$img_url,$newsId,$lang)
    {
        $this->assertParam('status',$status);
        $this->assertParam('img_url', $img_url);
        $data = [
            'status' => $status,
            'img_url' => $img_url,
            'newsId' => $newsId,
            'lang' => $lang,
            'createdAt' => time(),
        ];
        return (new Banner)->create($data);
    }

    /**
     * 列表
     *
     * @param [type] $page
     * @param [type] $pageSize
     * @return void
     */
    public function actionList($page = 1,$pageSize = 50)
    {
        $condtion = '1';
        $bind = [];
        $field = 'id,img_url,status,newsId,lang,createdAt';
        return (new Banner)->getRecords('banners',$field,$condtion,$bind,[],['id'],$page,$pageSize);
    }

    /**
     * 删除
     *
     * @param [type] $id
     * @return void
     */
    public function actionDel($id)
    {
        return (new Banner)->getDb()->delete('banners')->where('id='.$id)->query() == 1;
    }

    /**
     * 修改
     *
     * @param [type] $id
     * @param [type] $status
     * @return void
     */
    public function actionEdit($id,$status)
    {
        if($status == 'yes'){
            $data = ['status' => 'no'];
        }else{
            $data = ['status' => 'yes'];
        }
        return (new Banner)->getDb()->update('banners')->cols($data)->where('id='.$id)->query() == 1;
    }

    /**
     * 添加商城图片
     *
     * @param [type] $data
     * @return void
     */
    public function actionAddShop($type, $sort, $detail, $lang,$productId, $status, $url,$product_type,$title)
    {
        $data = [
            'type' => $type,
            'sort' => $sort,
            'detail' => $detail,
            'productId' => $productId,
            'status' => $status,
            'product_type' => $product_type,
            'title' => $title,
            'lang' => $lang,
            'url' => $url,
            'createdAt' => time(),
        ];
        return (new shopBanner)->create($data);
    }

    /**
     * 商城图列表
     *
     * @param integer $page
     * @param integer $size
     * @return void
     */
    public function actionShops($page = 1, $size = 50)
    {
        $condition = '1';
        $bind = [];
        $fileds = 'id,type,sort,detail,productId,status,url,lang,product_type,title,createdAt';
        return (new shopBanner)->getRecords('shop_banners', $fileds,$condition,$bind,[],['sort'],$page,$size);
    }

    /**
     * 修改
     *
     * @param [type] $id
     * @param [type] $status
     * @return void
     */
    public function actionEditShop($id, $status)
    {
        if ($status == 'yes') {
            $data = ['status' => 'no'];
        } else {
            $data = ['status' => 'yes'];
        }
        return (new shopBanner)->getDb()->update('shop_banners')->cols($data)->where('id=' . $id)->query() == 1;
    }



    /**
     * 删除banner
     *
     * @param [type] $id
     * @return void
     */
    public function actionDelShop($id)
    {
        return (new shopBanner)->getDb()->delete('shop_banners')->where('id=' . $id)->query() == 1;
    }

    /**
     * 编辑banner
     *
     * @param [type] $id
     * @param [type] $status
     * @param [type] $img_url
     * @param [type] $newsId
     * @param [type] $lang
     * @return void
     */
    public function actionUpdateBanner($id, $status, $img_url, $newsId, $lang){
        return (new Banner)->getDb()->update('banners')->cols([
            'status' => $status,
            'img_url' => $img_url,
            'newsId' => $newsId,
            'lang' => $lang
        ])->where("id = " . $id)->query() == 1;
    }

    /**
     * 编辑banner
     *
     * @param [type] $id
     * @param [type] $status
     * @param [type] $img_url
     * @param [type] $newsId
     * @param [type] $lang
     * @return void
     */
    public function actionUpdateShopBanner($id, $status, $url, $type, $productId, $detail, $sort, $lang, $title, $product_type)
    {
        return (new shopBanner)->getDb()->update('shop_banners')->cols([
            'status' => $status,
            'url' => $url,
            'type' => $type,
            'productId' => $productId,
            'detail' => $detail,
            'sort' => $sort,
            'lang' => $lang,
            'title' => $title,
            'product_type' => $product_type,
        ])->where("id = " . $id)->query() == 1;
    }

}