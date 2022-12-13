<?php

use SebastianBergmann\Environment\Console;

class AdminController extends BaseController
{
    /**
     * 列表
     *
     * @param [type] $name
     * @param [type] $model
     * @param integer $page
     * @param integer $size
     * @return void
     */
    public function actionList($name,$model,$condition = '',$bind = [],$sortBy = false,$page=1,$size=100)
    {
        $url = $GLOBALS['app_conf']['apis']['rateApi'] . '/admin/list';
        $data = [
            'name' => $name,
            'model' => $model,
            'condition' => $condition,
            'bind' => $bind,
            'sortBy' => $sortBy,
            'page' => $page,
            'size' => $size
        ];
        $result =  json_decode(cURL_request_JSON($url, $data, 'POST', getHeaders()), true);
        if(isset($result['data']['items'])){
            foreach($result['data']['items'] as $key=>$value){
                if(isset($value['createdAt'])){
                    $result['data']['items'][$key]['createdAt'] = date('Y-m-d',$value['createdAt']) ;
                }
                if (isset($value['updatedAt'])) {
                    $result['data']['items'][$key]['updatedAt'] = date('Y-m-d', $value['updatedAt']);
                }
                if (isset($value['expiredAt'])) {
                    $result['data']['items'][$key]['expiredAt'] = date('Y-m-d', $value['expiredAt']);
                }
            }
        }
        return $result;
    }

    /**
     * 添加产品
     *
     * @param [type] $name
     * @param [type] $model
     * @param [type] $data
     * @return void
     */
    public function actionCreate($name,$model,$data)
    {
        if(isset($data['cycle'])){
            $data['cycle'] = $data['cycle'] * 24 * 60 * 60;
        }
        $data['createdAt'] = time();
        $data['updatedAt'] = time();
        $postData = [
            'name' => $name,
            'model' => $model,
            'data' => $data
        ];
        $url = $GLOBALS['app_conf']['apis']['rateApi'] . '/admin/create';
        return json_decode(cURL_request_JSON($url, $postData, 'POST', getHeaders()), true);
    }

    /**
     * 编辑产品
     *
     * @param [type] $name
     * @param [type] $data
     * @return void
     */
    public function actionEdit($name,$model,$data)
    {
        if(isset($data['id'])){
            $id = $data['id'];
            unset($data['id']);
            unset($data['_index']);
            unset($data['_rowKey']);
            unset($data['createdAt']);
            $data['updatedAt'] = time();
            if (isset($data['cycle'])) {
                $data['cycle'] = $data['cycle'] * 24 * 60 * 60;
            }
        }
        $postData = [
            'name' => $name,
            'model' => $model,
            'id' => $id,
            'data' => $data
        ];
        $url = $GLOBALS['app_conf']['apis']['rateApi'] . '/admin/update';
        return json_decode(cURL_request_JSON($url, $postData, 'POST', getHeaders()), true);
    }

    /**
     * 查询
     *
     * @param [type] $condition
     * @return void
     */
    public function actionRead($name,$model,$id)
    {
        $postData = [
            'name' => $name,
            'model' => $model,
            'id' => $id
        ];
        $url = $GLOBALS['app_conf']['apis']['rateApi'] . '/admin/read';
        return json_decode(cURL_request_JSON($url, $postData, 'POST', getHeaders()), true);
    }
}
