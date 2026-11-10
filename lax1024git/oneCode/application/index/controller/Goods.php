<?php

namespace app\index\controller;

use think\Db;
use think\Cache;
use app\common\model\GoodsModel;
use app\common\model\GoodsAttrModel;
use app\common\model\OrderModel;
use app\common\model\CateModel;
use app\common\model\GrabOrderModel;
use app\services\UserService;
use app\services\OrderService;
use app\services\CommonService;
use think\Exception;
use app\common\model\GoodsCommentModel;
use app\admin\model\MemberModel;

class Goods extends Nbase {

    // 首页
    public function index() {
        $where = ['status' => 1];
        $cate_id = $this->request->param('cate_id/d');
        $area = $this->request->param('area/d', -1);
        $is_discount = $this->request->param('is_discount/d', -1);//greenUI的是否优惠商品
        $keyword = $this->request->param('keyword/s');
        $cate = null;
        if ($cate_id > 0) {
            $where['cate_id'] = $cate_id;
            $cate = CateModel::where('id', $cate_id)->find();
        }
        if ($area !== -1) {
            $part_config = CommonService::getGoodsPricePartConfig($area);
            if (isset($part_config['price_min']) && isset($part_config['price_max'])) {
                $where['retail_price'] = ['BETWEEN', [$part_config['price_min'], $part_config['price_max']]];
            }
        }
        if ($is_discount !== -1) {
            $where['is_discount'] = $is_discount;
        }
        
        if ($keyword) {
            $where['goods_name'] = ['LIKE', "%{$keyword}%"];
        }
        
        if ($this->request->isAjax()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('pageSize/d', 10);
            $offset = ($page >= 1 ? $page - 1 : 0) * $limit;
            $goods_list = GoodsModel::where($where)->limit($offset, $limit)->order('retail_price ASC')->select();
            if (empty($goods_list)) {
                $this->error(lang('没有数据'));
            }
            foreach ($goods_list as $key => $value) {
                $goods_list[$key]['share_url'] = $this->getGoodsShareUrl($value['goods_id']);
            }
            $content = $this->fetch('item2', ['goods_list' => $goods_list]);
            $this->success('ok', '', $content);
        }
        $this->setTitle($cate ? $cate['name'] : lang('更多商品'));
        $part = CommonService::getGoodsPricePartConfig();
        $this->assign([
            'part' => $part,
            'cate_id' => $cate_id,
            'area' => $area,
            'is_discount' => $is_discount
        ]);
        return $this->fetch();
    }
    
    public function search() {
        $this->setTitle(lang('搜索'));
        return $this->fetch();
    }
    
    public function info() {
        $goods_id = $this->request->param('id/d');

        $goods = GoodsModel::with('attr')->find($goods_id);
        if (empty($goods)) {
            return $this->error(lang('商品信息不存在'));
        }
        $view_replace_str = config('view_replace_str');
        $share_url = $this->getGoodsShareUrl($goods_id);
        $this->setTitle(lang('商品详情'), true, '', true, '<img src="'.$view_replace_str['__IMG__'].'/icon-share.png" class="shareIcon"  data-clipboard-text="'.$share_url.'"/>');
        $goods['main_pic'] = explode(",", $goods['main_pic']);
//        dump($goods->toArray());die;
//        dump($undone_order_list);die;
        $this->assign('goods', $goods);
        $this->assign('undone_order_list', $this->getUndoneOrderList($goods_id));
        $this->assign('isCollect', CommonService::isCollectGoods($this->userid, $goods_id));
        $this->assign('avg_rate', CommonService::getGoodsAvgRate($goods_id));
        return $this->fetch();
    }
    
    public function comment() {
        $goods_id = $this->request->param('goods_id/d');
        if (request()->isAjax()) {
            $page = $this->request->param('page/d', 1);   // 页数
            $limit = $this->request->param('pageSize/d', 10);   // 每页条数
            $offset = ($page - 1) * $limit;

            $lists = GoodsCommentModel::where('goods_id', $goods_id)->limit($offset, $limit)->order('id DESC')->select();
            foreach ($lists as $k => $val) {
                $lists[$k]['avatar'] = toAvatar($val['avatar']);
            }
            if ($lists) {
                $content = $this->fetch('item_comment', ['lists' => $lists]);
                $this->success('ok', '', $content);
            } else {
                $this->error(lang('暂无数据'));
            }
        }
        $this->setTitle(lang('商品评论'));
        $this->assign('goods_id', $goods_id);
        return $this->fetch();
    }
    
