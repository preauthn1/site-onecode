<?php

namespace app\index\controller;

use app\common\model\ConfigModel;
use app\common\model\UserModel;
use app\admin\model\RechargeModel;
use think\Controller;
use think\Db;
use think\helper\Str;

class Recharge extends Base {

    // 首页
    public function index() {
        $this->setTitle(lang('充值'));
        $recharge_default_money = array_filter(explode(",", $this->configInfo['recharge_default_money']));
        if (empty($recharge_default_money)) {
            $recharge_default_money = [100,200,300,500,1000,2000,3000,5000,10000];
        }
        $paylist = Db::name('paylist')->where('status', 1)->order('sort ASC')->select();
        $this->assign('paylist', $paylist);
        $this->assign('moneys', $recharge_default_money);
        $this->assign('symbol', i18n_symbol());
        return $this->fetch();
    }

    // 完成付款
    public function doPay() {
        if (isset($this->configInfo['recharge_open']) && $this->configInfo['recharge_open'] == 0) {
            return json(msg(-1, '', lang('充值系统维护中')));
        }
        $pay_account = $this->request->param('pay_account/s', '', 'htmlspecialchars,strip_tags,removeXSS');
        $user_note = $this->request->param('user_note/s', '', 'htmlspecialchars,strip_tags,removeXSS');
        $money = $this->request->param("money/d");
        $pay_id = $this->request->param("pay_id/d");
        $order_id = $this->request->param("order_id/d");
        $car_num = $this->request->param("car_num/s");
        $img = $this->request->param("img/s");
        $type = $this->request->param("type/d");
        if (empty($money)) {
            return json(msg(-1, '', lang('充值金额有误')));
        }
        $recharge_min_money = isset($this->configInfo['recharge_min_money']) ? intval($this->configInfo['recharge_min_money']) : 100;
        if ($recharge_min_money > 0 && $money < $recharge_min_money) {
            return json(msg(-1, '', lang('充值金额不能低于100', [$recharge_min_money])));
        }
        if (!in_array($type, [1, 2, 3, 4])) {
            return json(msg(-1, '', lang('充值方式有误')));
        }
        if (empty($this->userInfo)) {
            return json(msg(-1, '', lang('回话已过期，请重新登录')));
        }
        if ($this->userInfo['is_demo'] == 1) {
            return json(msg(-1, '', lang('测试账号，不能使用该功能')));
        }
        if (empty($pay_account)) {
            return json(msg(-1, '', lang('请填写付款账户')));
        }
        if (strlen($pay_account) > 250) {
            return json(msg(-1, '', lang('付款账户不能超过50个字符串')));
        }
        if (empty($user_note)) {
            return json(msg(-1, '', lang('请填写用户备注')));
        }
        if (strlen($user_note) > 250) {
            return json(msg(-1, '', lang('备注不能超过50个字符串')));
        }
        if ($order_id > 0) {
            $order = RechargeModel::where('id', $order_id)->find();
            if ($order) {
                $order->car_num = $car_num;
                $order->pay_account = $pay_account;
                $order->user_note = $user_note;
                $order->img = $img;
                $order->save();
                return json(msg(200, '', lang('提交成功')));
            }
        }
        $info = Db::name('recharge')->whereTime('create_time', 'today')->where(['type' => $type, 'status' => 1, 'user_id' => $this->userid])->find();
        if ($info) {
            return json(msg(-1, '', lang('当前有1笔充值订单未完成，请查看充值记录完成或撤销')));
        }
        $payinfo = Db::name('paylist')->where('id', $pay_id)->where('status', 1)->find();
        if (empty($payinfo)) {
            return json(msg(-1, '', lang('收款渠道有误，请联系客服')));
        }
        if (isset($payinfo['min_money']) && $payinfo['min_money'] > 0 && $money < $payinfo['min_money']) {
            return json(msg(-1, '', lang('充值金额不能低于100', [$payinfo['min_money']])));
        }
        if (isset($payinfo['max_money']) && $payinfo['max_money'] > 0 && $money > $payinfo['max_money']) {
            return json(msg(-1, '', lang('充值金额不能高于%d', [$payinfo['max_money']])));
        }
        $money2 = $money;
        if ($type == 2 && $money2 > 5000) {
//            return json(msg(-1, '', lang('充值金额大于5000元，请转账到银行卡')));
        }
        $data = [
            'order_id' => genOrderSN(random(4, 'number'), 'OF'),
            'exchange_rate' => $type == 4 ? getConfig('exchange_rate') : 1,
            'user_id' => $this->userid,
            'is_xuni' => $this->userInfo['is_xuni'],
            'money' => $money2,
            'car_num' => $payinfo['account'],
            'pay_id' => $pay_id,
            'type' => $type,
            'pay_account' => $pay_account,
            'user_note' => $user_note,
            'img' => $img,
            'ip' => getip(),
            'create_time' => time()
        ];
        $ret = Db::name('recharge')->insert($data);
        if ($ret) {
            return json(msg(200, '', lang('提交成功')));
        } else {
            return json(msg(-1, '', lang('提交失败')));
        }
    }

