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

use think\Db;
use think\Cache;
use app\admin\model\MemberModel;
use app\admin\model\RechargeModel;
use app\admin\model\WithdrawModel;
use app\admin\model\MoneyModel;
use app\common\model\OrderModel;
use app\admin\model\DayReportModel;
use Carbon\Carbon;
class Stat extends Base {

    public function index() {

        if (request()->isAjax()) {
            $param = input('param.');
            $start = $param['start'];
            $end = $param['end'];
            $mobile = $param['mobile'];
            $where = null;
            $where2 = null;
            $where3 = null;
            $userid = [];
            if ($mobile) {
                $userids = getTeamUseridByMobile($mobile);
                $userid = getUseridByMobile($mobile);
                if ($userid > 0) {
                    $sub = Db::name('home_user')->where('is_xuni', 0)->whereLike("tree_id", "%/{$userid}/%")->field("id")->buildSql();
                    $sub2 = Db::name('home_user')->where('is_xuni', 0)->where("leader1", $userid)->field("id")->buildSql();
                    $where = "user_id IN({$sub})";
                    $where2 = "id IN({$sub})";
                    $where3 = "user_id IN({$sub2})";
                }
            }
            //充值金额统计
            $recharge = Db::name('recharge')
                    ->alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)
                    ->where("r.create_time", ">=", strtotime($start . " 00:00:00"))
                    ->where("r.create_time", "<=", strtotime($end . " 23:59:59"))
                    ->where($where)
                    ->field("r.status, sum(r.money) AS total")->group('r.status')
                    ->column("SUM(r.money)", "r.status");
            //提现金额统计
            $withdrawals = Db::name('withdrawals')
                    ->alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)
                    ->where("r.create_time", ">=", strtotime($start . " 00:00:00"))
                    ->where("r.create_time", "<=", strtotime($end . " 23:59:59"))
                    ->where($where)
                    ->field("r.status, sum(money) AS total")->group('r.status')
                    ->column("SUM(r.money)", "r.status");
            //注册人数
            $reg_count = Db::name('home_user')
                    ->where('is_xuni', 0)
                    ->where("create_time", ">=", strtotime($start . " 00:00:00"))
                    ->where("create_time", "<=", strtotime($end . " 23:59:59"))
                    ->where($where2)
                    ->count();
            //充值人数
            $recharge_count = Db::name('recharge')
                    ->alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)
                    ->where("r.create_time", ">=", strtotime($start . " 00:00:00"))
                    ->where("r.create_time", "<=", strtotime($end . " 23:59:59"))
                    ->where($where)
                    ->where('r.status', 2)
                    ->count('distinct user_id');
            //提现人数
            $withdraw_count = Db::name('withdrawals')
                    ->alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)
                    ->where("r.create_time", ">=", strtotime($start . " 00:00:00"))
                    ->where("r.create_time", "<=", strtotime($end . " 23:59:59"))
                    ->where($where)
                    ->where('r.status', 2)
                    ->count('distinct user_id');

            $zs_recharge = 0;
            $zs_withdrawals = 0;
            $zs_recharge_count = 0;
            $zs_withdraw_count = 0;
            //查询直属
            if (!empty($userid)) {
                //充值金额统计
                $zs_recharge = Db::name('recharge')
                        ->alias('r')->join('home_user u', 'u.id=r.user_id')
                        ->where('u.is_xuni', 0)
                        ->where("r.create_time", ">=", strtotime($start . " 00:00:00"))
                        ->where("r.create_time", "<=", strtotime($end . " 23:59:59"))
                        ->where($where3)
                        ->field("r.status, sum(r.money) AS total")->group('r.status')
                        ->column("SUM(r.money)", "r.status");
                //提现金额统计
                $zs_withdrawals = Db::name('withdrawals')
                        ->alias('r')->join('home_user u', 'u.id=r.user_id')
                        ->where('u.is_xuni', 0)
                        ->where("r.create_time", ">=", strtotime($start . " 00:00:00"))
                        ->where("r.create_time", "<=", strtotime($end . " 23:59:59"))
                        ->where($where3)
                        ->field("r.status, sum(r.money) AS total")->group('r.status')
                        ->column("SUM(r.money)", "r.status");
                //充值人数
                $zs_recharge_count = Db::name('recharge')
                        ->alias('r')->join('home_user u', 'u.id=r.user_id')
                        ->where('u.is_xuni', 0)
                        ->where("r.create_time", ">=", strtotime($start . " 00:00:00"))
                        ->where("r.create_time", "<=", strtotime($end . " 23:59:59"))
                        ->where($where3)
                        ->where('r.status', 2)
                        ->count('distinct user_id');
                //提现人数
                $zs_withdraw_count = Db::name('withdrawals')
                        ->alias('r')->join('home_user u', 'u.id=r.user_id')
                        ->where('u.is_xuni', 0)
                        ->where("r.create_time", ">=", strtotime($start . " 00:00:00"))
                        ->where("r.create_time", "<=", strtotime($end . " 23:59:59"))
                        ->where($where3)
                        ->where('r.status', 2)
                        ->count('distinct user_id');
            }
            return $this->success("ok", "", [
                        'recharge' => $recharge,
                        'withdraw' => $withdrawals,
                        'withdraw_count' => intval($withdraw_count),
                        'recharge_count' => intval($recharge_count),
                        'reg_count' => intval($reg_count),
                        'zs' => [
                            'recharge' => $zs_recharge,
                            'withdraw' => $zs_withdrawals,
                            'withdraw_count' => intval($zs_withdraw_count),
                            'recharge_count' => intval($zs_recharge_count),
                        ]
            ]);
        }

        return $this->fetch();
    }
    
    public function iprank() {
        if ($this->request->isAjax()) {
            $param = $this->request->param();
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $field = $param['ip_type'] == 0 ? 'ip' : 'last_login_ip';
            $where = [];
            if (isset($param['startTime']) && $param['startTime'] && isset($param['endTime']) && $param['endTime']) {
                $start_time = strtotime($param['startTime'] . " 00:00:00");
                $end_time = strtotime($param['endTime'] . " 23:59:59");
                $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
            }

            $list = MemberModel::where($where)->field("{$field}, COUNT(id) AS total")
                    ->whereNotIn($field, ['', '0.0.0.0'])
                    ->group($field)->order('total DESC')->limit($offset, $limit)->select();
            $total = MemberModel::where($where)->whereNotIn($field, ['', '0.0.0.0'])->count($field);
            
            foreach ($list as &$value) {
                $value['ip_address'] = $value[$field];
                $value['ip_local'] = ip2location($value[$field]);
            }
            unset($value);
            $return = [];
            $return['total'] = $total;  //总数据
            $return['rows'] = $list;
            return $return;
        }
        return $this->fetch();
    }

    public function recharge() {
        if ($this->request->isAjax()) {
            $param = $this->request->param();
            $where = [];
            if (!isset($param['startTime']) || !isset($param['endTime'])) {
                $param['startTime'] = getDateStr();
                $param['endTime'] = getDateStr();
            }
            $start_time = strtotime($param['startTime'] . " 00:00:00");
            $end_time = strtotime($param['endTime'] . " 23:59:59");
            $where['r.create_time'] = ['BETWEEN', [$start_time, $end_time]];

            $model = new \app\admin\model\PaymentModel();
            $list = $model->order('sort ASC, type ASC')->select();
            $list[] = [
                'id' => 0,
                'name' => '手动上分'
            ];
            $order_success_counts = Db::name('recharge')
                    ->alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->where("r.status", 2)->group("r.pay_id")->where($where)->column("count(r.id)", "r.pay_id");
            $order_total_counts = Db::name('recharge')
                    ->alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->group("r.pay_id")->where($where)->column("count(r.id)", "r.pay_id");

            $PayAmounts = Db::name('recharge')
                    ->alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->group("r.pay_id")->where("r.status", 2)->where($where)->column("sum(r.money)", "pay_id");
            $zPayAmount = $zOrderSuccess = $zOrderTotal = 0;
            foreach ($list as &$value) {
                $value['PayAmount'] = (isset($PayAmounts[$value['id']]) ? $PayAmounts[$value['id']] : 0);
                $zPayAmount += $value['PayAmount'];
                //付款成功订单笔数
                $value['OrderSuccess'] = $order_success_count = isset($order_success_counts[$value['id']]) ? $order_success_counts[$value['id']] : 0;
                $zOrderSuccess += $value['OrderSuccess'];
                //订单总笔数
                $value['OrderTotal'] = $order_total_count = isset($order_total_counts[$value['id']]) ? $order_total_counts[$value['id']] : 0;
                $zOrderTotal += $value['OrderTotal'];

                $value['PayRatio'] = $order_total_count > 0 ? (sprintf("%.2f", $order_success_count / $order_total_count) * 100 ) . "%" : "-";
                if ($param['startTime'] == $param['endTime']) {
                    $value['Date'] = "{$param['startTime']}";
                } else {
                    $value['Date'] = "{$param['startTime']} 至 {$param['endTime']}";
                }
            }
            unset($value);
            $zdata = [];
            $zdata['name'] = "总计";
            $zdata['PayAmount'] = $zPayAmount . "元";
            $zdata['OrderSuccess'] = $zOrderSuccess;
            $zdata['OrderTotal'] = $zOrderTotal;
            $zdata['PayRatio'] = $zOrderTotal > 0 ? (sprintf("%.2f", $zOrderSuccess / $zOrderTotal) * 100 ) . "%" : "-";
            $zdata['Date'] = "-";
            $list[] = $zdata;
            $return = [];
            $return['total'] = count($list);  //总数据
            $return['rows'] = $list;
            return $return;
        }
        return $this->fetch();
    }

    public function rechargedays() {
        if ($this->request->isAjax()) {
            $param = $this->request->param();
            $where = [];
            if (!isset($param['startTime']) || !isset($param['endTime'])) {
                $param['startTime'] = getDateStr(strtotime("-7 days"));
                $param['endTime'] = getDateStr();
            }
            $start_time = strtotime($param['startTime'] . " 00:00:00");
            $end_time = strtotime($param['endTime'] . " 23:59:59");
            $map = [];
            if ($param['pay_id']) {
                $map['pay_id'] = $param['pay_id'];
            }
            $list = [];
            for($i = $start_time; $i <= $end_time; $i += 86400) {
                $where = [
                    'r.create_time' => [
                        "between time", [getDateStr($i) . " 00:00:00", getDateStr($i) . " 23:59:59"]
                    ]
                ];
                $row['PayAmount'] = Db::name('recharge')
                    ->alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->where("r.status", "2")->where($map)->where($where)->sum("r.money");
                //付款成功订单笔数
                $row['OrderSuccess'] = $order_success_count = Db::name('recharge')
                    ->alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->where($map)->where("r.status", "2")->where($where)->count();
                //订单总笔数
                $row['OrderTotal'] = $order_total_count = Db::name('recharge')
                    ->alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->where($map)->where($where)->count();
                $row['PayRatio'] = $order_total_count > 0 ? (sprintf("%.2f", $order_success_count/$order_total_count) * 100 ) . "%" : "-";
                $row['Date'] = getDateStr($i);
                $list[] = $row;
            }
            $return = [];
            $return['total'] = count($list);  //总数据
            $return['rows'] = $list;
            return $return;
        }
        $model = new \app\admin\model\PaymentModel();
        $pay_list = $model->order('sort ASC, type ASC')->select();
        $this->assign("pay_list", $pay_list);
        return $this->fetch();
    }
    
    public function reg() {
        return $this->fetch();
    }
    
    public function reg_report() {
        $startTime = $this->request->param('startTime/s');
        $endTime = $this->request->param('endTime/s');
        if (empty($startTime)) {
            $startTime = getDateStr(strtotime("-7 days"));
        }
        if (empty($endTime) || strtotime($endTime) >= strtotime(getDateStr(strtotime("+1 days")))) {
            $endTime = getDateStr();
        }
        $st = strtotime($startTime . " 00:00:00");
        $et = strtotime($endTime . " 23:59:59");
        $list = [];
        $redis = Cache::store('redis');
        $yesterday_time = strtotime(timeToStr(strtotime('-1 days'), "Y-m-d 23:59:59"));
        for($i=$st; $i <= $et; $i+=86400) {
            $date = getDateStr($i);
            $redis_key = "reg_report:{$date}";
            if ($redis->has($redis_key)) {
                $row = $redis->get($redis_key);
            }else{
                $where = [$date . " 00:00:00", $date . " 23:59:59"];
                $row = [];
                $row['date'] = $date;
                $row['reg'] = $this->getTotalReg($where);
                $row['recharge'] = $this->getTotalRechargeUsers($where);
                $row['no_recharge'] = $row['reg'] - $row['recharge'];
                if ($i <= $yesterday_time) { //缓存今天之前的数据
                    $redis->set($redis_key, $row, 7 * 86400);
                }
            }
            $list[] = $row;
        }
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $this->assign('list', $list);
        return $this->fetch();
    }
    
    public function reg_report_day() {
        if ($this->request->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $date = $param['date'];
            if (empty($date)) {
                $date = getDateStr();
            }
            $where = [$date . " 00:00:00", $date . " 23:59:59"];
            $result = Db::name('home_user')
                    ->whereTime('create_time', 'between', $where)
                    ->where('is_xuni', 0)
                    ->field("id, true_name,mobile,create_time")
                    ->limit($offset, $limit)->order('id desc')->select();
            $total = Db::name('home_user')->where('is_xuni', 0)->whereTime('create_time', 'between', $where)->count();
            foreach ($result as $key => $vo) {
                $result[$key]['create_time'] = timeToStr($vo['create_time']);
                $result[$key]['recharge'] = Db::name('recharge')->whereTime('create_time', $where)->where(['user_id' => $vo['id'], 'status' => 2])->sum('money');
            }
            $return['total'] = $total;  //总数据
            $return['rows'] = $result;
            return json($return);
        }
        $date = $this->request->param('date/s');
        if (empty($date)) {
            $date = getDateStr();
        }
        $this->assign('date', $date);
        return $this->fetch();
    }
    public function day_report() {
        $act = $this->request->param('act/s');
        if ($act == 'query') {
            $date = $this->request->param('date/s');
            $key = $this->request->param('key/s');
            $model = new DayReportModel;
            $result = $model->stat($date, true, $key);
            $this->success('ok', '', $result);
        }
        $update = $this->request->param('update/s');
        $startTime = $this->request->param('startTime/s');
        $endTime = $this->request->param('endTime/s');
        if (empty($startTime)) {
            $startTime = getDateStr(strtotime("-7 days"));
        }
        if (empty($endTime) || strtotime($endTime) >= strtotime(getDateStr(strtotime("+1 days")))) {
            $endTime = getDateStr();
        }
        $date_interval = Carbon::parse($startTime)->diff(Carbon::parse($endTime));
        if ($date_interval->days > 31) {
            $this->error("查询间隔天数请勿超过31天");
        }
        $st = strtotime($startTime . " 00:00:00");
        $et = strtotime($endTime . " 23:59:59");
        $model = new DayReportModel;
        $list = [];
        $today = getDateStr();
        for($i=$st; $i <= $et; $i+=86400) {
            $date = getDateStr($i);
            $row = $model->stat($date, $date == $today || $date == $update);
            $list[] = $row;
        }
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $this->assign('list', $list);
        return $this->fetch();
    }
    public function total() {
        $clear = $this->request->param('clear/d', 0);
        $redis_key = "stat_total:" . date("Ymd");
        $redis = Cache::store('redis');
        if ($redis->has($redis_key) && $clear == 0) {
            $data = $redis->get($redis_key);
        }else{
            //用户统计
            $real_today_reg = MemberModel::whereTime('create_time', 'today')->where('is_xuni', 0)->count();
            $xuni_today_reg = MemberModel::whereTime('create_time', 'today')->where('is_xuni', 1)->count();
            $real_month_reg = MemberModel::whereTime('create_time', 'month')->where('is_xuni', 0)->count();
            $xuni_month_reg = MemberModel::whereTime('create_time', 'month')->where('is_xuni', 1)->count();
            $real_last_month_reg = MemberModel::whereTime('create_time', 'last month')->where('is_xuni', 0)->count();
            $xuni_last_month_reg = MemberModel::whereTime('create_time', 'last month')->where('is_xuni', 1)->count();
            $real_reg = MemberModel::where('is_xuni', 0)->count();
            $xuni_reg = MemberModel::where('is_xuni', 1)->count();
            $status_1_reg = MemberModel::where('status', 1)->count();
            $status_2_reg = MemberModel::where('status', 2)->count();
            $real_user_money = MemberModel::where('is_xuni', 0)->sum('user_money');
            $xuni_user_money = MemberModel::where('is_xuni', 1)->sum('user_money');
        
            //数据统计
            $today = [
                'recharge' => $this->getTotalRecharge('today'),
                'withdraw' => $this->getTotalWithdraw('today'),
                'difference' => 0,
                'order_price' => $this->getTotalOrder('today', 'retail_price'),
                'order_gray' => $this->getTotalOrder('today', 'return_bonus'),
                'rebate' => $this->getTotalMoneyLog('today'),
                'handsel' => $this->getTotalMoneyLog('today', 0),
            ];
            $today['difference'] = i18n_sprintf($today['recharge'] - $today['withdraw']);
            $yesterday = [
                'recharge' => $this->getTotalRecharge('yesterday'),
                'withdraw' => $this->getTotalWithdraw('yesterday'),
                'difference' => 0,
                'order_price' => $this->getTotalOrder('yesterday', 'retail_price'),
                'order_gray' => $this->getTotalOrder('yesterday', 'return_bonus'),
                'rebate' => $this->getTotalMoneyLog('yesterday'),
                'handsel' => $this->getTotalMoneyLog('yesterday', 0),
            ];
            $yesterday['difference'] = i18n_sprintf($yesterday['recharge'] - $yesterday['withdraw']);
            $month = [
                'recharge' => $this->getTotalRecharge('month'),
                'withdraw' => $this->getTotalWithdraw('month'),
                'difference' => 0,
                'order_price' => $this->getTotalOrder('month', 'retail_price'),
                'order_gray' => $this->getTotalOrder('month', 'return_bonus'),
                'rebate' => $this->getTotalMoneyLog('month'),
                'handsel' => $this->getTotalMoneyLog('month', 0),
            ];
            $month['difference'] = i18n_sprintf($month['recharge'] - $month['withdraw']);
            $last_month = [
                'recharge' => $this->getTotalRecharge('last month'),
                'withdraw' => $this->getTotalWithdraw('last month'),
                'difference' => 0,
                'order_price' => $this->getTotalOrder('last month', 'retail_price'),
                'order_gray' => $this->getTotalOrder('last month', 'return_bonus'),
                'rebate' => $this->getTotalMoneyLog('last month'),
                'handsel' => $this->getTotalMoneyLog('last month', 0),
            ];
            $last_month['difference'] = i18n_sprintf($last_month['recharge'] - $last_month['withdraw']);
            $data = [
                'real_today_reg' => $real_today_reg,
                'xuni_today_reg' => $xuni_today_reg,
                'real_month_reg' => $real_month_reg,
                'xuni_month_reg' => $xuni_month_reg,
                'real_last_month_reg' => $real_last_month_reg,
                'xuni_last_month_reg' => $xuni_last_month_reg,
                'real_reg' => $real_reg,
                'xuni_reg' => $xuni_reg,
                'status_1_reg' => $status_1_reg,
                'status_2_reg' => $status_2_reg,
                'real_user_money' => i18n_sprintf($real_user_money),
                'xuni_user_money' => i18n_sprintf($xuni_user_money),
                'today' => $today,
                'yesterday' => $yesterday,
                'month' => $month,
                'last_month' => $last_month,
            ];
            $redis->set($redis_key, $data, 600);
        }
        
        $this->assign($data);
        return $this->fetch();
    }
    
    private function getTotalReg($time = 'today') {
        $model = new MemberModel;
        if (is_array($time)) {
            $model = $model->whereTime('create_time', 'between', $time);
        }else{
            $model = $model->whereTime('create_time', $time);
        }
        $result = $model->where('is_xuni', 0)->count();
        return intval($result);
    }
    /**
     * 获取当天充值用户数
     * @param type $time
     * @return type
     */
    private function getTotalRechargeUsers($time = 'today') {
        $model = new RechargeModel;
        if (is_array($time)) {
            $model = $model->whereTime('create_time', 'between', $time);
        }else{
            $model = $model->whereTime('create_time', $time);
        }
        $result = $model->where('status', 2)->whereIn('user_id', function($query) use($time){
            if (is_array($time)) {
                $query->name('home_user')->whereTime('create_time', 'between', $time)->where('is_xuni', 0)->field('id');
            }else{
                $query->name('home_user')->whereTime('create_time', $time)->where('is_xuni', 0)->field('id');
            }
        })->count('DISTINCT user_id');
        return intval($result);
    }
    
    private function getTotalRecharge($time = 'today') {
        if (in_array($time, ['today', 'yesterday'])) {
            $result = RechargeModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->where('r.status', 2)->whereTime('r.create_time', $time)->sum('r.money');
        }else{
            $result = DayReportModel::where('user_id', 0)->whereTime('stat_date', $time)->sum('recharge');
        }
        return i18n_sprintf($result);
    }
    private function getTotalWithdraw($time = 'today') {
        if (in_array($time, ['today', 'yesterday'])) {
            $result = WithdrawModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->where('r.status', 2)->whereTime('r.check_time', $time)->sum('r.money');
        }else{
            $result = DayReportModel::where('user_id', 0)->whereTime('stat_date', $time)->sum('withdraw');
        }
        return i18n_sprintf($result);
    }
    private function getTotalOrder($time = 'today', $field = 'retail_price') {
        if (in_array($time, ['today', 'yesterday'])) {
            $result = Db::name('order')->where('is_xuni', 0)->where('status', 2)->whereTime('create_time', $time)->sum($field);
        }else{
            $result = DayReportModel::where('user_id', 0)->whereTime('stat_date', $time)->sum($field == 'retail_price' ? 'order_price' : 'order_gray');
        }
        return i18n_sprintf($result);
    }
    private function getTotalMoneyLog($time = 'today', $type = 2) {
        if (in_array($time, ['today', 'yesterday'])) {
            $full_table_name = getMoneyLogTableByDay($time);
            $result = Db::table($full_table_name)->where('type', $type)->where('is_xuni', 0)->whereTime('create_time', $time)->sum('money');
        }else{
            $result = DayReportModel::where('user_id', 0)->whereTime('stat_date', $time)->sum($type == 2 ? 'rebate' : 'manual_money');
        }
        return i18n_sprintf($result);
    }
    public function get_reg_option() {
        $colors = getColors(100);
        $param = $this->request->param();
        
        if (!isset($param['startTime']) || !isset($param['endTime'])) {
            $param['startTime'] = getDateStr();
            $param['endTime'] = getDateStr();
        }
        $start_date = $param['startTime'];
        $end_date = $param['endTime'];
        $start_time = strtotime($param['startTime'] . " 00:00:00");
        $end_time = strtotime($param['endTime'] . " 23:59:59");
        
        $type = '';
        if ($end_time - $start_time <= 86400) {
            $type = 'hour';
            $xAxisName = "时";
            $text = getChineseDate($end_time) . " 24小时内注册统计图";
        } elseif (floor(($end_time - $start_time) / 86400) <= 31 && timeToStr($start_time, "Y") === timeToStr($end_time, "Y")) {
            $type = 'day';
            $xAxisName = "天";
            $text = getChineseDate($start_time) . (($end_time - $start_time) > 86400 ? " 至 " . timeToStr($end_time, "m月d日") : "") ." 注册统计图";
        } elseif (timeToStr($start_time, "Y") === timeToStr($end_time, "Y")) {
            $type = 'months';
            $xAxisName = "月";
            $start_time = getMkTime(timeToStr($start_time, "Y-m-01 00:00:00"));
            $end_time = getMkTime(timeToStr($end_time, "Y-m-" . timeToStr($end_time, "d") . " 23:59:59"));
            $text = timeToStr($start_time, "Y年m月") . " 至 " . timeToStr($end_time, "Y年m月") ." 注册统计图";
        } else {
            $type = 'year';
            $xAxisName = "年";
            $start_time = getMkTime(timeToStr($start_time, "Y-01-01 00:00:00"));
            $end_time = getMkTime(timeToStr($end_time, "Y-12-31 23:59:59"));
            $text = timeToStr($start_time, "Y年") . " 至 " . timeToStr($end_time, "Y年") ." 注册统计图";
        }
        $option = [
            "title" => ["text" => $text, 'textStyle' => ['fontSize' => 16], 'subtext' => '', "subtextStyle" => ['fontWeight' => 'bold', 'color' => '#666']],
            "color" => ["#2e6da4"],
            "tooltip" => ["trigger" => "axis", "axisPointer" => ["type" => 'shadow']],
            "grid" => ["left" => '3%', "right" => '6%', "bottom" => '8%', "top" => '14%', "containLabel" => true],
            "xAxis" => ["type" => 'category', "data" => [], "axisTick" => ["show" => false], "axisLine" => ["show" => false], "boundaryGap" => ['50%', '20%']],
            "yAxis" => ["type" => "value", 'nameLocation' => 'start', 'nameGap' => 25],
            "series" => [],
            "toolbox" => [
                "show" => true,
                "right" => '25px',
                "top" => '125px',
                "orient" => 'vertical',
                "feature" => [
                    "magicType" => ["type" => ['line', 'bar']],
                    "saveAsImage" => ["type" => "png",],
                    "dataView" => ["show" => true]
                ],
            ]
        ];
        
        $k = 0;
        $labels = $series = $datasets = [];
        if ($type === 'day') {
            for ($i = $start_time; $i <= $end_time; $i+=86400) {
                $day = timeToStr($i, 'm-d');
                $labels[] = $day;
                $datasets[$day] = 0;
            }
        } elseif ($type === 'months') {
            for ($i = intval(timeToStr($start_time, "m")); $i <= intval(timeToStr($end_time, "m")); $i++) {
                $lab = str_pad($i, 2, 0, STR_PAD_LEFT);
                $labels[] = $lab;
                $datasets[$lab] = 0;
            }
        } elseif ($type === 'year') {
            for ($i = timeToStr($start_time, "Y"); $i <= timeToStr($end_time, "Y"); $i++) {
                $year = "{$i}";
                $labels[] = $year;
                $datasets[$year] = 0;
            }
        } elseif ($type === 'hour') {
            for ($i = 0; $i <= 23; $i++) {
                $lab = str_pad($i, 2, 0, STR_PAD_LEFT);
                $hour = "{$lab}:00";
                $labels[] = $hour;
                $datasets[$hour] = 0;
            }
        }
        
        $start_date = getDateStr($start_time) . " 00:00:00";
        $end_date = getDateStr($end_time) . " 23:59:59";
        
        $stat = Db::name('home_user')->where('is_xuni', 0)->whereTime('create_time', 'BETWEEN', [$start_date, $end_date])
            ->select();
        
        for ($i = 0; $i < count($stat); $i++) {
            if ($type === 'day') {
                $key = timeToStr($stat[$i]['create_time'], 'm-d');
            } elseif ($type === 'months') {
                $key = timeToStr($stat[$i]['create_time'], 'm');
            } elseif ($type === 'year') {
                $key = timeToStr($stat[$i]['create_time'], 'Y');
            } elseif ($type === 'hour') {
                $key = timeToStr($stat[$i]['create_time'], 'H:00');
            }

            if(isset($datasets[$key])){
                $datasets[$key] = intval($datasets[$key]) + 1;
            }else{
                $datasets[$key] = 1;
            }
        } 
        
        if (empty($datasets)) {
            $this->error("无数据");
        }

        $option["xAxis"]['data'] = array_values(array_unique($labels));
//        $option["legend"]['data'] = array_values(array_unique($legend));
        $option["series"][] = array(
                "type" => "line",
                "data" => array_values($datasets),
            );        
        $this->success("ok", "", $option);
    }
}