    private function getUndoneOrderList ($goods_id) {
        $undone_order_list = [];
        $grab_order_mode = getConfig('grab_order_mode');
        if ($grab_order_mode == 1) { //系统配单
            for ($i = 0; $i < 3; $i++) {
                $row = [
                    'id' => 0,
                    'goods_id' => $goods_id,
                    'user_id' => 0,
                    'attr_id' => 0,
                    'avatar' => toAvatar(random_int(1, 10)),
                    'nick_name' => hidnstr(random(10), 1),
                ];

                $row['less_person_num'] = 1;
                $undone_order_list[] = $row;
            }
        } else if ($grab_order_mode == 2) { //CP共同完成
            $undone_order_list = Array();
        } else {
            $undone_orders = OrderService::getUndoneOrderByGoodsId($goods_id, $this->userid, empty($this->userInfo) ? 0 : $this->userInfo['cp_id']);
            foreach ($undone_orders as $k => $val) {
                $row = [
                    'id' => $val['id'],
                    'goods_id' => $val['goods_id'],
                    'user_id' => $val['user_id'],
                    'attr_id' => $val['attr_id'],
                    'avatar' => toAvatar($val['user']['avatar']),
                    'nick_name' => hidnstr($val['user']['nick_name'], 1),
                ];

                $row['less_person_num'] = $val['goods']['order_number'] - OrderService::getUsersByOrderId($val['parent_id'] > 0 ? $val['parent_id'] : $val['id']);
                $undone_order_list[] = $row;
            }
        }
        
        return $undone_order_list;
    }

    public function getListHtml() {
        $area = $this->request->param('area/d', -1);
        $is_discount = $this->request->param('is_discount/d', -1);//greenUI的是否优惠商品
        $amount = $this->request->param('amount',20);

        if($area != -1){
            $goods_list = CommonService::getGoodsList($area,$amount);
        }elseif ($is_discount != -1){
            $goods_list = CommonService::getGoodsList1($is_discount,$amount);
        }

        foreach ($goods_list as $key => $value) {
            $goods_list[$key]['share_url'] = $this->getGoodsShareUrl($value['goods_id']);
        }
        $content = $this->fetch('item', ['goods_list' => $goods_list]);
        $this->success('ok', '', $content);
    }

    public function collect() {
        if (empty($this->userInfo)) {
            return $this->error(lang('请登录'), url('login/index'));
        }
        $lock = userLock($this->userid, 'goods-collect', 10);
        if (!$lock) {
            return $this->error(lang('请勿频繁提交'));
        }
        $goods_id = $this->request->param('goods_id/d');
        if (CommonService::getGoods($goods_id)) {
            CommonService::collectGoods($this->userid, $goods_id);
            $this->success(lang("操作成功"));
        }else{
            return $this->error(lang('商品信息不存在'));
        }
    }
    
