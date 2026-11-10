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

class RechargeModel extends Model {

    protected $autoWriteTimestamp = true;
    // 确定链接表名
    protected $name = 'recharge';
    protected $insert = ['order_id', 'status' => 1];

    protected function setOrderIdAttr() {
        return genOrderSN(random(4, 'number'), 'RE');
    }

    /**
     * 根据搜索条件获取商家列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getLlistsByWhere($where, $offset, $limit, $orderby) {
        $lists = $this->alias('w')
                ->field('w.*, hu.id as uid, hu.true_name, hu.is_xuni, hu.mobile, hu.last_login_ip')
                ->join('home_user hu', 'w.user_id = hu.id', 'left')
                ->where($where)
                ->limit($offset, $limit)
                ->order($orderby)
                ->select();
        return $lists;
    }

    /**
     * 根据搜索条件获取商家列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getAllByWhere($where, $orderby) {
        $lists = $this->alias('w')
                ->field('w.*, hu.id as uid, hu.true_name, hu.is_xuni, hu.mobile, hu.last_login_ip')
                ->join('home_user hu', 'w.user_id = hu.id', 'left')
                ->where($where)
                ->order($orderby)
                ->select();
        return $lists;
    }

    /**
     * 根据搜索条件获取所有的商家数量
     * @param $where
     */
    public function getAlllists($where) {
        return $this->alias('w')
                        ->join('home_user hu', 'w.user_id = hu.id', 'left')
                        ->where($where)
                        ->count();
    }

    /**
     * 根据管理员id获取角色信息
     * @param $id
     */
    public function getOneInfo($id) {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除管理员
     * @param $id
     */
    public function delInfo($id) {
        try {

            $this->where('id', $id)->delete();
            return msg(1, '', '删除充值记录成功');
        } catch (PDOException $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    public static function getRecharge($userid) {
        return (new self())->where('user_id', $userid)->where('status', 2)->sum('money');
    }

    public static function getTodayRecharge($userid) {
        return (new self())->where('user_id', $userid)->whereTime('create_time', 'today')->where('status', 2)->sum('money');
    }

}
