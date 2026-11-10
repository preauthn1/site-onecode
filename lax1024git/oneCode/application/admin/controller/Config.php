<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\ConfigModel;

class Config extends Base
{
    public function index()
    {
        $config = new ConfigModel();
        if(request()->isPost()){
            $act = $this->request->param('act/s');
            if ($act == 'query_ip') {
                $ip = $this->request->param('ip/s');
                return $this->success('ok', '', ip2location_arr($ip, [1,2,3,4], 'en'));
            }
            $param = input('post.');
            $param['update_time'] = time();
            $flag = $config->editConfig($param);
            return json(msg($flag['code'], $flag['data'], lang($flag['msg'])));
        }
        $status = config('status');
        
        $this->assign([
            'config' => $config->getConfig(),
            'status' => $status,
            'ip' => $this->request->ip(),
            'ip_local' => ip2location_arr($this->request->ip(), [1,2,3,4], 'en'),
            'pd_part' => config('my.pd_part'),
        ]);
        return $this->fetch();
    }
}
