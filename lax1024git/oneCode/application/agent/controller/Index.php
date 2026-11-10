<?php

namespace app\agent\controller;

use app\common\model\UserModel;
use app\admin\model\RechargeModel;
use app\admin\model\WithdrawModel;
use app\common\model\OrderModel;
use app\admin\model\MoneyModel;
use think\Cache;
use app\admin\model\DayReportModel;
use Carbon\Carbon;

class Index extends Base {

    public function index() {
        $this->assign('langs', getLangs(1));
        return $this->fetch('/index');
    }

    /**
     * 后台默认首页
     * @return mixed
     */
    public function indexPage() {
        return redirect(url('report'));
    }

    public function report() {
        $id = $this->request->param("id/d", 0);
        if ($this->request->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            $whereTime = null;
            if ($id > 0) {
                $where['leader1'] = $id;
            }else{
                $where['id'] = $this->userid;
            }
            if (!empty($param['startTime']) && !empty($param['endTime'])) {
                $start_time = strtotime($param['startTime'] . " 00:00:00");
                $end_time   = strtotime($param['endTime'] . " 23:59:59");
                $whereTime = [$start_time, $end_time];
//                $where['user.create_time'] = ['BETWEEN', [$start_time, $end_time]];
            }
            if (!empty($param['searchText'])) {
                $where['id|mobile|user_name'] = $param['searchText'];
            }
            $redis = Cache::store('redis');
            $model = new UserModel();
            $result = $model->where($where)->whereLike('tree_id', "%/{$this->userid}/%")->limit($offset, $limit)->order('id desc')->select();
            $total = $model->where($where)->whereLike('tree_id', "%/{$this->userid}/%")->limit($offset, $limit)->count();
            foreach ($result as $key => $vo) {
                $redis_key = "agent_report:{$vo['id']}:" . (empty($whereTime) ? 'total' : (getDateStr($whereTime[0]) . "_" .  getDateStr($whereTime[1])) );
                if ($redis->has($redis_key)) {
                    $result[$key] = $redis->get($redis_key);
                    continue;
                }
                $result[$key]['team_users'] = getTeamCount($vo['id']);
                if ($result[$key]['team_users'] > 1) {
                    $result[$key]['operate'] = showOperate([
                        '查看下一级' => [
                            'href' => url('index/report', ['id' => $vo['id'], 'startTime' => getDateStr($whereTime[0]), 'endTime' => getDateStr($whereTime[1])]),
                            'btnStyle' => 'primary',
                            'icon' => 'fa fa-eye'
                        ],
                    ]);
                }else{
                    $result[$key]['operate'] = '-';
                }
                $result[$key]['total_active_user'] = $this->getTotalActiveUser($vo['id'], $whereTime);
                $result[$key]['total_recharge'] = $this->getTotalRecharge($vo['id'], $whereTime);
                $result[$key]['total_withdraw'] = $this->getTotalWithdraw($vo['id'], $whereTime);
                $result[$key]['wr_diff'] = sprintf4f($result[$key]['total_recharge'] - $result[$key]['total_withdraw']);
//                $result[$key]['total_order_price'] = $this->getTotalOrder($vo['id'], $whereTime, 'price');
//                $result[$key]['total_order_gray'] = $this->getTotalOrder($vo['id'], $whereTime, 'gray');
//                $result[$key]['total_order_count'] = $this->getTotalOrderCount($vo['id'], $whereTime);
//                $result[$key]['total_order_rebate'] = $this->getTotalMoneyLog($vo['id'], $whereTime);
//                $result[$key]['total_handsel'] = $this->getTotalMoneyLog($vo['id'], $whereTime, 0);
                $result[$key]['total_user_money'] = $this->getTotalUser($vo['id'], 'user_money');
                $result[$key]['total_frozen_money'] = $this->getTotalUser($vo['id'], 'frozen_money');
                $redis->set($redis_key, $result[$key], 300);
            }
            $return['total'] = $total;  //总数据
            $return['rows'] = $result;
            return json($return);
        }
        $startTime = $this->request->param('startTime/s');
        $endTime = $this->request->param('endTime/s');
        if (empty($startTime)) {
            $startTime = getDateStr(strtotime(date('Y-m-01')));
        }
        if (empty($endTime)) {
            $endTime = getDateStr();
        }
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $this->assign('id', $id);
        $this->assign('url', url('report', ['id' => $id]));
        return $this->fetch();
    }
    
    public function day_report() {
        $act = $this->request->param('act/s');
        if ($act == 'query') {
            $date = $this->request->param('date/s');
            $key = $this->request->param('key/s');
            $model = new DayReportModel;
            $result = $model->agent_stat($date, true, $this->userid, $key);
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
            $row = $model->agent_stat($date, $date == $today || $date == $update, $this->userid);
            $list[] = $row;
        }
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $this->assign('list', $list);
        return $this->fetch();
    }
    