    // 去支付
    public function toPay() {
        if (empty($this->userInfo)) {
            return $this->error(lang('请登录'), url('login/index'));
        }
        $lock = userLock($this->userid, 'goods-topay', 10);
        if (!$lock) {
            return $this->error(lang('请勿频繁提交'));
        }
        $user = $this->userInfo;
        $order_id = $this->request->param('order_id/d', 0);
        $goods_id = $this->request->param('goods_id/d', 0);
        $attr_id = $this->request->param('attr_id/d', 0);
        $flag = '';
        $grab_order_mode = getConfig('grab_order_mode');
        if ($grab_order_mode == 1) { //系统配单
            $is_must_cp = getConfig('is_must_cp', false, 0);
            if ($is_must_cp && empty($this->userInfo['cp_id'])) {
                return $this->error(lang('您不能个人拼单,必须要有绑定cp'));
            }
            $where = [];
            if ($order_id > 0) {
                $where['id'] = $order_id;
            }
            if ($goods_id > 0) {
                $where['goods_id'] = $goods_id;
            }
            if ($attr_id > 0) {
//                $where['attr_id'] = $attr_id;
            }
            $order_info = OrderModel::where($where)->where('specify_user_id', $user['id'])->where('status', 1)->where('parent_id', 0)->find();

            if ($order_info) {
                $my_order_info = OrderModel::where('parent_id', $order_info['id'])->where('user_id', $this->userid)->find();
                if ($my_order_info) {
                    return $this->error(lang('请在系统匹配订单'));
                }
                $order_id = $order_info['id'];
                $goods_id = $order_info['goods_id'];
                $attr_id = $order_info['attr_id'];
            }else{
                return $this->error(lang('请在系统匹配订单'));
            }
        } else if($grab_order_mode == 2){  //cp必须完成拼单-cp共同完成模式
            if (empty($this->userInfo['cp_id'])) {
                return $this->error(lang('您不能个人拼单,必须要有绑定cp'));
            }
            $where = [];
            if ($order_id > 0) {
                $where['id'] = $order_id;
            }
            if ($goods_id > 0) {
                $where['goods_id'] = $goods_id;
            }
            if ($attr_id > 0) {
//                $where['attr_id'] = $attr_id;
            }
            $order_info = OrderModel::where($where)->where('user_id', $this->userInfo['id'])->where('status', 1)->where('parent_id', 0)->find();
            if($order_info){
                if ($order_info['paid_status'] == 1){
                    $flag = 'A';
                    $order_id = $order_info['id'];
                    $goods_id = $order_info['goods_id'];
                    $attr_id = $order_info['attr_id'];
                }else if($order_info['paid_status'] == 2){
                    return $this->error(lang('您已付款，请等待您的CP付款'));
                }else
                    return $this->error(lang('请在系统匹配订单'));
            }else{
                $order_info_cp = OrderModel::where($where)->where('specify_user_id', $this->userInfo['id'])->where('status', 1)->where('parent_id', 0)->find();
                if ($order_info_cp) {
                    $order_info_fq = OrderModel::where('parent_id', $order_info_cp['id'])->where('user_id', $this->userInfo['id'])->find();
                    if ($order_info_fq)
                        return $this->error(lang('您已付款，请等待您的CP付款'));
                    else{
                        $flag = 'B';
                        $order_id = $order_info_cp['id'];
                        $goods_id = $order_info_cp['goods_id'];
                        $attr_id = $order_info_cp['attr_id'];
                    }
                }else{
                    return $this->error(lang('请在系统匹配订单'));
                }
            }
        } else {
            if ($order_id > 0) {
                $order_info = OrderModel::where('id', $order_id)->where('user_id', 'IN', function($query) use($user){
                    $query->name('home_user')->whereNull('delete_time')
                            ->where(function($query) use($user){
                                $query->where('cp_id', $user['id'])->whereOr('id', $user['cp_id']);
                            })->field('id');
                })->where('status', 1)->where('parent_id', 0)->find();
                if ($order_info) {
                    $my_order_info = OrderModel::where('parent_id', $order_id)->where('user_id', $this->userid)->find();
                    if ($my_order_info) {
    //                    OrderService::completeOrder($order_id);
                        return $this->error(lang('不能重复参与'));
                    }
                    $goods_id = $order_info['goods_id'];
                    $attr_id = $order_info['attr_id'];
                }else{
                    return $this->error(lang('不是关联账户无法进行拼单'));
                }
            }
        }
        
        $day_order_count = $this->userInfo['pd_num'] > 0 ? $this->userInfo['pd_num'] : getConfig('day_order_count');
        if (intval($day_order_count) > 0) {
            $today_order_count = OrderModel::where('user_id', $this->userid)->whereTime('create_time', 'today')->count();
            if ($today_order_count >= $day_order_count) {
                return $this->error(lang('您今天的拼单次数已经用完了'));
            }
        }
        $goods = GoodsModel::find($goods_id);
        if (empty($goods)) {
            return $this->error(lang("商品信息不存在"));
        }
        
        $shop_price = $goods['shop_price'];
        $retail_price = $goods['retail_price'];
        $return_bonus = $goods['return_bonus'];
        
        $attr = [];

        if ($attr_id > 0) {
            $attr = GoodsAttrModel::where('goods_id', $goods_id)->where('id', $attr_id)->find();
        }
        
        if (!empty($attr)) {
            $shop_price = $attr['shop_price'];
            $retail_price = $attr['retail_price'];
            $return_bonus = $attr['return_bonus'];
        }
        if ($retail_price > $this->userInfo['user_money']) {
            return $this->error(lang("余额不足"), url('index/kefu'));
        }
        try {
            if($grab_order_mode == 2){     //付款修改用户订单状态为已付款，同时生成cp订单
                if ($flag == 'A'){//发起拼单的付款
                    $order = [
                        'attr_id' => $attr['id'] ?? 0,
                        'shop_price' => $shop_price,
                        'retail_price' => $retail_price,
                        'return_bonus' => $return_bonus,
                        'paid_status' => 2,                 //支付状态改为2-已支付
                    ];
                    $where = [
                        'id' => $order_id,
                    ];
                    $res = OrderModel::update($order, $where);
                    if ($res) {
                        UserService::addPurchase($this->userid, $retail_price, $order_id);
                    }
                }else if ($flag == 'B'){//CP参与拼单
                    $order = [
                        'order_sn' => genOrderSN('', 'PD'),
                        'is_xuni' => $this->userInfo['is_xuni'],
                        'user_id' => $this->userInfo['id'],
                        'level_id' => $this->userInfo['level_id'],
                        'parent_id' => $order_id,
                        'goods_id' => $goods['goods_id'],
                        'attr_id' => $attr['id'] ?? 0,
                        'shop_price' => $shop_price,
                        'retail_price' => $retail_price,
                        'return_bonus' => $return_bonus,
                        'status' => 1,
                        'paid_status' => 2,
                        'repurchase_status' => 1,
                        'is_demo' => 0,
                    ];
                    $res = OrderModel::create($order);
                    if ($res) {
                        UserService::addPurchase($this->userid, $retail_price, $res->id);
                    }
                    /*if ($order_id > 0) {
                        //删除错误订单（多设置的cp发起的订单）
                        OrderService::delOtherOrder($order_id, $this->userid);
                    }*/
                }
                if ($order_id > 0) {
                    OrderService::completeOrder_cp($order_id, $this->userid);
                }
            }else{
                $order = [
                    'order_sn' => genOrderSN('', 'PD'),
                    'is_xuni' => $this->userInfo['is_xuni'],
                    'user_id' => $this->userInfo['id'],
                    'level_id' => $this->userInfo['level_id'],
                    'parent_id' => $order_id,
                    'goods_id' => $goods['goods_id'],
                    'attr_id' => $attr['id'] ?? 0,
                    'shop_price' => $shop_price,
                    'retail_price' => $retail_price,
                    'return_bonus' => $return_bonus,
                    'status' => 1,
                    'paid_status' => 2,
                    'repurchase_status' => 1,
                    'is_demo' => 0,
                ];
                $res = OrderModel::create($order);
                if ($res) {
                    UserService::addPurchase($this->userid, $retail_price, $res->id);
                }
                if ($order_id > 0) {
                    OrderService::completeOrder($order_id);
                }
            }
        } catch (Exception $exc) {
            return $this->error(lang($exc->getMessage()));
        }
        if ($flag == 'A' || $flag == 'B'){
            return $this->error(lang('付款成功'), url('index/index'));
        }else
            $this->success(lang("付款成功"));
    }

