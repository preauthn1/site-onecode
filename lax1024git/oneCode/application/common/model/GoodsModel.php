<?php

namespace app\common\model;

use think\Model;

class GoodsModel extends Model {

    protected $autoWriteTimestamp = true;
    // 确定链接表名
    protected $name = 'goods';

    public function attr() {
        return $this->hasMany('GoodsAttrModel', 'goods_id', 'goods_id');
    }

    /**
     * 更新商品状态
     * @param array $param
     */
    public function updateStatus($param = [], $where = []) {
        try {
            $this->where($where)->update($param);
            return msg(1, '', 'ok');
        } catch (\Exception $e) {

            return msg(-1, '', $e->getMessage());
        }
    }

}
