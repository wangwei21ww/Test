<?php

use SebastianBergmann\Environment\Console;

class AccountController extends BaseController
{
    /**
     * 获取用户信息
     * @param int $page
     * @return mixed
     */
    public function actionAccounts($page = 1, $pageSize = 100, $start = '',$end = '')
    {
        if($start != '' and $end != ''){
            $postData = [
                'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
                'start' => $start,
                'end' => $end
            ];
            $config = $GLOBALS['app_conf']['bitKeepSdk'];

            $result = json_decode((new BitKeepOpenSDK($config))->export($postData), true);
            if (isset($result['data'])) {
                $data['items'] = $result['data'];
            }
        }else{
            $postData = [
                'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
                'page' => $page,
                'pageSize' => $pageSize
            ];
            $config = $GLOBALS['app_conf']['bitKeepSdk'];

            $result = json_decode((new BitKeepOpenSDK($config))->accounts($postData), true);

            if (isset($result['data'])) {
                $data = $result['data'];
            }
        }
        return $data;
    }

    /**
     * 搜索用户
     *
     * @param [type] $email
     * @return void
     */
    public function actionSearch($email)
    {
        if(strpos($email,'@')){
            if ($email == '') {
                throw new Exception('The emil field', 90160);
            }

            $postData = [
                'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
                'identity' => $email,
            ];

            $config = $GLOBALS['app_conf']['bitKeepSdk'];

            $result = json_decode((new BitKeepOpenSDK($config))->search($postData), true);

            if (isset($result['data'])) {
                $data = $result['data'];
            }
        }else{
            $postData = [
                'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
                'userId' => $email,
            ];
            $config = $GLOBALS['app_conf']['bitKeepSdk'];

            $result = json_decode((new BitKeepOpenSDK($config))->accountInfo($postData), true);

            if (isset($result['data'])) {
                $tmp = $result['data'];
                $data['total'] = 1;
                $data['items'][0] = [
                    'id' => $email,
                    'username' => $tmp['username'],
                    'email' => $tmp['email'],
                    'cc' => $tmp['cc'],
                    'createdAt' => $tmp['createdAt'],
                    'validate' => $tmp['validate'],
                ];
            }
        }
        return $data;
    }

    /**
     * 登录获取token
     *
     * @param [type] $email
     * @param [type] $password
     * @return void
     */
    public function actionUserLogin($userName, $password)
    {
       $data = (new AdminUser)->find(['userName'=>$userName]);
       if($data['passWord'] === md5($password)){
           session_start();

           return $data;
       }
       return false;
    }


    /**
     * 获取用户kyc详情
     *
     * @param [type] $userId
     * @return void
     */
    public function actionGetDetail($userId)
    {
        if ($userId == '' or $userId == 0) {
            throw new Exception('The userId invalid', 90200);
        }
        $postData = [
            'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
            'userId' => $userId
        ];
        $config = $GLOBALS['app_conf']['bitKeepSdk'];
        $result = json_decode((new BitKeepOpenSDK($config))->profile($postData), true);
        return $result;
    }

    /**
     * 更新kyc状态
     *
     * @param [type] $userId
     * @param [type] $kyc_status
     * @return void
     */
    public function actionUpdateKyc($userId, $kyc_status, $reason = '')
    {
        if ($userId == '' or $userId == 0 or $kyc_status == '') {
            throw new Exception('The params invalid', 90200);
        }
        $postData = [
            'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
            'userId' => $userId,
        ];
        if ($kyc_status == 'ok') {
            $params = ['kyc_status' => 'validated'];
            sendMail($userId,'KYC审核通过', '尊敬的用户您好：您的实名认证已经完成，欢迎使用！');
        } else {
            $params = ['kyc_status' => 'reject', 'reason'=> $reason];
            sendMail($userId,'认证失败', '尊敬的用户您好：您的实名认证审核拒绝，拒绝原因是'.$reason.'，请重新认证！');
        }
        $postData['params'] = $params;
        $config = $GLOBALS['app_conf']['bitKeepSdk'];
        $result = json_decode((new BitKeepOpenSDK($config))->updateKyc($postData), true);
        return $result;
    }