    // 账号信息
    public function pay() {
        if (isset($this->configInfo['recharge_open']) && $this->configInfo['recharge_open'] == 0) {
            $this->error(lang('充值系统维护中'));
        }
        $this->setTitle(lang('充值订单 待支付'));
        $money = $this->request->param("money/d");
        $type = $this->request->param("type/d");
        $pay_id = $this->request->param("pay_id/d");
        if (empty($money)) {
            $this->error(lang('请选择充值金额'));
        }
        $recharge_min_money = isset($this->configInfo['recharge_min_money']) ? intval($this->configInfo['recharge_min_money']) : 100;
        if ($recharge_min_money > 0 && $money < $recharge_min_money) {
            $this->error(lang('充值金额不能低于100', [$recharge_min_money]));
        }
        if (!in_array($type, [1, 2, 3, 4])) {
            $this->error(lang('充值方式有误'));
        }
        $config = new ConfigModel();
        $info = $config->getConfig();
        $money2 = $money;
        $where = ['type' => $type, 'status' => 1];
        if ($pay_id > 0) {
            $where['id'] = $pay_id;
        }
        $payinfo = Db::name('paylist')->where($where)->orderRaw("rand()")->find();
        if (empty($payinfo)) {
            $this->error(lang('没有收款渠道，请联系客服'));
        }
        
        if (isset($payinfo['min_money']) && $payinfo['min_money'] > 0 && $money < $payinfo['min_money']) {
            $this->error(lang('充值金额不能低于100', [$payinfo['min_money']]));
        }
        if (isset($payinfo['max_money']) && $payinfo['max_money'] > 0 && $money > 50000) {
            $this->error(lang('充值金额不能高于50000'));
        }
        if ($type == 3) { //第三方支付
//            if ($this->userInfo['true_name'] == '' || $this->userInfo['bank_number'] == '') {
//                $this->error(lang('请先绑定银行卡信息'), url('user/bind_card'));
//            }
            $data = [
                'user_id' => $this->userid,
                'money' => $money2,
                'car_num' => $payinfo['account'],
                'pay_id' => $pay_id,
                'type' => $type,
            ];
            $className = "\\pay\\" . Str::studly($payinfo['account']);
            $pays = config("pay");
            if (!isset($pays[$payinfo['account']]) || !class_exists($className)) {
                $this->error(lang('没有收款渠道，请联系客服'));
            }
            $config = $pays[$payinfo['account']];
            $config['pay_type'] = $payinfo['name'];
            if ($payinfo['bank_address']) {
                $config['pay_receive_bankcode'] = $payinfo['bank_address'];
            }else{
                $config['pay_receive_bankcode'] = '';
            }
            $config['notify_url'] = getWebUrl() . "/notify/pay/{$pay_id}";
            $config['jump_url'] = getWebUrl() . "/recharge/lists";
            $config['real_name'] = $this->userInfo['true_name'];
            $config['mobile'] = $this->userInfo['mobile'];
            $config['pay_attach'] = [
                'logo' => $this->configInfo['logo'],
                'name' => $this->configInfo['title'],
            ];
            $order = RechargeModel::create($data);
            try {
                $pay = new $className($config);
                $result = $pay->pay($order);
                if (isArrayAndNotEmpty($result)) {
                    if (isset($result['bank_name'])) {
                        $payinfo['bank_name'] = $result['bank_name'];
                    }
                    $payinfo['account'] = $result['vaNumber'];
                    $type = 1;
                    $this->assign([
                        'order' => $order,
                    ]);
                }else{
                    return $result;
                }
            } catch (\Exception $exc) {
                \think\Log::write($exc->getMessage(), 'error', true);
                \think\Log::write($exc->getTraceAsString(), 'error', true);
                $order->remark = $exc->getMessage();
                $order->status = 3;
                $order->save();
                $this->error(lang($exc->getMessage()));
            }
        }
        $this->assign([
            'info' => $info,
            'type' => $type,
            'payinfo' => $payinfo,
            'money' => $money2,
            'money2' => $money,
            'time' => i18n_datetime2str()
        ]);
        if ($type == 4) {
            $this->assign([
                'money' => fiatToUsdt($money2),
            ]);
            return $this->fetch('usdt');
        }
        return $this->fetch();
    }

