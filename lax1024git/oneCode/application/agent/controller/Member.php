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

use app\admin\model\LevelModel;
use app\admin\model\MemberModel;
use app\admin\model\MoneyModel;
use app\admin\model\UserModel AS AdminModel;
use think\Db;
use think\Cache;

class Member extends Base {

    // 用户列表 
    public function index() {
        $id = $this->request->param("id/d", 0);
        $level = $this->request->param('level/d', 1);
        if (request()->isAjax()) {
            $param = $this->request->param();
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $is_xuni = $param['is_xuni'];
            $userid = $param['userid'];
            $time_type = intval($param['time_type']);
            $invite_code = $param['invite_code'];
            $where['is_demo'] = 0;
            if (!empty($param['searchText'])) {
                $where['user_name|mobile|true_name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            if ($userid > 0) {
                $where["id"] = $userid;
            }
            if ($invite_code != '') {
                $where["invite_code"] = $invite_code;
            }
            if ($param['level_id'] != '') {
                $where['level_id'] = $param['level_id'];
            }
            if(!empty($param['ip'])){
                $where['last_login_ip'] =  $param['ip'];
            }
            if ($id > 0 && ($level >= 1 && $level <= 7)) {
                $where["leader{$level}"] = $id;
            }
            if (in_array($is_xuni, [0,1])) {
                $where['is_xuni']= $is_xuni;
            }elseif($is_xuni == 2) { //未登录过的用户
                $where['last_login_time'] = 0;
            }elseif($is_xuni == 3) { //未绑定银行卡的用户
                $where['bank_number'] = '';
            }elseif($is_xuni == 4) { //已绑定银行卡的用户
                $where['bank_number'] = ["<>", ""];
            }
            $orderby = 'id DESC';
            if (!empty($param['sortName']) && in_array($param['sortName'], ['last_login_time', 'user_money', 'level_id'])) {
                $orderby = $param['sortName'] . " " . ($param['sortOrder'] == "asc" ? "ASC" : "DESC");
            }
            if (!empty($param['startTime']) && !empty($param['endTime'])) {
                $start_time = strtotime($param['startTime'] . " 00:00:00");
                $end_time   = strtotime($param['endTime'] . " 23:59:59");
                if ($time_type == 1) {
                    $where['last_login_time'] = ['BETWEEN', [$start_time, $end_time]];
                }else{
                    $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
                }
            }
            $where['tree_id'] =  ['like', "%/{$this->userid}/%"];

            $user = new MemberModel();
            $selectResult = $user->getUsersByWhere($where, $offset, $limit, $orderby);
            $status = config('user_status');
            $level_name = Db::name('home_user_level')->column("level_name", 'level_id');
            // 拼装参数
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['is_xuni_text'] = $vo['is_xuni'] == 1 ? '<span style="color:red">是</span>' : '否';
                $selectResult[$key]['last_login_time'] = $vo['last_login_time'] > 0 ? date('Y-m-d H:i:s', $vo['last_login_time']) : '-';
                $selectResult[$key]['level_id'] = isset($level_name[$vo['level_id']]) ? $level_name[$vo['level_id']] : '非会员';
                $selectResult[$key]['status'] = $status[$vo['status']];
                if ($vo['delete_time']) {
                    $selectResult[$key]['status'] = '<span style="color:red;">已删除</span>';
                    $selectResult[$key]['operate'] = '-';
                }else{
                    $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                }
                $leader1_mobile = $user->getOneUser($vo['leader1'], "mobile");
                $selectResult[$key]['leader1_mobile'] = empty($leader1_mobile) ? "-" : $leader1_mobile;
                if ($leader1_mobile) {
                    $selectResult[$key]['leader1_mobile'] .= "<br><a class='btn btn-info btn-xs' href='javascript:;' onclick='viewTopId(\"". trim($vo['tree_id'], '/')."\")'>查看上级</a>";
                }
                $selectResult[$key]['recharge'] = Db::name('recharge')->where('user_id', $vo['id'])->where('status', 2)->sum('money');
                $selectResult[$key]['withdraw'] = Db::name('withdrawals')->where('user_id', $vo['id'])->where('status', 2)->sum('money');
                $selectResult[$key]['user_money'] = i18n_sprintf($vo['user_money']);
            }
            
            $return['total'] = $user->getAllUsers($where);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
        $user = new MemberModel();
        $info = $user->getOneUser($id);
        $levelModel = new LevelModel();
        
        $this->assign('level', $level);
        $this->assign('id', $id);
        $this->assign('info', $info);
        $this->assign('levelAll', $levelModel->getLevel());
        $this->assign('url', url('index', ['id' => $id, 'level' => $level]));
        $this->assign('export_url', url('export', ['id' => $id, 'level' => $level]));
        return $this->fetch();
    }
   
    // 导出用户列表
    public function export() {
        $id = $this->request->param("id/d", 0);
        $level = $this->request->param('level/d', 1);
        $param = input('param.');
        $userid = $param['userid'];
        $time_type = intval($param['time_type']);
        $is_xuni = $param['is_xuni'];
        $invite_code = $param['invite_code'];
        $where['is_demo'] = 0;
        if (!empty($param['searchText'])) {
            $where['user_name|mobile|true_name'] = ['like', '%' . $param['searchText'] . '%'];
        }
        if ($id > 0 && ($level >= 1 && $level <= 7)) {
            $where["leader{$level}"] = $id;
        }elseif($id > 0 && $level == 99){
            $where['tree_id'] = ['like', "%/{$id}/%"];
        }
        if ($param['level_id'] != '') {
            $where['level_id'] = $param['level_id'];
        }
        if ($userid > 0) {
            $where["id"] = $userid;
        }
        if ($invite_code != '') {
            $where["invite_code"] = $invite_code;
        }
        if (in_array($is_xuni, [0,1])) {
            $where['is_xuni']= $is_xuni;
        }elseif($is_xuni == 2) { //未登录过的用户
            $where['last_login_time'] = 0;
        }elseif($is_xuni == 3) { //未绑定银行卡的用户
            $where['bank_number'] = '';
        }elseif($is_xuni == 4) { //已绑定银行卡的用户
            $where['bank_number'] = ["<>", ""];
        }
        $where['tree_id'] =  ['like', "%/{$this->userid}/%"];
        
        $orderby = 'id DESC';
        if (!empty($param['sortName']) && in_array($param['sortName'], ['last_login_time', 'user_money', 'level_id'])) {
            $orderby = $param['sortName'] . " " . ($param['sortOrder'] == "asc" ? "ASC" : "DESC");
        }
        if (!empty($param['startTime']) && !empty($param['endTime'])) {
            $start_time = strtotime($param['startTime'] . " 00:00:00");
            $end_time   = strtotime($param['endTime'] . " 23:59:59");
            if ($time_type == 1) {
                $where['last_login_time'] = ['BETWEEN', [$start_time, $end_time]];
            }else{
                $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
            }
        }
        $user = new MemberModel();
        $list = $user->getAllUsersByWhere($where, $orderby);
        $status = config('user_status');
        $recharge = Db::name('recharge')->where("status", 2)->group("user_id")->column("sum(money)", "user_id");
        $withdrawals = Db::name('withdrawals')->where("status", 2)->group("user_id")->column("sum(money)", "user_id");
        // 拼装参数
        foreach ($list as $key => $vo) {
            $list[$key]['is_xuni'] = $vo['is_xuni'] == 1 ? '是' : '否';
            $list[$key]['last_login_time'] = date('Y-m-d H:i:s', $vo['last_login_time']);
            $list[$key]['status'] = $status[$vo['status']];
            if ($vo['delete_time']) {
                $list[$key]['status'] = '已删除';
            }
            $list[$key]['recharge'] = isset($recharge[$vo['id']]) ? intval($recharge[$vo['id']]) : 0;
            $list[$key]['withdraw'] = isset($withdrawals[$vo['id']]) ? intval($withdrawals[$vo['id']]) : 0;
            $list[$key]['user_money'] = i18n_sprintf($vo['user_money']);
        }
        $header = [
            "id" => "ID",
            "invite_code" => "邀请码",
            "mobile" => "手机号",
            "user_money" => "余额",
            "recharge" => "充值总额",
            "withdraw" => "提现总额",
            "leader1" => "上级ID",
            "is_xuni" => "虚拟账户",
            "create_time" => "注册时间",
            "ip" => "注册IP",
            "last_login_time" => "最后登录时间",
            "last_login_ip" => "最后登录IP",
            "status" => "状态",
        ];
        return genCsv($header, $list, "用户列表");
    }
    // 账户调整
    public function money() {
        $money_type = config("my.money_type");
        if (request()->isAjax()) {
            $param = $this->request->param();
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            $where_str = [];
            if (!empty($param['username'])) {
                $user_id = UserModel::where('mobile', $param['username'])->value('id');
                $where['user_id'] = $user_id ?? $param['username'];
                $where_str[] = "user_id = '" . ($user_id ?? $param['username']) . "'";
            }
            if (isset($param['userId']) && $param['userId'] !== '') {
                $where['user_id'] = $param['userId'];
                $where_str[] = "user_id = '{$param['userId']}'";
            }
            if ($param['type'] !== '') {
                $where['type'] = $param['type'];
                $where_str[] = "type = '{$param['type']}'";
            }
            if (empty($param['startTime'])) {
                $param['startTime'] = getDateStr();
            }
            if (empty($param['endTime'])) {
                $param['endTime'] = getDateStr();
            }
            if ($param['from_user_id'] !== '') {
                $where['from_user_id'] = $param['from_user_id'];
                $where_str[] = "from_user_id = '{$param['from_user_id']}'";
            }
            if ($param['order_id'] !== '') {
                $where['order_id'] = $param['order_id'];
                $where_str[] = "order_id = '{$param['order_id']}'";
            }
            if (!empty($param['startTime']) && !empty($param['endTime'])) {
                $start_time = strtotime($param['startTime'] . " 00:00:00");
                $end_time   = strtotime($param['endTime'] . " 23:59:59");
                $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
                $where_str[] = "create_time BETWEEN '{$start_time}' AND '{$end_time}'";
            }
            if (!empty($param['startTime']) && empty($param['endTime'])) {
                $start_time = strtotime($param['startTime'] . " 00:00:00");
                $where['create_time'] = ['>=', $start_time];
                $where_str[] = "create_time >= '{$start_time}'";
            }
            if (!empty($param['endTime']) && empty($param['startTime'])) {
                $end_time   = strtotime($param['endTime'] . " 23:59:59");
                $where['create_time'] = ['<=', $end_time];
                $where_str[] = "create_time <= '{$start_time}'";
            }
            $tables = [];
            for ($i = strtotime($param['startTime']); $i <= strtotime($param['endTime']); $i+=86400) {
                $full_table_name = "qd_money_log_" . timeToStr($i, "Ymd");
                if (existTable($full_table_name)) {
                    $tables[] = $full_table_name;
                }
            }
            $field = "id,is_xuni,user_id,admin_id,order_id,goods_id,from_user_id,remarks,money,user_money,type,status,create_time";
            $sqls = [];
            $count_sqls = [];
            $stat_sqls = [];
            for($i=0; $i < count($tables); $i++) {
                $sqls[] = "SELECT {$field} FROM {$tables[$i]} WHERE " . implode(" AND ", $where_str);
                $count_sqls[] = "SELECT count(id) AS total FROM {$tables[$i]} WHERE " . implode(" AND ", $where_str);
                $stat_sqls[] = "SELECT type, sum(money) AS total FROM {$tables[$i]} WHERE " . implode(" AND ", $where_str) . " GROUP BY type";
            }
            
            $db_result = Db::table('qd_money_log');
            if (isArrayAndNotEmpty($sqls)) {
                $db_result = $db_result->union($sqls, true);
            }
            $result = $db_result->field($field)->where($where)
                    ->limit($offset, $limit)->order('create_time desc')->select();
            
            if (isArrayAndNotEmpty($count_sqls)) {
                $count_sql = Db::field("count(id) AS total")
                    ->table('qd_money_log')
                    ->union($count_sqls, true)
                    ->where($where)
                    ->buildSql();
                $total = Db::table($count_sql . " a")->sum("a.total");
            }else{
                $total = Db::table("qd_money_log")->where($where)->count();
            }
            foreach ($result as $key => &$vo) {
                $vo['type'] = $money_type[$vo['type']];
                if ($vo['admin_id'] > 0) {
                    $vo['admin_name'] = AdminModel::where('id', $vo['admin_id'])->value('user_name');
                }else{
                    $vo['admin_name'] = '-';
                }
                $vo['create_time'] = date('Y-m-d H:i:s', $vo['create_time']);
            }
            unset($vo);
            $return['total'] = $total;  //总数据
            $return['rows'] = $result;
            
            if (isArrayAndNotEmpty($stat_sqls)) {
                $stat_sql = Db::field('type, sum(money) AS total')
                    ->table('qd_money_log')
                    ->union($stat_sqls, true)
                    ->where($where)
                    ->group('type')
                    ->buildSql();
                $stat = Db::table($stat_sql . " a")->group('a.type')->field('a.type, sum(a.total) AS total')->select();
            }else{
                $stat = Db::table('qd_money_log')->where($where)->group('type')->field('type, sum(money) AS total')->select();
            }

            if ($stat) {
                foreach ($stat as $k => $v) {
                    $stat[$k]['text'] = $money_type[$v['type']];
                    $stat[$k]['total'] = sprintf4f($v['total']);
                }
            }
            $return['stat'] = $stat;
            return json($return);
        }
        $uid = input('uid/d');
        $this->assign('uid', $uid);
        $this->assign('money_type', $money_type);
        return $this->fetch();
    }

    /**
     * 增加余额
     */
    function balance_add(){
        $id = input('id');
        $number = input("number");
        if ($id == null || !is_numeric($id) || $id<1){
            return json(msg(-1,'',"用户信息有误"));
        }
        if ($number == null || (!is_float($number) && !is_numeric($number)) || $number==0){
            return json(msg(-1,'',"增加余额数量有误"));
        }
        $user = MemberModel::where('id', $id)->find();
        if (!$user || (!in_array($this->userid,explode('/',$user->tree_id)) && $user->leader1!=$this->userid)){
            return json(msg(-1,'',"该用户不是您的下级"));
        }
        insert_operation_log('代理增加用户余额', "代理ID:{$this->userid},名称:{$this->userInfo['true_name']},用户ID:{$id},原余额:{$user->user_money},修改后的余额:".sprintf('%.4f',$user->user_money+$number));
        addMoneyLog($id, $number, 1,"人工充值");
        return json(msg(1,$number,lang('success')));
    }

    /**
     * 增加积分
     */
    function points_add(){
        $id = input('id');
        $number = input("number");
        if ($id == null || !is_numeric($id) || $id<1){
            return json(msg(-1,'',"用户信息有误"));
        }
        if ($number == null || (!is_float($number) && !is_numeric($number)) || $number==0){
            return json(msg(-1,'',"增加积分数量有误"));
        }
        $user = MemberModel::where('id', $id)->find();
        if (!$user || (!in_array($this->userid,explode('/',$user->tree_id)) && $user->leader1!=$this->userid)){
            return json(msg(-1,'',"该用户不是您的下级"));
        }
        insert_operation_log('代理增加用户积分', "代理ID:{$this->userid},名称:{$this->userInfo['true_name']},用户ID:{$id},原积分:{$user->points},修改后的积分:".sprintf('%.4f',$user->points+$number));
        MemberModel::inc('points',$number)->where('id',$id)->update();
        return json(msg(1,$number,lang('success')));
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id) {
        return [
            '团队下级' => [
                'auth' => 'member/index',
                'href' => url('member/index', ['id' => $id]),
                'btnStyle' => 'success',
                'icon' => 'fa fa-paste'
            ],
            '账变记录' => [
                'auth' => 'member/money',
                'href' => url('member/money', ['uid' => $id]),
                'btnStyle' => 'success',
                'icon' => 'fa fa-comment'
            ],
//            '增加余额' => [
//                'auth' => 'member/balance_add',
//                'href' => "javascript:balance_add(" . $id . ")",
//                'btnStyle' => 'success',
//                'icon' => 'fa fa-add'
//            ],
//            '增加积分' => [
//                'auth' => 'member/points_add',
//                'href' => "javascript:points_add(" . $id . ")",
//                'btnStyle' => 'success',
//                'icon' => 'fa layui-icon-add-circle'
//            ]
        ];
    }

}
