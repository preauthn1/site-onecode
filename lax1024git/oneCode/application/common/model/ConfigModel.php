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
namespace app\common\model;

use think\Model;

class ConfigModel extends Model
{
    // 确定链接表名
    protected $name = 'setting';

    /**
     * 获取系统设置信息
     * @param $id
     */
    public function getConfig($cache = true)
    {
        $redis = \think\Cache::store('redis');
        $redis_key = config("my.redis_key.config");
        if ($redis->has($redis_key) && $cache) {
            return $redis->get($redis_key);
        }
        $all = $this->all();
        $result = [];
        foreach ($all as $row) {
            $result[$row['field']] = $row['value'];
        }
        return $result;
    }
}