    //系统配单
    public function grab() {
        if (empty($this->userInfo)) {
            return $this->error(lang('请登录'), url('login/index'));
        }

        $grab = GrabOrderModel::where('user_id', $this->userid)->where('status', 0)->find();
        if (!empty($grab)) {
            return $this->result(lang('系统正在为您匹配订单'), 9);
        }

        $grab_order_mode = getConfig('grab_order_mode');
        if ($grab_order_mode == 2) { //cp必须完成拼单-cp共同完成模式
            $order = OrderModel::where('status', 1)
                ->where(function($query) {
                    $query->where('user_id', $this->userid)->whereOr('specify_user_id', $this->userid);
                })->find();
            if (!empty($order)) {   //订单未完成
                return $this->success(lang('匹配成功'), url('goods/info', ['id' => $order['goods_id']]));
            }

            $order_check = OrderModel::where('status', 1)->where('parent_id', 0)
                ->where(function($query) {
                    $query->where('specify_user_id', $this->userInfo['cp_id'])->whereOr('user_id', $this->userInfo['cp_id']);
                })->find();
            if($order_check){
                return $this->result(lang('您的CP可能需要重新设置，因为CP正在和其他用户拼单'), 8);
            }
        }else{
            $order = OrderModel::where('specify_user_id', $this->userid)->where('status', 1)->where('parent_id', 0)->find();
            if (!empty($order)) {
                return $this->success(lang('匹配成功'), url('goods/info', ['id' => $order['goods_id']]));
            }
        }
        
        $lock = userLock($this->userid, 'goods-grab', 10);
        if (!$lock) {
            return $this->error(lang('请勿频繁提交'));
        }
        if ($grab_order_mode == 2) { //cp必须完成拼单-cp共同完成模式
            if (empty($this->userInfo['cp_id'])) {
                return $this->error(lang('您不能个人拼单,必须要有绑定cp'));
            }
        }else{
            $is_must_cp = getConfig('is_must_cp', false, 0);
            if ($is_must_cp && empty($this->userInfo['cp_id'])) {
                return $this->error(lang('您不能个人拼单,必须要有绑定cp'));
            }
        }
//        if ($this->userInfo['pd_num'] == 0){
//            return $this->error(lang('VIP0暂不支持拼单'));
//        }
        $day_order_count = $this->userInfo['pd_num'] > 0 ? $this->userInfo['pd_num'] : getConfig('day_order_count');
        if (intval($day_order_count) > 0) {
            $today_order_count = GrabOrderModel::where('user_id', $this->userid)->whereTime('create_time', 'today')->count();
            if ($today_order_count >= $day_order_count) {
                return $this->error(lang('您今天的拼单次数已经用完了'));
            }
        }

        GrabOrderModel::create([
            'user_id' => $this->userid,
            'status' => 0,
        ]);
        return $this->result(lang('系统正在为您匹配订单'), 9);
    }

}
