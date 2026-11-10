<?php

namespace app\index\controller;

use Carbon\Carbon;
use think\Db;

class Yuebao extends Base {

    // 首页
    public function index() {
        $this->setTitle(lang('余额宝'), 1);
        $yuebao_rate = Db::name('yuebao_rate')->where('status', 1)->order('days', 'DESC')->select();
        foreach ($yuebao_rate as &$value) {
            $value['rate'] = ($value['rate'] / 100);
        }
        $userid = $this->userid;
        $today  = strtotime(date('Y-m-d'));
        // 账户信息
        // 计算余额宝利息
        $yeb = Db::name('yuebao')->where(['user_id' => $userid, 'status'=> 1])->select();
        foreach ($yeb as $k => $val) {
            $days = $val['days'];
            if ($days <= 0) {
                $rate = ($val['rate'] / 10000);
            }else{
                $rate = ($val['rate'] / 10000 / $days);
            }
            $create_time = $val['create_time'];
            $create_day  = strtotime(date('Y-m-d 23:59:59', $create_time)) + 1;
            $days_cha = floor(($today - $create_day) / (86400));
            if ($days > 0) {
                $max_time = $create_day + ((1 + $days) * 86400);
            }else{
                $max_time = 0;
            }
            $money = $val['money'];
            $gray = $val['gray'];
            $yuebao_log = [];
            for ($i = 1; $i <= $days_cha; $i++) { 
                // 返利时间
                $get_time = $create_day + $i * 86400;
                if ($max_time > 0 && $get_time >= $max_time) {
                    continue;
                }
                $res = Db::name('yuebao_log')->where(['user_id' => $userid, 'rec_id' => $val['id'], 'get_time' => $get_time])->find();
                if($res){
                    continue;
                }
                // 当天的佣金
                $newgray = sprintf4f($money * $rate);
                $money += $newgray;
                $gray  += $newgray;
                $data = [
                    'rec_id'      => $val['id'],
                    'user_id'     => $userid,
                    'gray'        => $newgray,
                    'get_time'    => $get_time,
                    'create_time' => time()
                ];
                $yuebao_log[] = $data;
            }
            //更新收益
            if ($money != $val['money']) {
                Db::name('yuebao')->where(['id' => $val['id'],'user_id' => $userid])->update(['money' => sprintf4f($money), 'gray' => sprintf4f($gray)]);
            }
            if (isArrayAndNotEmpty($yuebao_log)) {
                Db::name('yuebao_log')->insertAll($yuebao_log);
            }
        }
        // 总资产
        $total = $this->userInfo['user_money'];
        // 余额宝金额
        $yeb_total = Db::name('yuebao')->where('user_id', $userid)->where('status', 1)->sum('principal');
        // 累计收益
        $cumulative_income = Db::name('yuebao')->where('user_id', $userid)->where('status', 2)->sum('gray');
        // 预计收益
        $expected_return = Db::name('yuebao')->where('user_id', $userid)->where('status', 1)->sum('gray');
        $this->assign([
            'lists' => $yuebao_rate,
            'total' => $total,
            'yeb_total' => i18n_number_format($yeb_total),
            'cumulative_income' => i18n_number_format($cumulative_income),
            'expected_return' => i18n_number_format($expected_return),
            'symbol' => i18n_symbol(),
        ]);
        return $this->fetch();
    }

