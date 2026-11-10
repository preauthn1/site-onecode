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

use app\admin\model\LevelModel;
use app\admin\model\RechargeModel;
use think\Db;

class Recharge extends Base {

    //用户列表
    public function index() {
        $status = config('recharge_status');
        $type = config('my.pay_type');
        if ($this->request->isAjax() || $this->request->isPost()) {
            $export = $this->request->param("export/d", 0);
            $return = $this->getdata(1, $export === 1);
            if ($export) {
                $header = [
                    "id" => "ID",
                    "car_num" => "收款账号",
                    "true_name" => "真实姓名",
                    "mobile" => "联系电话",
                    "user_note" => "付款类型",
                    "pay_account" => "付款账户",
                    "money" => "金额",
                    "status_text" => "状态",
                    "create_time" => "时间",
                ];
                return genXlsx($header, $return, "银行卡充值记录");
            }
            return json($return);
        }
        $remark = Db::name('recharge')->where("remark", "<>", "")->distinct(true)->column("remark");
        $this->assign([
            'remark' => array_filter($remark),
            'status' => $status,
            'type' => $type
        ]);
        return $this->fetch();
    }

    public function alipay() {
        $status = config('recharge_status');
        $type = config('my.pay_type');
        if ($this->request->isAjax() || $this->request->isPost()) {
            $export = $this->request->param("export/d", 0);
            $return = $this->getdata(2, $export === 1);
            if ($export) {
                $header = [
                    "id" => "ID",
                    "car_num" => "收款账号",
                    "true_name" => "真实姓名",
                    "mobile" => "联系电话",
                    "user_note" => "付款类型",
                    "pay_account" => "付款账户",
                    "money" => "金额",
                    "status_text" => "状态",
                    "create_time" => "时间",
                ];
                return genXlsx($header, $return, "支付宝充值记录");
            }
            return json($return);
        }
        $remark = Db::name('recharge')->where("remark", "<>", "")->distinct(true)->column("remark");
        $this->assign([
            'remark' => array_filter($remark),
            'status' => $status,
            'type' => $type
        ]);
        return $this->fetch();
    }
    
    public function usdt() {
        $status = config('recharge_status');
        $type = config('my.pay_type');
        if ($this->request->isAjax() || $this->request->isPost()) {
            $export = $this->request->param("export/d", 0);
            $return = $this->getdata(4, $export === 1);
            if ($export) {
                $header = [
                    "id" => "ID",
                    "car_num" => "收款账号",
                    "mobile" => "联系电话",
                    "user_note" => "付款类型",
                    "pay_account" => "付款账户",
                    "money" => "金额",
                    "usdt_money" => "USDT金额",
                    "status_text" => "状态",
                    "create_time" => "时间",
                ];
                return genXlsx($header, $return, "USDT充值记录");
            }
            return json($return);
        }
        $remark = Db::name('recharge')->where("remark", "<>", "")->distinct(true)->column("remark");
        $this->assign([
            'remark' => array_filter($remark),
            'status' => $status,
            'type' => $type
        ]);
        return $this->fetch();
    }

    public function third() {
        $status = config('recharge_status');
        $type = config('my.pay_type');
        if ($this->request->isAjax() || $this->request->isPost()) {
            $export = $this->request->param("export/d", 0);
            $return = $this->getdata(3, $export === 1);
            if ($export) {
                $header = [
                    "id" => "ID",
                    "order_id" => "订单号",
                    "channel" => "第三方渠道",
                    "bank_name" => "银行",
                    "mobile" => "联系电话",
                    "money" => "金额",
                    "status_text" => "状态",
                    "create_time" => "时间",
                ];
                return genXlsx($header, $return, "第三方充值记录");
            }
            return json($return);
        }
        $paylist = \app\admin\model\PaymentModel::where('type', 3)->select();
        $remark = Db::name('recharge')->where("remark", "<>", "")->distinct(true)->column("remark");
        $pay = config('pay');
        $this->assign([
            'remark' => array_filter($remark),
            'status' => $status,
            'type' => $type,
            'paylist' => empty($paylist) ? [] : $paylist,
            'pay' => $pay,
        ]);
        return $this->fetch();
    }

