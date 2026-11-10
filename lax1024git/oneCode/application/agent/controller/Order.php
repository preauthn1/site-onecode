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

namespace app\agent\controller;

use app\common\model\OrderModel;
use app\admin\model\MemberModel;
use app\services\OrderService;
use app\services\UserService;
use app\common\model\GrabOrderModel;
use app\common\model\GoodsModel;
use app\common\model\UserModel;

class Order extends Base {
    
    public function index() {
        $grab_order_mode = getConfig('grab_order_mode');
        $order_status = config('my.order');
        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = ['is_demo' => 0, 'parent_id' => 0];
            $uid = null;
            if (!empty($param['searchText'])) {
                $uid = UserModel::where('mobile|id', $param['searchText'])->where('tree_id', 'LIKE', "%/{$this->userid}/%")->value('id');
            }
            if ($uid) {
                $where['user_id|specify_user_id'] = $uid;
            }elseif(!empty($param['searchText'])) {
                $where['order_sn'] = $param['searchText'];
            }
            if (!$uid) {
                $where['user_id|specify_user_id'] = ['IN', function($query){
                    $query->name('home_user')->where('tree_id', 'LIKE', "%/{$this->userid}/%")->field('id');
                }];
            }
            if (empty($param['startTime'])) {
//                $param['startTime'] = getDateStr();
            }
            if (empty($param['endTime'])) {
//                $param['endTime'] = getDateStr();
            }
            if (!empty($param['startTime']) && !empty($param['endTime'])) {
                $start_time = ($param['startTime'] . " 00:00:00");
                $end_time = ($param['endTime'] . " 23:59:59");
                $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
            }

            $result = OrderModel::where($where)
                            ->limit($offset, $limit)->order('create_time desc')->select();

            $total = OrderModel::where($where)->count();

            // 拼装参数
            foreach ($result as $key => $row) {
                $buttons = $this->makeButton($row['id']);
                if (($grab_order_mode == 1 && $row['status'] == '1' && $row['user_id'] == 0) || ($grab_order_mode == 2 && $row['status'] == '1' && $row['parent_id'] == 0 && $row['paid_status'] == '1')) {
                    $buttons['删除'] = [
                        'auth' => 'order/del',
                        'href' => "javascript:del(" . $row['id'] . ")",
                        'btnStyle' => 'danger',
                        'icon' => 'fa fa-trash-o'
                    ];
                }
                $result[$key]['operate'] = showOperate($buttons);
                $result[$key]['status'] = getStatusText($row['status'], $order_status['status'][$row['status']]);
                $result[$key]['re_users'] = OrderService::getRepurchaseUsersByOrderId($row['id']);
                $result[$key]['mobile'] = MemberModel::where('id', $row['user_id'])->value("mobile");
            }
            $return = [];
            $return['total'] = $total;  //总数据
            $return['rows'] = $result;
            return json($return);
        }

