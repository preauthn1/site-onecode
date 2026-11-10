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
use app\admin\model\MemberModel;
use app\admin\model\MoneyModel;
use app\admin\model\RechargeModel;
use app\admin\model\UserModel AS AdminModel;
use think\Db;
use think\Cache;
use think\Queue;
use app\services\UserService;

class Member extends Base {

    // 用户列表 
    public function index() {
        $id = $this->request->param("id/d", 0);
        $level = $this->request->param('level/d', 1);
        $vuser = $this->request->param('vuser/d', -1);
        if (request()->isAjax()) {
            $param = $this->request->param();
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $is_xuni = $param['is_xuni'];
            $time_type = intval($param['time_type']);
            $where['is_demo'] = 0;
            if (!empty($param['searchText'])) {
                $where['id|user_name|mobile|true_name|invite_code'] = $param['searchText'];
            }
            if ($param['level_id'] != '') {
                $where['level_id'] = $param['level_id'];
            }
            if(!empty($param['ip'])){
                $where['last_login_ip|ip'] =  $param['ip'];
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
            //重复IP用户
            $repeat_flag = $this->request->param('repeatIP/d', 0);
            if($repeat_flag == '1'){
                $where['last_login_ip'] = ['in', function($query)  use ($where){
                    $query->name('home_user')
                        ->whereNotIn('last_login_ip', ['', '0.0.0.0'])
                        ->group('last_login_ip')
                        ->having('count(1) > 1')
                        ->field('last_login_ip');
                        if(!empty($where['user.create_time'])){
                            $where['create_time'] = $where['user.create_time'];
                            unset($where['user.create_time']);
                        }
                        $query->where($where);
                }];
                $orderby = 'last_login_ip DESC,id DESC';
            }

            $user = new MemberModel();
            $selectResult = $user->getUsersByWhere($where, $offset, $limit, $orderby);
            $status = config('user_status');
            $level_name = Db::name('home_user_level')->column("level_name", 'level_id');
            // 拼装参数
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['is_xuni_text'] = $vo['is_xuni'] == 1 ? '<span style="color:red">是</span>' : '否';
                $selectResult[$key]['last_login_time'] = $vo['last_login_time'] > 0 ? date('Y-m-d H:i:s', $vo['last_login_time']) : '-';
                $selectResult[$key]['level_id'] = isset($level_name[$vo['level_id']]) ? $level_name[$vo['level_id']] : '非会员';
                $selectResult[$key]['status'] = getStatusText($vo['status'], $status[$vo['status']]);
                if ($vo['delete_time']) {
                    $selectResult[$key]['status'] = '<span style="color:red;">已删除</span>';
                    $selectResult[$key]['operate'] = '-';
                }else{
                    $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                    $selectResult[$key]['operate'] .= ' <button class="btn btn-primary btn-sm bmd-modalButton toggle-tooltip" data-toggle="modal" data-bmdSrc="'.url('message/chat', ['userid' => $vo['id']]).'" data-bmdWidth="750" data-bmdHeight="500" data-target="#bmbModal" title="回复"><i class="fa fa-commenting"></i></button>';
                }
                $leader1_mobile = $user->getOneUser($vo['leader1'], "mobile");
                $selectResult[$key]['leader1_mobile'] = empty($leader1_mobile) ? "-" : $leader1_mobile;
                if ($leader1_mobile) {
                    $selectResult[$key]['leader1_mobile'] .= "<br><a class='btn btn-info btn-xs' href='javascript:;' onclick='viewTopId(\"". trim($vo['tree_id'], '/')."\")'>查看上级</a>";
                }
                $ipInfo = "";
                if ($vo['ip'] && $vo['ip'] != '0.0.0.0') {
                    $ipInfo .= "注册IP:{$vo['ip']}";
                    $ipInfo .= "<br>" . ip2location($vo['ip']);
                }
                if ($vo['last_login_ip'] && $vo['last_login_ip'] != '0.0.0.0' && $vo['last_login_ip'] != $vo['ip']) {
                    $ipInfo .= "<br>登录IP:{$vo['last_login_ip']}";
                    $ipInfo .= "<br>" . ip2location($vo['last_login_ip']);
                }
                $selectResult[$key]['recharge'] = Db::name('recharge')->where('user_id', $vo['id'])->where('status', 2)->sum('money');
                $selectResult[$key]['withdraw'] = Db::name('withdrawals')->where('user_id', $vo['id'])->where('status', 2)->sum('money');
                $selectResult[$key]['user_money'] = i18n_sprintf($vo['user_money']);
                $selectResult[$key]['ip_info'] = $ipInfo;
                $selectResult[$key]['cp_url'] = url('member/setcp', ['user_id' => $vo['id'], 'win' => 1]);
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
        $userid = $this->request->param('userid/d', 0);
        $time_type = $this->request->param('time_type/d', 0);
        $is_xuni = $this->request->param('is_xuni/d', 0);
        $invite_code = $this->request->param('invite_code/s');
        $where['is_demo'] = 0;
        if (isset($param['searchText']) && !empty($param['searchText'])) {
            $where['id|invite_code|user_name|mobile|true_name'] = $param['searchText'];
        }
        if ($id > 0 && ($level >= 1 && $level <= 7)) {
            $where["leader{$level}"] = $id;
        }elseif($id > 0 && $level == 99){
            $where['tree_id'] = ['like', "%/{$id}/%"];
        }
        if (isset($param['level_id']) && $param['level_id'] != '') {
            $where['level_id'] = $param['level_id'];
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
        }else{
            $this->error(lang('请选择时间'));
        }
        $user = new MemberModel();
        $list = $user->getAllUsersByWhere($where, $orderby);
        $status = config('user_status');
        $recharge = Db::name('recharge')->where("status", 2)->group("user_id")->column("sum(money)", "user_id");
        $withdrawals = Db::name('withdrawals')->where("status", 2)->group("user_id")->column("sum(money)", "user_id");
        // 拼装参数
        foreach ($list as $key => $vo) {
            $list[$key]['is_xuni'] = lang($vo['is_xuni'] == 1 ? '是' : '否');
            $list[$key]['last_login_time'] = date('Y-m-d H:i:s', $vo['last_login_time']);
            $list[$key]['status'] = $status[$vo['status']];
            if ($vo['delete_time']) {
                $list[$key]['status'] = lang('已删除');
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
    public function batch() {
        $id = $this->request->param("id/d", 0);
        $type = $this->request->param('type/d', 1);
        if (!in_array($type, [1, 2]) || empty($id)) {
            $this->error(lang("参数有误"));
        }
        $user = new MemberModel();
        $row = $user->withTrashed()->whereLike('tree_id', "%/{$id}/%")->setField('status', $type == 1 ? 2 : 1);
        if ($row > 0) {
            insert_operation_log('批量解封/封停', "用户id:{$id},"  . "成功".($type == 1 ? "封停" : '解封')."{$row}个会员");
        }
        $this->success("成功".($type == 1 ? "封停" : '解封')."{$row}个会员");
    }
    // 添加用户
    public function userAdd() {
        if (request()->isPost()) {
            $param = input('post.');
            if ($param['paypwd']) {
                $param['paypwd'] = md5($param['paypwd'] . config('salt'));
            }
            $user = new MemberModel();
            if (empty($param['password'])) {
                return json(msg(-1, '', lang("请填写密码")));
            }
            if($param['leader1']) {
                $parent = $user->getOneUser($param['leader1']);
                if (empty($parent)) {
                    return json(msg(-1, '', lang("上级用户信息不存在")));
                }
                $param['leader2'] = $parent['leader1'];
                $param['leader3'] = $parent['leader2'];
                $param['leader4'] = $parent['leader3'];
                $param['leader5'] = $parent['leader4'];
                $param['leader6'] = $parent['leader5'];
                $param['leader7'] = $parent['leader6'];
            }
            $param['password'] = md5($param['password'] . config('salt'));
            $param['avatar'] = mt_rand(1, 100) . '.png';
            $param['invite_code'] = generateInviteCode();
            $param['create_time'] = time();
            $flag = $user->insertUser($param);
            if ($flag['code'] == 1) {
                $id = MemberModel::where('mobile', $param['mobile'])->value('id');
                updateTreeById2($id);
                insert_operation_log('新增会员', "用户id:{$id}");
            }
            return json(msg($flag['code'], $flag['data'], lang($flag['msg'])));
        }
        $level = new LevelModel();
        $banks = i18n_banks2();
        $this->assign([
            'level' => $level->getLevel(),
            'banks' => $banks,
            'status' => config('user_status')
        ]);
        return $this->fetch();
    }

    // 编辑用户
    public function userEdit() {
        $user = new MemberModel();
        if (request()->isPost()) {
            $param = input('post.');
            if ($param['paypwd']) {
                $param['paypwd'] = md5($param['paypwd'] . config('salt'));
            }else{
                unset($param['paypwd']);
            }
            // 变更关系
            if (!empty($param['password'])) {
                $param['password'] = md5($param['password'] . config('salt'));
            } else {
                unset($param['password']);
            }
            if (!empty($param['level_id'])){
                $level_model = new LevelModel();
                $level = $level_model->order('sort','DESC')->select();
                foreach ($level AS $item){
                    if ($item['level_id'] == $param['level_id']){
                        $param['pd_num'] = $item['dannum'];
                    }
                }
            }
            unset($param['leader1']);
            $flag = $user->editUser($param);
            if ($flag['code'] == 1) {
                insert_operation_log('修改会员', "用户id:{$param['id']}");
            }
            return json(msg($flag['code'], $flag['data'], lang($flag['msg'])));
        }
        $id = input('param.id');
        $info = $user->getOneUser($id);
        if (empty($info)) {
            $this->error(lang("用户信息不存在"));
        }
        $info['old_leader1'] = isset($info['leader1']) ? $info['leader1'] : '-';
//        $info['update_time'] = $info['update_time'] ? date("Y-m-d H:i:s", $info['update_time']) : "-";
        $level = new LevelModel();
        if ($info['leader1']) {
            $info['parent'] = $user->getOneUser($info['leader1']);
        }
        $banks = i18n_banks2();
        $this->assign([
            'level' => $level->getLevel(),
            'user' => $info,
            'banks' => $banks,
            'status' => config('user_status')
        ]);
        return $this->fetch();
    }

    // 删除用户
    public function userDel() {
        $id = input('param.id');
        $ids = explode(",", $id);
        if (empty($ids)) {
            return msg(-1, '', '参数有误');
        }
        foreach ($ids as $id) {
            MemberModel::destroy($id);
            insert_operation_log('删除会员', "用户id:{$id}");
        }
        return json(msg(1, '', lang('删除用户成功')));
    }
    
    public function userBatch() {
        $param= input('param.');
        $result=false;
        $msg='操作完成';
        if (request()->isPost()) {
            if(isset($param['ids']) && !empty($param['ids'])){
                $ids= $param['ids'];
                //批量禁用
                if(isset($param['disable_member'])){
                    $status= $param['disable_member'];
                    if($status>0){
                        MemberModel::where(['id'=>['in',$ids]])->update(['status'=>$status]);
                        $result=true;
                    }
                    $msg=($status==2?"封停":"解封")."用户".($result?"成功":"失败");
                }
            }
            return json(msg($result, '', lang($msg)));
        }
    }

    // 账户调整
    public function money() {
        $money_type = config("my.money_type");
        $uid = input('uid/d');
        $this->assign('uid', $uid);
        $this->assign('money_type', $money_type);
        return $this->fetch();
    }

    // 调整积分
    public function moneyAdd() {
        if (request()->isPost()) {
            $param = $this->request->param();
            unset($param['code']);
            if ($param['money'] == 0) {
                $this->error(lang('金额有误'));
            }
            if (!in_array($param['usertype'], [1,2,3])) {
                $this->error(lang('请选择操作类型'));
            }
            $redis = Cache::store('redis');
            $redis_key = "money_add:{$param['user_id']}";
            if ($redis->has($redis_key)) {
                $this->error(lang('请勿重复操作'));
            }
            $user = MemberModel::where('id', $param['user_id'])->find();
            $redis->set($redis_key, 1, 3);
            $userid = $user->id;
            
            if ($param['usertype'] == 1) { //彩金
                $param['remarks'] = "管理员操作彩金:{$param['remarks']}";
                $user_money = Db::name('home_user')->where(['id' => $param['user_id']])->value('user_money');
                if ($param['money'] < 0) {
                    if ($user->user_money < abs($param['money'])){
                        $this->error(lang('提现金额超出用户金额'));
                    }
                    $param['remarks'] = "管理员操作提现彩金:{$param['remarks']}";
                    $data = [
                        'order_id' => genOrderSN(random(4, 'number'), 'MU'),
                        'user_id' => $userid,
                        'money' => abs($param['money']),
                        'car_num' => '9999999999',
                        'type' => 1,
                        'is_xuni' => 1,
                        'remark' => '手动补单',
                        'status' => 2,
                        'check_time'  => time(),
                        'create_time' => time()
                    ];
                    $ret = Db::name('withdrawals')->insertGetId($data);
                }else{
                    $param['remarks'] = "管理员充值彩金:{$param['remarks']}";
                    $data = [
                        'order_id' => genOrderSN(random(4, 'number'), 'MU'),
                        'user_id' => $userid,
                        'money' => $param['money'],
                        'car_num' => '9999999999',
                        'type' => 1,
                        'is_xuni' => 1,
                        'remark' => '手动补单',
                        'status' => 2,
                        'create_time' => time()
                    ];
                    $ret = Db::name('recharge')->insertGetId($data);
                }
                if ($param['money'] < 0 && $user_money < abs($param['money'])) {
                    $this->error(lang('扣减金额').$param['money']. lang('不能大于帐户金额').$user_money);
                }
                UserService::adminAddMoneyLog($userid, $param['money'], 0, $param['remarks'], session('id', ''));
            }elseif ($param['usertype'] == 2) { //充值
                if ($param['money'] < 0) {
                    if ($user->user_money < abs($param['money'])){
                        $this->error(lang('提现金额超出用户金额'));
                    }
                    $param['remarks'] = "管理员操作提现:{$param['remarks']}";
                    $data = [
                        'order_id' => genOrderSN(random(4, 'number'), 'MU'),
                        'user_id' => $userid,
                        'money' => abs($param['money']),
                        'car_num' => '9999999999',
                        'type' => 1,
                        'is_xuni' => $user->is_xuni,
                        'remark' => '手动补单',
                        'status' => 2,
                        'check_time'  => time(),
                        'create_time' => time()
                    ];
                    $ret = Db::name('withdrawals')->insertGetId($data);
                }else{
                    $param['remarks'] = "管理员操作充值:{$param['remarks']}";
                    $data = [
                        'order_id' => genOrderSN(random(4, 'number'), 'MU'),
                        'user_id' => $userid,
                        'money' => $param['money'],
                        'car_num' => '9999999999',
                        'type' => 1,
                        'is_xuni' => $user->is_xuni,
                        'remark' => '手动补单',
                        'status' => 2,
                        'create_time' => time()
                    ];
                    $ret = Db::name('recharge')->insertGetId($data);
                }
                if ($ret) {
                    UserService::adminAddMoneyLog($userid, $param['money'], 1, $param['remarks'], session('id', ''));
                    $model = new \app\common\model\UserModel();
                    $user = $model->getOneUser($userid);
                    if ($param['money'] > 0){
                        if ($user->level_id == 0) {
                            $level = Db::name('home_user_level')->order('money','ASC')->find();
                            $user->level_id = 1;
                            $user->pd_num = $level['dannum'];
                            $user->save();
                        }else{
                            $level = Db::name('home_user_level')->where('money','>=',$param['money'])->order('money','ASC')->find();
                            if ($level['level_id'] > $user->level_id){
                                $user->level_id = $level;
                                $user->pd_num = $level['dannum'];
                                $user->save();
                            }
                        }
                    }
                    insert_operation_log('充值:手动补单', "订单号:{$data['order_id']}");
                } else {
                    return $this->error(lang('手动补单失败'));
                }
            }else{ //积分
                $param['remarks'] = "管理员操作积分:{$param['remarks']}";
                UserService::adminAddMoneyLog($userid, $param['money'], 20, $param['remarks'], session('id', ''));
            }
            $this->success(lang('操作成功'), url('member/money', ['uid'=>$param['user_id']]));
        }
    }

    public function edit_parent() {
        $id = $this->request->param('userid/d');
        $user = MemberModel::where('id', $id)->find();
        if (empty($user)) {
            $this->error(lang("用户信息不存在"));
        }
        if ($this->request->isAjax()) {
            $redis = Cache::store('redis');
            $redis_key = "chanage_leader1_{$id}";
            if ($redis->has($redis_key)) {
                $this->error(lang("请勿重复提交,正在修改中..."));
            }
            $leader1 = $this->request->param('leader1/s', 0);
            if ($leader1 == $user->leader1) {
                $this->error(lang("修改的上级ID和原有用户上级ID一致,无须修改"));
            }
            $leader1_info = MemberModel::where('id', $leader1)->find();
            if (!$leader1_info) {
                $this->error(lang("修改的上级ID有误"));
            }
            insert_operation_log('修改会员上级id', "用户ID:{$id},原上级ID:{$user->leader1},修改的上级ID:{$leader1}");
            $user->leader1 = $leader1;
            $user->save();
            Queue::push("ChangeLeader1", $id);
            $redis->set($redis_key, 1);
            $this->success(lang("修改成功"));
        }
        
        $child_count = MemberModel::whereLike('tree_id', "%/{$id}/%")->count();
        $this->assign('user', $user);
        $this->assign('child_count', $child_count);
        return $this->fetch();
    }
    
    public function setcp() {
        $id = $this->request->param('user_id/d');
        $user = MemberModel::where('id', $id)->find();
        if (empty($user)) {
            $this->error(lang("用户信息不存在"));
        }
        if ($this->request->isAjax()) {
            $cp_id = $this->request->param('cp_id/s');
            if (empty($cp_id)) {
                $this->error(lang("请填写CP ID"));
            }
            if ($cp_id == $user->cp_id) {
                $this->error(lang("修改的CPID和原有用户CPID一致,无须修改"));
            }
            $cp_info = MemberModel::where('id|mobile|invite_code', $cp_id)->find();
            if (!$cp_info) {
                $this->error(lang("填写的修改CP信息有误"));
            }
            insert_operation_log('修改会员CPid', "用户ID:{$id},原CPID:{$user->cp_id},修改后的CPID:{$cp_info->id}");
            $user->cp_id = $cp_info->id;
            $user->save();
            $this->success(lang("修改成功"));
        }
        
        $this->assign('user', $user);
        return $this->fetch();
    }

    /**
     * 取消cp
     */
    public function cancelcp(){
        $id = $this->request->param('user_id/d');
        $user = MemberModel::where('id', $id)->find();
        if (empty($user)) {
            $this->error(lang("用户信息不存在"));
        }
        if (0 == $user->cp_id) {
            $this->error(lang("修改的CPID和原有用户CPID一致,无须修改"));
        }
        insert_operation_log('修改会员CPid', "用户ID:{$id},原CPID:{$user->cp_id},修改后的CPID:0");
        $user->cp_id = 0;
        $user->save();
        Db::name('order')->where('specify_user_id',$id)->where('status',1)->update(['status'=>3,'paid_status'=>3]);
        $this->success(lang("修改成功"));
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
            '编辑' => [
                'auth' => 'member/useredit',
                'href' => url('member/userEdit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '账户' => [
                'auth' => 'member/money',
                'href' => url('member/money', ['uid' => $id]),
                'btnStyle' => 'success',
                'icon' => 'fa fa-comment'
            ],
            '删除' => [
                'auth' => 'member/userdel',
                'href' => "javascript:userDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

}