    // 去充值
    public function doPay() {
        // userId
        $userid = session('userid', '', 'index');
        $type = $this->request->param('type/d', 1);
        $money = $this->request->param('money/d', 0);
        if (!$userid || !$type || !$money) {
            return json(msg(-1, '', lang('无效操作')));
        }
        $info = Db::name('yuebao_rate')->where(['status' => 1, 'id' => $type])->find();
        if (!$info || intval($money) <= 0) {
            return json(msg(-1, '', lang('无效操作')));
        }
        if ($this->userInfo['user_money'] < $money) {
            return json(msg(-1, '', lang('抱歉您的余额不足')));
        }
        if ($info['min_amount'] > 0 && $money < $info['min_amount']) {
            return json(msg(-1, '', lang('存入金额不能低于%d', [$info['min_amount']])));
        }
        if ($info['max_amount'] > 0 && $money > $info['max_amount']) {
            return json(msg(-1, '', lang('存入金额不能高于%d', [$info['max_amount']])));
        }
        $gray = sprintf4f(intval($money) * $info['rate'] / 10000);
        if ($gray <= 0) {
            return json(msg(-1, '', lang('您存入的金额不够产生收益')));
        }
        $data = [
            'user_id' => $userid,
            'money' => intval($money),
            'principal' => intval($money),
            'rate' => $info['rate'],
            'redemption_fee' => $info['redemption_fee'],
            'days' => $info['days'],
            'gray' => 0,
            'create_time' => time(),
            'is_xuni' => $this->userInfo['is_xuni'],
            'is_demo' => $this->userInfo['is_demo']
        ];
        $ret = Db::name('yuebao')->insert($data);
        if ($ret) {
            //存入余额宝触发规则
            triggerActivityTask([
                'user_id' => $this->userid,
                'trigger_type' => 6,
                'type' => 6,
            ]);
            addMoneyLog($userid, (-1 * intval($money)), 5, '', 1);
            checkLevel($userid); // 会员升级
            return json(msg(200, '', lang('存入成功')));
        } else {
            return json(msg(-1, '', lang('存入失败')));
        }
    }

    // 记录
    public function lists() {
        $this->setTitle(lang('余额宝存取记录'), 1, '', 1);
        $userid = session('userid', '', 'index');
        if (request()->isAjax()) {
            $status = input('status/d') ? input('status/d') : 1;
            $page = input('page/d') ? input('page/d') : 1;   // 页数
            $pageSize = input('pageSize/d') ? input('pageSize/d') : 10;  // 每页条数
            $offset = ($page - 1) * $pageSize;
            $limit = $pageSize;
            $lists = Db::name('yuebao')->where(['user_id' => $userid, 'status' => $status])->limit($offset, $limit)->order('id DESC')->select();
            foreach ($lists as $k => $val) {
                $val['status_str'] = config('yuebao_status')[$val['status']];
                if ($val['days'] <= 0) {
                    $val['income_time'] = i18n_datetime2str();
                }else{
                    $val['income_time'] = i18n_datetime2str(Carbon::parse(Carbon::createFromTimestamp($val['create_time']))->addDays($val['days'] + 1)->timestamp);
                }
                $val['create_time'] = i18n_datetime2str($val['create_time']);
                $val['gray'] = i18n_currency($val['gray']);
                $val['money'] = i18n_currency($val['money']);
                $val['principal'] = i18n_currency($val['principal']);
                if ($val['out_time']) {
                    $val['out_time'] = i18n_datetime2str($val['out_time']);
                }
                $lists[$k] = $val;
            }
            if ($lists)
                return json(msg(200, $lists, lang('获取成功')));
            else
                return json(msg(0, '', lang('暂无数据')));
        } else {
            return $this->fetch();
        }
    }

    // 取出
    public function cancelOrder() {
        $userid = $this->userid;
        $id = input('id/d');  // 订单ID
        $order = Db::name('yuebao')->where('id', $id)->where('user_id', $this->userid)->lock()->find();
        if (!$order) {
            return json(msg(-1, '', lang('信息有误')));
        }
        if ($order['status'] != 1) {
            return json(msg(-1, '', lang('非法操作')));
        }
        $redemption_fee = 0;
        $update = ['status' => 2, 'out_time' => time()];
        if ($order['days'] > 0) {
            $out_time = Carbon::parse(Carbon::createFromTimestamp($order['create_time']))->addDays($order['days'] + 1);
            $date_interval = Carbon::now()->diff($out_time);
            if ($date_interval->invert != 1) { //未到结算时间
                if ($order['redemption_fee'] > 0) {
                    $redemption_fee = sprintf4f($order['principal'] * $order['redemption_fee'] / 10000);
                    $order['gray'] = 0;
                    $update['gray'] = 0;
                    $order['principal'] = sprintf4f($order['principal'] - $redemption_fee);
                    $update['money'] = $order['principal'];
                }else{
                    $this->error(lang('未到结算时间,无法取出'));
                }
            }
        }
        $ret = Db::name('yuebao')->where('id', $id)->update($update);
        if ($ret) {
            addMoneyLog($userid, $order['principal'], 6, '', 2);
            if ($order['gray'] > 0) {
                addMoneyLog($userid, $order['gray'], 6);
            }
            return json(msg(200, '', lang('取出成功')));
        } else {
            return json(msg(-1, '', lang('取出失败')));
        }
    }

}
