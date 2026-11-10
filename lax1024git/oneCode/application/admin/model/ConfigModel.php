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
namespace app\admin\model;

use think\Model;
use think\Cache;

class ConfigModel extends Model
{
    // 确定链接表名
    protected $name = 'setting';

    /**
     * 编辑管理员信息
     * @param $param
     */
    public function editConfig($param)
    {
        foreach ($param as $key => $value) {
             $row = $this->where(['field' => $key])->find();
             if ($row) {
                 $this->where(['field' => $key])->update(['value' => trim($value)]);
             }else{
                $this->insert([
                    'name' => trim($key),
                    'field' => trim($key),
                    'value' => trim($value),
                    
                ]);
             }
        }
        $this->reWriteConfig();
        return msg(1, '', 'ok');
    }

    public function reWriteConfig() {
        $config = $this->getConfig();
        if (extension_loaded('redis')) {
            Cache::store("redis")->set(config("my.redis_key.config"), $config);
        }
    }
    /**
     * 获取系统设置信息
     * @param $id
     */
    public function getConfig()
    {
        $all = $this->all();
        $result = [];
        foreach ($all as $row) {
            $result[$row['field']] = $row['value'];
        }
        return $result;
    }

    /**
     * 更新车型状态
     * @param array $param
     */
    public function updateStatus($param = [])
    {
        try{
            $this->where('id', 1)->update($param);
            return msg(1, '', 'ok');
        }catch (\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }
}