    private function getdata($type, $export = 0) {
        $status_text = config('recharge_status');
        $type_text = config('my.pay_type');
        $param = input('param.');
        if (!isset($param['pageNumber'])) {
            $param['pageNumber'] = 1;
        }
        if (!isset($param['pageSize'])) {
            $param['pageSize'] = 10;
        }
        $limit = $param['pageSize'];
        $offset = ($param['pageNumber'] - 1) * $limit;
        $where = ['type' => $type];
        $uid = $this->request->param("uid/d", 0);
        if ($uid > 0) {
            $where['user_id'] = $uid;
        }
        if (isset($param['vuser']) && in_array($param['vuser'], [0, 1])) {
            if ($param['vuser'] == 1) {
                $where['hu.is_xuni'] = 1;
            } else {
                $where['hu.is_xuni'] = ['<>', 1];
            }
        }
        if (!empty($param['searchText'])) {
            $where['hu.mobile|hu.true_name|w.order_id'] = ['like', '%' . $param['searchText'] . '%'];
        }
        $where['w.status'] = ['>', -2];
        if ($param['searchStatus'] != '') {
            $where['w.status'] = $param['searchStatus'];
        }
        if (isset($param['pay_id']) && $param['pay_id'] != '') {
            $where['w.pay_id'] = $param['pay_id'];
        }
        $orderby = 'id DESC';
        if (!empty($param['sortName']) && in_array($param['sortName'], ['money', 'create_time'])) {
            $orderby = "w." . $param['sortName'] . " " . ($param['sortOrder'] == "asc" ? "ASC" : "DESC");
        }
        if (!empty($param['startTime']) && !empty($param['endTime'])) {
            $start_time = strtotime($param['startTime'] . " 00:00:00");
            $end_time = strtotime($param['endTime'] . " 23:59:59");
            $where['w.create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        if (!empty($param['startTime']) && empty($param['endTime'])) {
            $start_time = strtotime($param['startTime'] . " 00:00:00");
            $where['w.create_time'] = ['>=', $start_time];
        }
        if (!empty($param['endTime']) && empty($param['startTime'])) {
            $end_time = strtotime($param['endTime'] . " 23:59:59");
            $where['w.create_time'] = ['<=', $end_time];
        }
        $medol = new RechargeModel();
        if ($export) {
            $selectResult = $medol->getAllByWhere($where, $orderby);
        } else {
            $selectResult = $medol->getLlistsByWhere($where, $offset, $limit, $orderby);
        }
        if ($type == 3) {
            $paylist = \app\admin\model\PaymentModel::where('type', 3)->column('*', 'id');
            $pay = config('pay');
        }
        // 拼装参数
        foreach ($selectResult as $key => $vo) {
            if ($vo['status'] == 1) {
                $buttons = showOperate($this->makeButton($vo['id']));
            } else {
                $buttons = '-';
            }
            if ($vo['type'] == 4) {
                $selectResult[$key]['usdt_money'] = fiatToUsdt($vo['money'], $vo['exchange_rate']);
            }
            $selectResult[$key]['type'] = $type_text[$vo['type']];
            $selectResult[$key]['money'] = i18n_sprintf($vo['money']);
            $selectResult[$key]['status_text'] = $status_text[$vo['status']];
            $selectResult[$key]['operate'] = $buttons;
            $selectResult[$key]['img'] = show_img(toMedia($vo['img']));
            $selectResult[$key]['last_login_ip'] = $vo['last_login_ip'] . "<br>" . ip2location($vo['last_login_ip']);
            if ($vo['ip']) {
                $selectResult[$key]['ip']     = $vo['ip'] . "<br>" . ip2location($vo['ip']);
            }
            if ($type == 3) {
                if (isset($paylist[$vo['pay_id']])) {
                    $selectResult[$key]['channel'] = $pay[$paylist[$vo['pay_id']]['account']]['name'];
                    $selectResult[$key]['bank_name'] = $paylist[$vo['pay_id']]['bank_name'];
                } else {
                    $selectResult[$key]['channel'] = '';
                    $selectResult[$key]['bank_name'] = '';
                }
            }
        }
        if ($export) {
            return $selectResult;
        }
        $return['total'] = $medol->getAllLists($where);  //总数据
        $return['rows'] = $selectResult;

        $model = new RechargeModel();
        $stat = $model->alias('w')
                        ->join('home_user hu', 'w.user_id = hu.id', 'left')
                        ->where($where)->group('status')->field('w.status,sum(w.money) AS total')->select();
        if ($stat) {
            foreach ($stat as $k => $v) {
                $stat[$k]['text'] = $status_text[$v['status']];
                $stat[$k]['total'] = i18n_sprintf($v['total']);
            }
        }
        $return['stat'] = $stat;
        return $return;
    }

    // 审核
    public function checkStatus() {
        $id = input('param.id');
        $status = input('param.status');
        $remark = input('param.remark');
        if (!in_array($status, [2, 3])) {
            return json(msg(-1, '', lang('审核状态有误')));
        }
        if (empty($remark)) {
            return json(msg(-1, '', lang('备注不能为空')));
        }
        $ids = explode(",", $id);
        foreach ($ids as $id) {
            $info = Db::name('recharge')->where('id', $id)->where("status", 1)->lock()->find();
            if (!$info) {
                return json(msg(-1, '', lang('充值信息不存在')));
            }
        }
        foreach ($ids as $id) {
            $info = Db::name('recharge')->where('id', $id)->where("status", 1)->lock()->find();
            // 类型
            $type = $info['type'];
            $money = $info['money'];
            $userid = $info['user_id'];
            $ret = Db::name('recharge')->where(['id' => $id])->where("status", 1)->update(['status' => $status, 'remark' => $remark]);
            if ($status == 2) {
                addMoneyLog($userid, $money, 1);
                $model = new \app\common\model\UserModel();
                $user = $model->getOneUser($userid);
                $level_model = new LevelModel();
                $level = $level_model->order('sort','DESC')->select();
                $rechange = new RechargeModel();
                $total_balance = $rechange->where('user_id',$user->id)->count('money');
                foreach ($level AS $item){
                    if ($item['price']!=0 && $total_balance>=$item['price']){
                        $user->level_id = $item['level_id'];
                        $user->pd_num = $item['dannum'];
                    }
                }
                $user->save();
                insert_operation_log('审核充值:成功', "订单号:{$info['order_id']}");
            } else {
                insert_operation_log('审核充值:失败', "订单号:{$info['order_id']}");
            }
        }
        return json(msg(1, '', lang('操作成功')));
    }

    // 删除用户
    public function rechargeDel() {
        return json(msg(-1, '', lang('操作失败')));
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id) {
        return [
            '审核通过' => [
                'auth' => 'recharge/checkstatus',
                'href' => "javascript:checkStatus(" . $id . ", 2)",
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '审核失败' => [
                'auth' => 'recharge/checkstatus',
                'href' => "javascript:checkStatus(" . $id . ", 3)",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-paste'
            ]
        ];
    }

    public function add() {
        if ($this->request->isAjax()) {
            $user_id = $this->request->param('user_id/s');
            $win = $this->request->param('win/d', 0);
            $money = $this->request->param('money/d');
            $user = \app\admin\model\MemberModel::where('id', $user_id)->find();
            if (!$user) {
                $user = \app\admin\model\MemberModel::where('mobile', $user_id)->find();
            }
            if (!$user) {
                return $this->error('用户信息不存在');
            }
            if ($money <= 0) {
                return $this->error('充值金额有误');
            }
            $userid = $user->id;
            $data = [
                'order_id' => genOrderSN(random(4, 'number'), 'MU'),
                'user_id' => $userid,
                'money' => $money,
                'car_num' => '9999999999',
                'type' => 1,
                'is_xuni' => $user->is_xuni,
                'remark' => '手动补单',
                'status' => 2,
                'create_time' => time()
            ];
            $ret = Db::name('recharge')->insertGetId($data);
            if ($ret) {
                addMoneyLog($userid, $money, 1);
                checkLevel($user['leader1']); // 会员升级
                checkLevel($userid); // 会员升级
                triggerActivityTask([
                    'user_id' => $userid,
                    'recharge_id' => $ret,
                    'type' => 1,
                ]);
                //自己充值触发规则
                triggerActivityTask([
                    'user_id' => $userid,
                    'recharge_id' => $ret,
                    'trigger_type' => 1,
                    'type' => 6,
                ]);
                if ($user['leader1'] > 0) {
                    //下级充值触发规则
                    triggerActivityTask([
                        'from_user_id' => $userid,
                        'user_id' => $user['leader1'],
                        'recharge_id' => $ret,
                        'trigger_type' => 2,
                        'type' => 6,
                    ]);
                    //下级首充触发规则
                    triggerActivityTask([
                        'from_user_id' => $userid,
                        'user_id' => $user['leader1'],
                        'recharge_id' => $ret,
                        'trigger_type' => 7,
                        'type' => 6,
                    ]);
                }
                insert_operation_log('充值:手动补单', "订单号:{$data['order_id']}");
                return $this->success('手动补单成功', url('index'), $win ? 'close' : '');
            } else {
                return $this->error('手动补单失败');
            }
        }
        $user_id = $this->request->param('user_id/d');
        $this->assign('user_id', $user_id);
        return $this->fetch();
    }

}