    /**
     * 获取待审核的kyc
     *
     * @param integer $page
     * @param integer $pageSize
     * @return void
     */
    public function actionUserKycs()
    {
        $postData = [
            'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
            'params' => [
                'kyc_status' => 'pending',
            ]
        ];
        $config = $GLOBALS['app_conf']['bitKeepSdk'];
        $result = json_decode((new BitKeepOpenSDK($config))->profiles($postData), true);
        return $result;
    }

    /**
     * 获取历史审核的kyc
     *
     * @param integer $page
     * @param integer $pageSize
     * @return void
     */
    public function actionUserKycsHistory($params,$page=1,$size=100,$id = '')
    {   
        if($id){
            $postData = [
                'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
                'userId' => $id,
            ];
            $config = $GLOBALS['app_conf']['bitKeepSdk'];

            $result = json_decode((new BitKeepOpenSDK($config))->accountInfo($postData), true);

            if (isset($result['data'])) {
		 $tmp = $result['data'];
                if($params['kyc_status'] == $tmp['kyc_status']){
                    $data['total'] = 1;
                    $data['items'][0] = [
                        'userId' => $id,
                        'val' => $tmp['kyc_status'],
                    ];
                }else{
                    $data['total'] = 0;
                    $data['items'] = [
                    ];
                }
            }
            return $data;
        }
        $postData = [
            'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
            'params' => $params,
            'page'=>$page,
            'size' => $size
        ];
        $config = $GLOBALS['app_conf']['bitKeepSdk'];
        $result = json_decode((new BitKeepOpenSDK($config))->profiles($postData), true);
        return $result;
    }

    /**
     * 获取个人资产
     *
     * @param [type] $uid
     * @return void
     */
    public function actionAssets($uid)
    {
        $postData = [
            'appId' => $GLOBALS['app_conf']['bitKeepSdk']['appId'],
            'userId' => $uid
        ];
        $config = $GLOBALS['app_conf']['bitKeepSdk'];
        return json_decode((new BitKeepOpenSDK($config))->walletList($postData), true);
    }

    /**
     * 获取好友邀请
     *
     * @param [type] $uid
     * @return void
     */
    public function actionInvite($uid,$start = 0,$end = 0,$page = 1)
    {
        $url = $GLOBALS['app_conf']['apis']['api'] . '/Invite/Cloudview';
        $detailUrl = $GLOBALS['app_conf']['apis']['api'] . '/Invite/ProfitDetail';
        $cloud = json_decode(cURL_request_JSON($url, ['uid' => $uid], 'POST', getHeaders()), true);
        $datail = json_decode(cURL_request_JSON($detailUrl, ['uid' => $uid], 'POST', getHeaders()), true);
        $result['cloud']['items'] = $cloud['data'];
        $result['cloud']['info'] = $datail['data'][2];
        $rentUrl =  $GLOBALS['app_conf']['apis']['api'] . '/Invite/InviterProfits';
        $rentRe =  json_decode(cURL_request_JSON($rentUrl, ['uid' => $uid,'start'=>$start,'end'=>$end,'page'=>$page], 'POST', getHeaders()), true);
        $result['rent']['items'] = $rentRe['data']['items'];
        $result['rent']['info'] = $rentRe['data']['all_data'];
        return $result;
    }

    /**
     * 设置推广人
     *
     * @param [type] $name
     * @param [type] $data
     * @return void
     */
    public function actionTagging($uid,$name)
    {
        $url = $GLOBALS['app_conf']['apis']['api'] . '/Invite/Taging';
        return json_decode(cURL_request_JSON($url, ['uid' => $uid,'name'=>$name], 'POST', getHeaders()), true);
    }

}
