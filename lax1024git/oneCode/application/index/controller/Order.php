<?php

namespace app\index\controller;

use app\common\model\OrderModel;
use think\Db;
use think\Log;
use think\Exception;
use app\services\OrderService;
use app\services\UserService;

class Order extends Base {

    // 首页
    public function index() {
//        $act = $this->request->param('act/d', 0);
//        $this->assign('type', $act);
        $this->setTitle(lang('拼单记录'));
        return $this->fetch();
    }

    public function getLists() {
        $type = $this->request->param('type/d', 0);
        $page = $this->request->param('page/d', 1);
        $limit = $this->request->param('pageSize/d', 10);
        $offset = ($page >= 1 ? $page - 1 : 0) * $limit;
        $where = [];
        $grab_order_mode = getConfig('grab_order_mode');
        $user = $this->userInfo;
        if ($type == 2) { //可以参与的
            if ($grab_order_mode == 1) { //系统配单
                $where['user_id'] = 0;
                $where['specify_user_id'] = $user['id'];
            } else if ($grab_order_mode == 2){ //CP共同完成
                $where['specify_user_id'] = $user['id'];
                $where['status'] = 1;
                $where['paid_status'] = 2;
            } else {
                $where['user_id'] = ["IN", function($query) use($user){
                    $query->name('home_user')->whereNull('delete_time')
                            ->where(function($query) use($user){
                                $query->where('cp_id', $user['id'])->whereOr('id', $user['cp_id']);
                            })->field('id');
                }];
            }
            $where['status'] = 1;
        }elseif($type == 3) { //已完成的
            if ($grab_order_mode == 2){ //CP共同完成
                $where['user_id'] = $user['id'];
                $where['status'] = 2;
            } else {
                $where['user_id'] = $this->userid;
                $where['status'] = 2;
            }
        }else{ //我发起的
            if ($grab_order_mode == 2){ //CP共同完成
                $where['user_id'] = $user['id'];
                $where['parent_id'] = 0;
                $where['status'] = 1;
            } else {
                $where['user_id'] = $this->userid;
                $where['status'] = 1;
            }
        }
        /******************* 去掉可参与的 start ********************/
//        if($type == 2) { //已完成的
//            if ($grab_order_mode == 2){ //CP共同完成
//                $where['user_id'] = $user['id'];
//                $where['status'] = 2;
//            } else {
//                $where['user_id'] = $this->userid;
//                $where['status'] = 2;
//            }
//        }else{ //我发起的
//            if ($grab_order_mode == 2){ //CP共同完成
//                $where['user_id'] = $user['id'];
//                $where['parent_id'] = 0;
//                $where['status'] = 1;
//            } else {
//                $where['user_id'] = $this->userid;
//                $where['status'] = 1;
//            }
//        }
        /******************* 去掉可参与的 end ********************/
        /******************* 去掉我发起的 start ********************/
        /*if ($type == 1) { //可以参与的
            if ($grab_order_mode == 1) { //系统配单
                $where['user_id'] = 0;
                $where['specify_user_id'] = $user['id'];
            } else if ($grab_order_mode == 2){ //CP共同完成
                $where['specify_user_id'] = $user['id'];
                $where['status'] = 1;
                $where['paid_status'] = 2;
            } else {
                $where['user_id'] = ["IN", function($query) use($user){
                    $query->name('home_user')->whereNull('delete_time')
                        ->where(function($query) use($user){
                            $query->where('cp_id', $user['id'])->whereOr('id', $user['cp_id']);
                        })->field('id');
                }];
            }
            $where['status'] = 1;
        }elseif($type == 2) { //已完成的
            if ($grab_order_mode == 2){ //CP共同完成
                $where['user_id'] = $user['id'];
                $where['status'] = 2;
            } else {
                $where['user_id'] = $this->userid;
                $where['status'] = 2;
            }
        }*/
        /******************* 去掉我发起的 end ********************/
        $lists = OrderModel::with(['goods', 'goods_attr', 'user'])->where($where)->limit($offset, $limit)->order('id desc')->select();
        $result = [];
        foreach ($lists as $k => $val) {
            $row = [
                'id' => $val['id'],
                'user_id' => $val['user_id'],
                'type' => $type,
                'order_sn' => $val['order_sn'],
                'goods_id' => $val['goods_id'],
                'status' => $val['status'],
                'repurchase_status' => $val['repurchase_status'],
                'goods_id' => $val['goods_id'],
                'attr_id' => $val['attr_id'],
                'shop_price' => $val['shop_price'],
                'retail_price' => $val['retail_price'],
                'return_bonus' => $val['return_bonus'],
                'goods_name' => $val['goods']['goods_name'],
                'pic' => toMedia($val['goods']['pic']),
                'nick_name' => hidnstr($val['user']['nick_name'], 1),
                'create_time' => $val['create_time'],
            ];
            
            if (isset($row['goods_attr']) && $row['goods_attr']) {
                $row['goods_name'] .= "[{$row['goods_attr']['name']}]";
            }
            $row['less_person_num'] = 0;
            if (in_array($type, [2, 1]) ) {
                $row['less_person_num'] = $val['goods']['order_number'] - OrderService::getUsersByOrderId($val['parent_id'] > 0 ? $val['parent_id'] : $val['id']);
            }
            $result[] = $row;
        }
        $grab_order_mode = getConfig('grab_order_mode');
        $this->assign("my_grab_order_mode", $grab_order_mode);
        if ($result) {
            $content = $this->fetch('item', ['lists' => $result]);
            $this->success('ok', '', $content);
        } else {
            $this->error(lang('暂无数据'));
        }
    }
    
