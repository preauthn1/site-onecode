<?php
namespace app\admin\controller;

use app\common\model\UploadModel;
use think\Controller;
use think\Db;
use think\File;

class Upload extends Base
{
    /**
     * 上传
     * @author jry <598821125@qq.com>
     */
    public function upload()
    {
        if(request()->isAjax()){
            $UploadModel = new UploadModel();
            $ret = $UploadModel->Upload('file');
            if($ret){
                $ret['data']['src'] = toMedia($ret['data']['path']);
                $ret['data']['url'] = toMedia($ret['data']['path']);
                return json(msg($ret['code'], $ret['data'], $ret['msg']));
            } else {
                return json(msg(-1, '', '上传失败'));
            }
        }
    }

    // 编辑头像
    public function upavatar()
    {
        if(request()->isAjax()){
            $UploadModel = new UploadModel();
            $ret = $UploadModel->Upload('file', 'head');
            if($ret){
                $ret['data']['src'] = toMedia($ret['data']['path']);
                $ret['data']['url'] = toMedia($ret['data']['path']);
                return json(msg($ret['code'], $ret['data'], $ret['msg']));
            } else {
                return json(msg(-1, '', '上传失败'));
            }
        }
    }

    // 上传文件
    public function uploadFile()
    {
        if(request()->isAjax()){
            $UploadModel = new UploadModel();
            $ret = $UploadModel->UploadFile('file');
            if($ret){
                $ret['data']['src'] = toMedia($ret['data']['path']);
                $ret['data']['url'] = toMedia($ret['data']['path']);
                return json(msg($ret['code'], $ret['data'], $ret['msg']));
            } else {
                return json(msg(-1, '', '上传失败'));
            }
        }
    }
    
}
