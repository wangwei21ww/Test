<?php

class UploadController extends BaseController
{
    public function actionIndex()
    {
        $fullpath = $GLOBALS['app_conf']['bannerUpload']['uploadDir'] . 'images';
        if (is_dir($fullpath) === false) {
            mkdir($fullpath, 0777, true);
        }
        $exts = ['png', 'jpg', 'jpeg'];
        $container = [];
        $i = 0;

        foreach ($_FILES as $file_info) {
            $info = pathinfo($file_info['file_name']);
            $filename = $this->isOriginName() == 'hash' ? md5(microtime(true) . $i++) : $info['filename'];
            $filenames = explode('.', $file_info['file_name']);
            l('upload_img', ['file_name' => $file_info['file_name']], true);
            $ext = end($filenames);
            if (!in_array($ext, $exts)) {
                throw new Exception('The Picture format is incorrect', 90070);
            }
            $upfile = $fullpath . '/' . $filename . '.' . $ext;
            file_put_contents($upfile, $file_info['file_data']);
            $container = str_replace($GLOBALS['app_conf']['bannerUpload']['uploadDir'], $GLOBALS['app_conf']['bannerUpload']['browserDomain'], $upfile);
            //$container = str_replace('/home/huobiao/api/web/','http://dev.bitkeep.com:40101/',$upfile);
        }
        return $container;
    }

    public function isOriginName()
    {
        $namePattrns = ['origin', 'hash'];
        if (isset($_POST['namePattrn']) and in_array($_POST['namePattrn'], $namePattrns)) {
            return $_POST['namePattrn'];
        }
        return 'hash';
    }
}
