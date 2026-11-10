<?php

namespace app\admin\model;

use think\Model;
use think\Db;
use app\admin\model\RechargeModel;
use app\admin\model\WithdrawModel;
use app\common\model\OrderModel;
use app\admin\model\MoneyModel;
use app\admin\model\MemberModel;
use Carbon\Carbon;
class DayReportModel extends Model {

    // 确定链接表名
    protected $name = 'day_report';
    protected $autoWriteTimestamp = true;

    /**
     * 
     * @param type $date
     * @param type $update 是否更新
     * @return type
     */
    public function stat($date = null, $update = false, $key = null) {
        if (is_null($date)) {
            return;
        }
        $stat_date = Carbon::parse(Carbon::parse($date)->format("Y-m-d 00:00:00"));
        $where_time = [$stat_date->timestamp, Carbon::parse($stat_date)->addDays()->timestamp];
        $Ymd = $stat_date->format("Ymd");
        $data = [
            'stat_date' => $stat_date->format("Y-m-d"),
            'user_id' => 0
        ];
        $report = db('day_report')->where($data)->find();
        if (!$update && $report) {
            return $report;
        }
        if ($key == 'all' || is_null($key)) {
            $data['recharge'] = RechargeModel::where('is_xuni', 0)->where('status', 2)->whereTime('create_time', 'between', $where_time)->sum('money');
            $data['recharge_user'] = RechargeModel::where('is_xuni', 0)->where('status', 2)->whereTime('create_time', 'between', $where_time)->count('DISTINCT user_id');
            $today_userids = RechargeModel::where('is_xuni', 0)->where('status', 2)->whereTime('create_time', 'between', $where_time)->column('DISTINCT user_id');
            $before_today_userids = RechargeModel::where('is_xuni', 0)->where('status', 2)->whereTime('create_time', "<", $stat_date->timestamp)->column('DISTINCT user_id');
            $first_recharge_user = 0;
            foreach ($today_userids as $id) {
                if ((isArrayAndNotEmpty($before_today_userids) && !in_array($id, $before_today_userids)) || empty($before_today_userids)) {
                    $first_recharge_user++;
                }
            }
            $data['first_recharge_user'] = $first_recharge_user;
            $data['withdraw'] = WithdrawModel::where('is_xuni', 0)->where('status', 2)->whereTime('check_time', 'between', $where_time)->sum('money');
            $data['withdraw_user'] = WithdrawModel::where('is_xuni', 0)->where('status', 2)->whereTime('check_time', 'between', $where_time)->count('DISTINCT user_id');
            $data['wr_diff'] = sprintf4f($data['recharge'] - $data['withdraw']);
            $data['online_user'] = Db::name('home_user_login')->alias('l')->join('home_user u', 'u.id=l.user_id')->where('u.is_xuni', 0)
                    ->whereTime('l.create_time', 'between', $where_time)->count('DISTINCT user_id');
            $data['user_money'] = MemberModel::where('is_xuni', 0)->whereTime('create_time', "<", $stat_date->timestamp)->sum('user_money');
        }
        if($key == 'all' || $key == 'order_user') {
            $data['order_user'] = OrderModel::where('is_xuni', 0)->where('paid_status', 2)
                    ->whereTime('create_time', 'between', $where_time)->count('DISTINCT user_id');
        }
        if($key == 'all' || $key == 'order_price') {
            $data['order_price'] = OrderModel::where('is_xuni', 0)
                    ->where('paid_status', 2)->whereTime('create_time', 'between', $where_time)->sum('retail_price');
        }
        if($key == 'all' || $key == 'order_gray') {
            $data['order_gray'] = OrderModel::where('is_xuni', 0)
                    ->where('status', 2)->where('repurchase_status', 3)->whereTime('update_time', 'between', $where_time)->sum('return_bonus');
        }
        if($key == 'all' || $key == 'rebate') {
            if (existTable("qd_money_log_{$Ymd}")) {
                $data['rebate'] = Db::table("qd_money_log_{$Ymd}")->where('is_xuni', 0)
                        ->where('type', 2)->whereTime('create_time', 'between', $where_time)->sum('money');
            }else{
                $data['rebate'] = MoneyModel::where('is_xuni', 0)
                        ->where('type', 2)->whereTime('create_time', 'between', $where_time)->sum('money');
            }
        }
        if($key == 'all' || $key == 'manual_money') {
            if (existTable("qd_money_log_{$Ymd}")) {
                $data['manual_money'] = Db::table("qd_money_log_{$Ymd}")->where('is_xuni', 0)
                        ->where('type', 0)->whereTime('create_time', 'between', $where_time)->sum('money');
            }else{
                $data['manual_money'] = MoneyModel::where('is_xuni', 0)
                        ->where('type', 0)->whereTime('create_time', 'between', $where_time)->sum('money');
            }
        }
        $data['update_time'] = time();
        if ($report) {
            db('day_report')->where('id', $report['id'])->update($data);
        }else{
            $data['create_time'] = time();
            db('day_report')->insert($data);
        }
        if (!is_null($key) && isset($data[$key])) {
            return $data[$key];
        }
        return $this->stat($date);
    }
    