        $this->assign([
            'order_status' => $order_status,
            'grab_order_mode' => $grab_order_mode,
        ]);
        return $this->fetch();
    }
    
    public function repurchase() {
        $order_status = config('my.order');
        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = ['is_demo' => 0, 'repurchase_status' => ['>=', 2]];
            $uid = null;
            if (!empty($param['searchText'])) {
                $uid = UserModel::where('mobile|id', $param['searchText'])->where('tree_id', 'LIKE', "%/{$this->userid}/%")->value('id');
            }
            if ($uid) {
                $where['user_id'] = $uid;
            }elseif(!empty($param['searchText'])) {
                $where['order_sn'] = $param['searchText'];
            }
            if (!$uid) {
                $where['user_id'] = ['IN', function($query){
                    $query->name('home_user')->where('tree_id', 'LIKE', "%/{$this->userid}/%")->field('id');
                }];
            }
            if (empty($param['startTime'])) {
//                $param['startTime'] = getDateStr();
            }
            if (empty($param['endTime'])) {
//                $param['endTime'] = getDateStr();
            }
            if (!empty($param['startTime']) && !empty($param['endTime'])) {
                $start_time = ($param['startTime'] . " 00:00:00");
                $end_time = ($param['endTime'] . " 23:59:59");
                $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
            }

            $result = OrderModel::with('user')->where($where)
                            ->limit($offset, $limit)->order('update_time DESC')->select();

            $total = OrderModel::where($where)->count();

            // 拼装参数
            foreach ($result as $key => $row) {
                if ($row['repurchase_status'] == 2) {
                    $result[$key]['operate'] = showOperate($this->makeButton($row, 'repurchase'));
                }
                
                $result[$key]['is_xuni'] = getStatusText($row['is_xuni'], config('my.is_xuni')[$row['is_xuni']]);
                $result[$key]['repurchase_status'] = getStatusText($row['repurchase_status'], $order_status['repurchase'][$row['repurchase_status']]);
                $result[$key]['mobile'] = MemberModel::where('id', $row['user_id'])->value("mobile");
            }
            $return = [];
            $return['total'] = $total;  //总数据
            $return['rows'] = $result;
            return json($return);
        }

        $grab_order_mode = getConfig('grab_order_mode');
        $this->assign([
            'order_status' => $order_status,
            'grab_order_mode' => $grab_order_mode,
        ]);
        return $this->fetch();
    }
    
    public function grab() {
        if (request()->isAjax()) {
            $param = input('param.');
            if (isset($param['act']) && $param['act'] == 'grab') {
                $lock = userLock($param['id'], 'goods-grab', 10);
                if (!$lock) {
                    return $this->error(lang('请勿频繁提交'));
                }
                $goods = GoodsModel::where('goods_id', $param['goods_id'])->find();
                if (empty($goods)) {
                    return $this->error(lang('请选择商品'));
                }
                $where['user_id'] = ['IN', function($query){
                    $query->name('home_user')->where('tree_id', 'LIKE', "%/{$this->userid}/%")->field('id');
                }];
                $grab = GrabOrderModel::where('id', $param['id'])->where($where)->where('status', 0)->find();
                if (!empty($grab)) {
                    $grab->status = 1;
                    $grab->goods_id = $param['goods_id'];
                    $grab->save();

                    $grab_order_mode = getConfig('grab_order_mode');
                    if($grab_order_mode==2){                    //cp共同完成模式
                        $MemberModel = new MemberModel();
                        $curUser=$MemberModel->getOneUser($grab['user_id']);//当前用户信息
                        $order = [
                            'order_sn' => genOrderSN('', 'PD'),//AB为虚拟订单编号开头,PD-真实
                            'is_xuni' => $curUser['is_xuni'],                   //1-虚拟用户，0-真实用户
                            'user_id' => $grab['user_id'],
                            'level_id' => $curUser['level_id'],
                            'parent_id' => 0,
                            'goods_id' => $goods['goods_id'],
                            'attr_id' => 0,
                            'shop_price' => $goods['shop_price'],
                            'retail_price' => $goods['retail_price'],
                            'return_bonus' => $goods['return_bonus'],
                            'specify_user_id' => $curUser['cp_id'],
                            'status' => 1,
                            'paid_status' => 1,
                            'repurchase_status' => 1,
                        ];
                    }else {
                        $order = [
                            'order_sn' => genOrderSN('', 'AB'),
                            'is_xuni' => 1,
                            'user_id' => 0,
                            'level_id' => 0,
                            'parent_id' => 0,
                            'goods_id' => $goods['goods_id'],
                            'attr_id' => 0,
                            'shop_price' => $goods['shop_price'],
                            'retail_price' => $goods['retail_price'],
                            'return_bonus' => $goods['return_bonus'],
                            'specify_user_id' => $grab['user_id'],
                            'status' => 1,
                            'paid_status' => 2,
                            'repurchase_status' => 1,
                        ];
                    }
                    OrderModel::create($order);
                    insert_operation_log('代理后台分配订单', "订单号:{$order['order_sn']};用户ID:{$grab['user_id']},申请记录ID:{$param['id']}");
                }
                $this->success(lang('分配成功'));
            }
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            $uid = null;
            if (!empty($param['searchText'])) {
                $uid = UserModel::where('mobile|id', $param['searchText'])->where('tree_id', 'LIKE', "%/{$this->userid}/%")->value('id');
            }
            if ($uid) {
                $where['user_id'] = $uid;
            }
            if (!$uid) {
                $where['user_id'] = ['IN', function($query){
                    $query->name('home_user')->where('tree_id', 'LIKE', "%/{$this->userid}/%")->field('id');
                }];
            }
            if (!empty($param['startTime']) && !empty($param['endTime'])) {
                $start_time = ($param['startTime'] . " 00:00:00");
                $end_time = ($param['endTime'] . " 23:59:59");
                $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
            }

            $result = GrabOrderModel::with('goods')->where($where)
                            ->limit($offset, $limit)->order('id DESC')->select();

            $total = GrabOrderModel::where($where)->count();

            // 拼装参数
            $status = [
                '0' => '待处理',
                '1' => '已处理',
            ];
            foreach ($result as $key => $row) {
                if ($row['status'] == 0) {
                    $result[$key]['operate'] = showOperate($this->makeButton($row['id'], 'grab'));
                }
                $result[$key]['status'] = getStatusText($row['status'], $status[$row['status']]);
                $result[$key]['mobile'] = MemberModel::where('id', $row['user_id'])->value("mobile");
            }
            $return = [];
            $return['total'] = $total;  //总数据
            $return['rows'] = $result;
            return json($return);
        }

        $this->assign([
            'goods_list' => GoodsModel::where('status', 1)->field('goods_id,goods_name,retail_price')->order('retail_price ASC')->select()
        ]);
        return $this->fetch();
    }

    // 编辑订单
    public function detail() {
        $id = $this->request->param('id/d');
        $order = OrderModel::with(['goods', 'user', 'goods_attr'])->where('id', $id)->where('parent_id', 0)->find();
        if (empty($order)) {
            $this->error(lang('订单信息不存在'));
        }
//        dump($order->toArray());die;
        $order_status = config('my.order');
        
        $sub_orders = OrderModel::with('user')->where('id', $id)->whereOr('parent_id', $id)->select();
        $this->assign('order', $order);
        $this->assign('sub_orders', $sub_orders);
        $this->assign('order_status', $order_status);
        return $this->fetch();
    }

    public function checkout() {
        $id = $this->request->param('id/d');
        $status = $this->request->param('status/d');
        $lock = userLock($id, 'order_checkout');
        if (!$lock) {
            return $this->error(lang('请勿重复提交'));
        }
        
        $where['user_id'] = ['IN', function($query){
            $query->name('home_user')->where('tree_id', 'LIKE', "%/{$this->userid}/%")->field('id');
        }];
        $order = OrderModel::where('id', $id)->where('repurchase_status', 2)->where($where)->lock(true)->find();
        if (empty($order)) {
            $this->error(lang('订单信息不存在或已处理'));
        }
        
        if (!in_array($status, [1, 3])) {
            $this->error(lang('操作有误'));
        }
        insert_operation_log('代理后台回购审核:' . ($status == 3 ? '通过' : '拒绝'), "订单号:{$order['order_sn']};");
        $order->repurchase_status = $status;
        $order->save();
        if ($status == 3) {
            UserService::addRePurchase($order->user_id, $order->retail_price, $id);
            UserService::addBonus($order->user_id, $order->return_bonus, $id);
        }
        $this->success(lang('操作成功'));
    }
    
    // 删除用户
    public function del() {
        $id = input('param.id');
        $where = [];
        $where['user_id'] = ['IN', function($query){
            $query->name('home_user')->where('tree_id', 'LIKE', "%/{$this->userid}/%")->field('id');
        }];
        $info = OrderModel::where('id', $id)->where($where)->find();
        $grab_order_mode = getConfig('grab_order_mode');
        if ($info && $grab_order_mode == 1 && $info['status'] == '1' && $info['user_id'] == 0) {
            insert_operation_log('代理后台删除分配订单', $info);
            $info->delete();
        }else if ($info && $grab_order_mode == 2 && $info['status'] == '1') {
            $done_order = OrderModel::where('parent_id', $id)->find();//参与者已付款
            if($done_order){
                return json(msg(1, url('index'), lang('CP已付款，订单不能删除')));
            }
            insert_operation_log('代理后台删除分配订单-CP拼单', $info);
            $info->delete();
        }
        return json(msg(1, url('index'), lang('删除成功')));
    }
    
    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id, $type = 'list') {
        if ($type == 'repurchase') {
            return [
                '确认回购' => [
                    'auth' => 'order/checkout',
                    'data' => "data-order='". json_encode($id)."'",
                    'href' => 'javascript:;',
                    'btnStyle' => 'primary repurchaseEvent',
                    'icon' => 'fa fa-check'
                ],
                '拒绝' => [
                    'auth' => 'order/checkout',
                    'data' => "data-order='". json_encode($id)."'",
                    'href' => 'javascript:;',
                    'btnStyle' => 'danger rejectEvent',
                    'icon' => 'fa fa-close'
                ],
            ]; 
        } 
        elseif($type == 'grab') {
            return [
                '分配' => [
                    'auth' => 'order/grab',
                    'data' => "data-id='". $id ."'",
                    'href' => 'javascript:;',
                    'btnStyle' => 'primary grabEvent',
                    'icon' => 'fa fa-check'
                ],
            ]; 
        }
        else{
            return [
                '详情' => [
                    'auth' => 'order/detail',
                    'href' => url('order/detail', ['id' => $id]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
            ]; 
        }
    }

}