    /**
     * 回购
     * @return type
     */
    public function repurchase() {
        $lock = userLock($this->userid, 'order-repurchase', 10);
        if (!$lock) {
            return $this->error(lang('请勿频繁提交'));
        }
        
        if (UserService::isActivity($this->userid)) {
            return $this->error(lang('请完成活动任务之后进行回购'));
        }
        
        $order_id = $this->request->param('id/d');
        $where = [
            'id' => $order_id,
            'user_id' => $this->userid,
            'status' => 2,
            'repurchase_status' => 1,
        ];

        Db::startTrans();
        try {
            $order = OrderModel::where($where)->lock(true)->find();
            if ($order) {
                $order->repurchase_status = 2;
                $order->save();
            }
            Db::commit();
        } catch (Exception $exc) {
            Db::rollback();
            Log::write($exc->getMessage(), 'error', true);
            return $this->error(lang('操作失败'));
        }
        return $this->success(lang('操作成功'));
    }
    
    /**
     * 回购记录
     * @return type
     */
    public function repurchase_log() {
        if ($this->request->isAjax()) {
            $type = $this->request->param('type/d', 0);
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('pageSize/d', 10);
            $offset = ($page >= 1 ? $page - 1 : 0) * $limit;
            $where = [];
            $where['user_id'] = $this->userid;
            // 回购状态，1待回购，2回购申请，3回购成功
            $where['repurchase_status'] = $type == 1 ? 3 : 2;
            
            $lists = OrderModel::with(['goods', 'goods_attr', 'user'])->where($where)->limit($offset, $limit)->order('id desc')->select();
            $result = [];
            foreach ($lists as $k => $val) {
                $row = [
                    'id' => $val['id'],
                    'type' => $type,
                    'order_sn' => $val['order_sn'],
                    'goods_id' => $val['goods_id'],
                    'status' => $val['status'],
                    'repurchase_status' => $val['repurchase_status'],
                    'goods_id' => $val['goods_id'],
                    'attr_id' => $val['attr_id'],
                    'shop_price' => $val['shop_price'],
                    'retail_price' => $val['retail_price'],
                    'return_bonus' => $val['return_bonus'],
                    'goods_name' => $val['goods']['goods_name'],
                    'pic' => toMedia($val['goods']['pic']),
                    'nick_name' => hidnstr($val['user']['nick_name'], 1),
                    'create_time' => $val['create_time'],
                ];
                $row['less_person_num'] = 0;

                if (isset($row['goods_attr']) && isArrayAndNotEmpty($row['goods_attr'])) {
                    $row['goods_name'] .= "[{$row['goods_attr']['name']}]";
                }
                $result[] = $row;
            }
            if ($result) {
                $content = $this->fetch('item2', ['lists' => $result]);
                $this->success('ok', '', $content);
            } else {
                $this->error(lang('暂无数据'));
            }
        }
        $this->setTitle(lang('回购记录'));
        return $this->fetch();
    }

}