    private function getTotalUser($id, $field = 'user_money') {
        $db = UserModel::where('is_xuni', 0)->whereLike('tree_id', "%/{$id}/%");
        $result = $db->sum($field);
        return sprintf4f($result);
    }
    
    private function getTotalActiveUser($id, $time = null) {
        $db = \think\Db::name('home_user_login')->alias('r')->join('home_user u', 'u.id=r.user_id')
                ->where('u.is_xuni', 0)->whereLike('u.tree_id', "%/{$id}/%");
        if (isArrayAndNotEmpty($time)) {
            $db = $db->whereTime('r.create_time', 'BETWEEN', $time);
        }
        $result = $db->count('distinct user_id');
        return sprintf4f($result);
    }
    
    private function getTotalRecharge($id, $time = null) {
        $db = RechargeModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                ->where('u.is_xuni', 0)->where('r.status', 2)->whereLike('u.tree_id', "%/{$id}/%");
        if (isArrayAndNotEmpty($time)) {
            $db = $db->whereTime('r.create_time', 'BETWEEN', $time);
        }
        $result = $db->sum('r.money');
        return sprintf4f($result);
    }
    private function getTotalWithdraw($id, $time = null) {
        $db = WithdrawModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                ->where('u.is_xuni', 0)->where('r.status', 2)->whereLike('u.tree_id', "%/{$id}/%");
        if (isArrayAndNotEmpty($time)) {
            $db = $db->whereTime('r.check_time', 'BETWEEN', $time);
        }
        $result = $db->sum('r.money');
        return sprintf4f($result);
    }
    private function getTotalOrder($id, $time = null, $field = 'price') {
        $db = OrderModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                ->where('u.is_xuni', 0)->where('r.status', 2)->whereLike('u.tree_id', "%/{$id}/%");
        if (isArrayAndNotEmpty($time)) {
            $db = $db->whereTime('r.create_time', 'BETWEEN', $time);
        }
        $result = $db->sum($field);
        return sprintf4f($result);
    }
    private function getTotalOrderCount($id, $time = null) {
        $db = OrderModel::alias('r')->join('home_user u', 'u.id=r.user_id')
                ->where('u.is_xuni', 0)->where('r.status', 2)->whereLike('u.tree_id', "%/{$id}/%");
        if (isArrayAndNotEmpty($time)) {
            $db = $db->whereTime('r.create_time', 'BETWEEN', $time);
        }
        $result = $db->count('r.id');
        return $result;
    }
    private function getTotalMoneyLog($id, $time = null, $type = 4) {
        $db = MoneyModel::alias("r")->join('home_user u', 'u.id=r.user_id')->where('r.type', $type)->whereLike('u.tree_id', "%/{$id}/%");
        if (isArrayAndNotEmpty($time)) {
            $db = $db->whereTime('r.create_time', 'BETWEEN', $time);
        }
        $result = $db->sum('r.money');
        return sprintf4f($result);
    }
    
    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id) {
        return [
            '查看下一级' => [
                'href' => url('index/report', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-eye'
            ],
        ];
    }
    /**
     * Ajax获取最新客服消息
     * @return type
     */
    public function get_message() {
//        if ($this->user['is_online'] == 1 || $this->user['id'] == 1) {
            $map = ["read" => 0, "master_id" => 0];
            $map['user_id'] = ['IN', function($query){
                $query->name('home_user')->where('tree_id', 'LIKE', "%/{$this->userid}/%")->field('id');
            }];
            $data = db("message")->field("DISTINCT user_id, MAX(id) AS MessageID")->where($map)->order("MessageID DESC")->group("user_id")->select();
            $desc = "";
            foreach ($data as &$value) {
                $value['content'] = db("message")->where("id", $value['MessageID'])->value("content");
                if (stripos($value['content'], "img[") !== false) {
                    $value['content'] = "图片信息";
                }else{
                    $value['content'] = removeXSS($value['content']);
                }
                if ($desc == "") {
                    $desc = $value['content'];
                }
                $value['Title'] = \think\helper\Str::substr($value['content'], 0, 10) . "...";
                $value['ChatUrl'] = url("message/chat", ['userid' => $value['user_id']]);
            }
            unset($value);
            $json = [
                'count' => count($data), 
                "list" => $data, 
                "token" => md5(json_encode($data)),
                "url" => url("message/index", "", false, true), 
                "title" => "有新的客服消息！", 
                "desc" => $desc, 
            ];
            return json($json);
//        }else{
//            return json(['count' => 0, "list" => [], "token" => md5(json_encode([]))]);
//        }
    }
}