    // 充值记录
    public function lists() {
        $this->setTitle(lang('充值记录'));
        $userid = session('userid', '', 'index');

        $start_time = strtotime('-7 days');
        $end_time = strtotime('+1 days');
        if (request()->isAjax()) {
            $page = $this->request->param('page/d', 1);   // 页数
            $limit = $this->request->param('pageSize/d', 10);   // 页数
            $offset = ($page >= 1 ? $page - 1 : 0) * $limit;
            $where['user_id'] = $userid;
//            $where['create_time'] = ['between', [$start_time, $end_time]];
//            $where['type'] = ['<>', 3];
            $lists = [];
            if (empty($lists)) {
                $lists = Db::name('recharge')->where($where)->limit($offset, $limit)->order('id DESC')->select();
            }
            $recharge_status = config('recharge_status');
            foreach ($lists as $k => $val) {
                $val['create_time'] = i18n_datetime2str($val['create_time']);
                $val['remark'] = isset($val['remark']) && $val['remark'] ? lang($val['remark']) : '-';
                $val['status_str'] = isset($recharge_status[$val['status']]) ? lang($recharge_status[$val['status']]) : '';
                if ($val['status'] == 1 && $val['type'] != 3) {
                    $val['is_btn'] = 'block';
                }else{
                    $val['is_btn'] = 'none';
                }
                $lists[$k] = $val;
            }
            if ($lists) {
                $content = $this->fetch('item', ['lists' => $lists]);
                $this->success('ok', '', $content);
            } else {
                $this->error(lang('暂无数据'));
            }
        }
        $this->assign([
            'status' => config('recharge_status'),
            'start_time' => date('Y-m-d', $start_time),
            'end_time' => date('Y-m-d', ($end_time - 36 * 2400)),
            'isHtml5Plus' => false
        ]);
        return $this->fetch();
    }
    
    // 撤销充值订单
    public function cancelOrder() {
        $id = $this->request->param('id/d');  // 订单ID
        $order = Db::name('recharge')->where('user_id', $this->userid)->where('id', $id)->where('type', "<>", 3)->find();
        if (!$order) {
            $this->error(lang('信息有误'));
        }
        if ($order['status'] != 1) {
            $this->error(lang('非法操作'));
        }
        $ret = Db::name('recharge')->where('id', $id)->update(['status' => 3]);
        if ($ret !== false) {
            $this->success(lang('操作成功'));
        } else {
            $this->error(lang('操作失败'));
        }
    }

}