    /**
     * 
     * @param type $date
     * @param type $update 是否更新
     * @return type
     */
    public function agent_stat($date = null, $update = false, $user_id=0, $key = null) {
        if (is_null($date) || empty($user_id)) {
            return;
        }
        $stat_date = Carbon::parse(Carbon::parse($date)->format("Y-m-d 00:00:00"));
        $where_time = [$stat_date->timestamp, Carbon::parse($stat_date)->addDays()->timestamp];
        $Ymd = $stat_date->format("Ymd");
        $data = [
            'stat_date' => $stat_date->format("Y-m-d"),
            'user_id' => $user_id
        ];
        $report = db('day_report')->where($data)->find();
        if (!$update && $report) {
            return $report;
        }
        
        if ($key == 'all' || is_null($key)) {
            $data['recharge'] = RechargeModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->where('r.status', 2)->whereLike('u.tree_id', "%/{$user_id}/%")->whereTime('r.create_time', 'between', $where_time)->sum('r.money');
            $data['recharge_user'] = RechargeModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->where('r.status', 2)->whereLike('u.tree_id', "%/{$user_id}/%")->whereTime('r.create_time', 'between', $where_time)->count('DISTINCT user_id');
            $today_userids = RechargeModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->where('r.status', 2)->whereLike('u.tree_id', "%/{$user_id}/%")->whereTime('r.create_time', 'between', $where_time)->column('DISTINCT user_id');
            $before_today_userids = RechargeModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->where('r.status', 2)->whereLike('u.tree_id', "%/{$user_id}/%")->whereTime('r.create_time', "<", $stat_date->timestamp)->column('DISTINCT user_id');
            $first_recharge_user = 0;
            foreach ($today_userids as $id) {
                if ((isArrayAndNotEmpty($before_today_userids) && !in_array($id, $before_today_userids)) || empty($before_today_userids)) {
                    $first_recharge_user++;
                }
            }
            $data['first_recharge_user'] = $first_recharge_user;
            $data['withdraw'] = WithdrawModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->where('r.status', 2)->whereLike('u.tree_id', "%/{$user_id}/%")->whereTime('r.check_time', 'between', $where_time)->sum('r.money');
            $data['withdraw_user'] = WithdrawModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                    ->where('u.is_xuni', 0)->where('r.status', 2)->whereLike('u.tree_id', "%/{$user_id}/%")->whereTime('r.check_time', 'between', $where_time)->count('DISTINCT user_id');
            $data['wr_diff'] = sprintf4f($data['recharge'] - $data['withdraw']);
            $data['user_money'] = MemberModel::where('is_xuni', 0)->whereLike('tree_id', "%/{$user_id}/%")->whereTime('create_time', "<", $stat_date->timestamp)->sum('user_money');
            $data['online_user'] = Db::name('home_user_login')->alias('l')->join('home_user u', 'u.id=l.user_id')->where('u.is_xuni', 0)
                ->whereLike('u.tree_id', "%/{$user_id}/%")->whereTime('l.create_time', 'between', $where_time)->count('DISTINCT user_id');
        }
        if($key == 'all' || $key == 'order_user') {
            $data['order_user'] = OrderModel::where('is_xuni', 0)->where('paid_status', 2)
                    ->whereTime('create_time', 'between', $where_time)->whereIn('user_id', function($query) use($user_id){
                        $query->name('home_user')->where('is_xuni', 0)->whereLike('tree_id', "%/{$user_id}/%")->field('id');
                    })->count('DISTINCT user_id');
        }
        if($key == 'all' || $key == 'order_price') {
            $data['order_price'] = OrderModel::where('is_xuni', 0)->where('paid_status', 2)->whereTime('create_time', 'between', $where_time)
                    ->whereIn('user_id', function($query) use($user_id){
                        $query->name('home_user')->where('is_xuni', 0)->whereLike('tree_id', "%/{$user_id}/%")->field('id');
                    })->sum('retail_price');
        }
        if($key == 'all' || $key == 'order_gray') {
            $data['order_gray'] = OrderModel::where('is_xuni', 0)->where('status', 2)->where('repurchase_status', 3)->whereIn('user_id', function($query) use($user_id){
                $query->name('home_user')->where('is_xuni', 0)->whereLike('tree_id', "%/{$user_id}/%")->field('id');
            })->whereTime('update_time', 'between', $where_time)->sum('return_bonus');
        }
        if($key == 'all' || $key == 'rebate') {
            if (existTable("qd_money_log_{$Ymd}")) {
                $data['rebate'] = Db::table("qd_money_log_{$Ymd}")->alias('r')->join('home_user u', 'u.id=r.user_id')
                        ->where('u.is_xuni', 0)->where('r.type', 2)->whereLike('u.tree_id', "%/{$user_id}/%")
                        ->whereTime('r.create_time', 'between', $where_time)->sum('r.money');
            }else{
                $data['rebate'] = MoneyModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                        ->where('u.is_xuni', 0)->where('r.type', 2)->whereLike('u.tree_id', "%/{$user_id}/%")
                        ->whereTime('r.create_time', 'between', $where_time)->sum('r.money');
            }
        }
        if($key == 'all' || $key == 'manual_money') {
            if (existTable("qd_money_log_{$Ymd}")) {
                $data['manual_money'] = Db::table("qd_money_log_{$Ymd}")->alias('r')->join('home_user u', 'u.id=r.user_id')
                        ->where('u.is_xuni', 0)->where('r.type', 0)->whereLike('u.tree_id', "%/{$user_id}/%")
                        ->whereTime('r.create_time', 'between', $where_time)->sum('r.money');
            }else{
                $data['manual_money'] = MoneyModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                        ->where('u.is_xuni', 0)->where('r.type', 0)->whereLike('u.tree_id', "%/{$user_id}/%")
                        ->whereTime('r.create_time', 'between', $where_time)->sum('r.money');
            }
        }
        $data['update_time'] = time();
        if ($report) {
            db('day_report')->where('id', $report['id'])->update($data);
        }else{
            $data['create_time'] = time();
            db('day_report')->insert($data);
        }
        if (!is_null($key) && isset($data[$key])) {
            return $data[$key];
        }
        return $this->agent_stat($date, false, $user_id);
    }
}
