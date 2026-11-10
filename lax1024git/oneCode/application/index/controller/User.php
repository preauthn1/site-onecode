<?php

namespace app\index\controller;

use app\common\model\ConfigModel;
use app\common\model\UserModel;
use app\common\model\CollectModel;
use think\Db;
use app\services\UserService;

class User extends Base {

    // 获取用户信息
    public function index() {
        $this->setTitle(lang('我的'));
        $userid = $this->userid;
        $userModel = new UserModel();
        $userInfo = $userModel->getOneUser($userid);
        if (!$userInfo) {
            session('?username', null, 'index');
            session('?userid', null, 'index');
            $loginUrl = url('login/index');
            $this->redirect($loginUrl);
        }
        $userInfo['user_money'] = i18n_currency($userInfo['user_money']);
//        $userInfo['tyj_money'] = i18n_currency($userInfo['tyj_money']);

        $full_table_name = getMoneyLogTableByDay();
        $ret = Db::table($full_table_name)->where('user_id', $userid)->where('type', 4)->sum('money');
        $userInfo['todaysy_money'] = i18n_currency($ret);
        $userInfo['earnings_money'] = i18n_currency($userInfo['earnings_money']+$ret);

        // 余额宝金额
        $yeb_total = Db::name('yuebao')->where('user_id', $userid)->where('status', 1)->sum('principal');
        // 预计收益
        $expected_return = Db::name('yuebao')->where('user_id', $userid)->where('status', 1)->sum('gray');
        $navs = i18n_home_navs(2);
        $this->assign([
            'userInfo' => $userInfo,
            'yeb_total' => i18n_currency($yeb_total),
            'expected_return' => i18n_currency($expected_return),
            'navs' => $navs
        ]);
        return $this->fetch();
    }

    public function collect() {
        if ($this->request->isAjax()) {
            $type = $this->request->param('type/d', 0);
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('pageSize/d', 10);
            $offset = ($page >= 1 ? $page - 1 : 0) * $limit;
            $where = [];
            $where['user_id'] = $this->userid;
            $lists = CollectModel::with(['goods'])->where($where)->limit($offset, $limit)->order('id desc')->select();
            
            if ($lists) {
                $content = $this->fetch('user/module/item_collect', ['lists' => $lists]);
                $this->success('ok', '', $content);
            } else {
                $this->error(lang('暂无数据'));
            }
        }
        $this->setTitle(lang('我的收藏'));
        return $this->fetch();
    }

    // 编辑头像
    public function avatar() {
        $this->setTitle(lang('头像设置'));
        $userid = $this->userid;
        if (request()->isAjax()) {
            $avatar = input('avatar');
            $ret = Db::name('home_user')->where(['id' => $userid])->update(['avatar' => $avatar]);
            if ($ret) {
                return json(msg(200, '', lang('修改成功')));
            } else {
                return json(msg(-1, '', lang('修改失败')));
            }
        } else {
            // 获取头像图片
            $lists = headLists();
            $this->assign([
                'lists' => $lists
            ]);
            return $this->fetch();
        }
    }

    // 编辑资料
    public function bind_card() {
        $this->setTitle(lang('绑定银行卡'));
        $userid = $this->userid;
        $banks = i18n_banks2();
//        dump($banks);die;
        if (request()->isAjax()) {
            $data = input('post.');
            $paypwd_hash = md5($data['paypwd'] . config('salt'));   // 密码
            if (!$this->userInfo['paypwd']) {
                return json(msg(-1, '', lang('请先设置提现密码')));
            }
            if ($this->userInfo['paypwd'] != $paypwd_hash && !password_verify($data['paypwd'], $this->userInfo['paypwd'])) {
                return json(msg(-1, '', lang('提现密码错误')));
            }
            if (empty($data["true_name"])) {
                return json(msg(-1, '', lang('请输入真实姓名')));
            }
            if (empty($data["bank_number"])) {
                return json(msg(-1, '', lang('请输入银行卡号')));
            }
            if (!validNumber($data["bank_number"])) {
                return json(msg(-1, '', lang('银行卡号只能是数字')));
            }
            if (!i18n_bank_valid2($data["bank_name"], $data["bank_number"])) {
                return json(msg(-1, '', lang('银行号码不正确,请核对后绑定')));
            }
            $update = [
                "true_name" => strval($data["true_name"]),
                "bank_number" => strval($data["bank_number"]),
                "bank_name" => i18n_bank_code($data["bank_name"]),
                "bank_kh" => isset($data["bank_kh"]) ? $data["bank_kh"] : '',
                "bank_address" => isset($data["bank_address"]) ? $data["bank_address"] : '',
                "alipay" => isset($data["alipay"]) ? strval($data["alipay"]) : '',
//                "qqnum" => $data["qqnum"],
                "update_time" => time()
            ];
            if ($this->userInfo['true_name'] != '') {
                unset($update['true_name']);
            }
            if ($this->userInfo['bank_number'] != '') {
                unset($update['bank_number']);
            }else{
                $exists = Db::name('home_user')->where([
                    'bank_number' => $update['bank_number'],
                    'id' => ["<>", $userid]
                ])->find();
                if (!empty($exists)) {
                    return json(msg(-1, '', lang('该银行卡已经绑定其他账号')));
                } 
            }
            $ret = Db::name('home_user')->where(['id' => $userid])->update($update);
            if ($ret !== false) {
                return json(msg(200, '', lang('修改成功')));
            } else {
                return json(msg(-1, '', lang('修改失败')));
            }
        } else {
            $_banks_json = [];
            $curr_bank = [
                'bank' => '',
                'icon' => '',
            ];
            foreach ($banks as $k => $v){
                if ($this->userInfo['bank_name'] == $v['bank_code']) {
                    $curr_bank['bank'] = $v['name'];
                    $curr_bank['icon'] = $v['logo'];
                }
                $_banks_json[] = ['id' => $v['bank_code'], 'value' => "({$v['bank_code']}) " . $v['name']];
            }
            $this->userInfo['bank_number'] = substr($this->userInfo['bank_number'],0,3).'********'.substr($this->userInfo['bank_number'],strlen($this->userInfo['bank_number'])-5,5);
            $this->assign('userInfo', $this->userInfo);
            $this->assign('banks', $banks);
            $this->assign('banks_json', json_encode($_banks_json));
            $this->assign('curr_bank', $curr_bank);
            return $this->fetch();
        }
    }
    // 编辑资料
    public function usdt() {
        $this->setTitle(lang('绑定USDT'));
        if (request()->isAjax()) {
            $data = input('post.');
            $paypwd_hash = md5($data['paypwd'] . config('salt'));   // 密码
            if (!$this->userInfo['paypwd']) {
                $this->error(lang('请先设置提现密码'));
            }
            if ($this->userInfo['paypwd'] != $paypwd_hash && !password_verify($data['paypwd'], $this->userInfo['paypwd'])) {
                $this->error(lang('提现密码错误'));
            }
            if (empty($data["usdt_type"])) {
                $this->error(lang('请选择USDT类型'));
            }
            if (empty($data["usdt"])) {
                $this->error(lang('请输入USDT地址'));
            }
            $update = [
                "usdt_type" => strval($data["usdt_type"]),
                "usdt" => strval($data["usdt"]),
                "update_time" => time()
            ];
            if ($this->userInfo['usdt'] != '') {
                unset($update['usdt']);
            }else{
                $exists = Db::name('home_user')->where([
                    'usdt' => $update['usdt'],
                    'id' => ["<>", $this->userid]
                ])->find();
                if (!empty($exists)) {
                    $this->error(lang('该USDT地址已经绑定其他账号'));
                }
            }
            $ret = Db::name('home_user')->where(['id' => $this->userid])->update($update);
            if ($ret !== false) {
                $this->success(lang('修改成功'));
            } else {
                $this->error(lang('修改失败'));
            }
        }
        return $this->fetch();
    }

    // 修改密码
    public function pass_word() {
        $this->setTitle(lang('密码管理'));
        $userid = $this->userid;
        $type = $this->request->param('type/s');
        $this->assign('type', $type);
        if (request()->isAjax()) {
            $step = input('step/d', 2);
            $data = [];
            if ($step == 2) { //登录密码
                $oldpassword = input('oldpassword');
                $password = input('password');
                $repassword = input('repassword');
                if (!$password) {
                    return json(msg(-1, '', lang('缺少必要参数')));
                }
                if ($password != $repassword) {
                    return json(msg(-1, '', lang('两次密码不一致')));
                }
                $info = Db::name('home_user')->where(['id' => $userid])->find();
                $oldpassword2 = md5($oldpassword . config('salt'));   // 密码
                if ($info['password'] != $oldpassword2 && !password_verify($oldpassword, $info['password'])) {
                    return json(msg(-1, '', lang('旧密码错误')));
                }
                $data['password'] = md5($password . config('salt'));   // 密码
            }elseif($step == 3) {//支付密码
                $oldpaypwd = input("oldpaypwd");  // 支付密码
                $paypwd = input("paypwd");  // 支付密码
                $repaypwd = input("repaypwd");  // 支付密码
                if (!$paypwd) {
                    return json(msg(-1, '', lang('请输入提现密码')));
                }
                if ($paypwd != $repaypwd) {
                    return json(msg(-1, '', lang('两次密码不一致')));
                }
                
                $info = Db::name('home_user')->where(['id' => $userid])->find();
                $oldpaypwd2 = md5($oldpaypwd . config('salt'));   // 密码
                if ($info['paypwd'] != '' && $info['paypwd'] != $oldpaypwd2 && !password_verify($oldpaypwd, $info['paypwd'])) {
                    return json(msg(-1, '', lang('旧提现密码错误')));
                }
                $data['paypwd'] = md5($paypwd . config('salt'));   // 密码
            }
            if(!empty($data)) {
                $ret = Db::name('home_user')->where(['id' => $userid])->update($data);
                if ($ret !== false) {
                    if ($step == 2) { //修改登录密码后需要重新登录
                        session('username',   null, 'index');
                        session('userid',     null, 'index');
                        session('checkTimes', null, 'index');
                        session('is_demo',    null, 'index');
                    }
                    return json(msg(200, '', lang('修改成功') . ($step == 2 ? "," . lang('请重新登录') : "")));
                } else {
                    return json(msg(-1, '', lang('修改失败')));
                }
            }else{
                return json(msg(-1, '', lang('缺少必要参数')));
            }
        } else {
            return $this->fetch();
        }
    }

    // 分享
    public function share() {
        $this->setTitle(lang('推广海报'));
        $userid = $this->userid;
        $invite_code = Db::name('home_user')->where('id', $userid)->value('invite_code');
        $web_url = getWebUrl();
        $share_type = \think\Env::get('share_type', 2);
        if ($share_type == 1) {
            $share_url = $web_url . url('login/reg', ['inviteCode' => $invite_code]);
        }else{
            $share_url = $web_url . "/download/{$invite_code}";
        }
        $this->assign([
            'invite_code' => $invite_code,
            'share_url' => $share_url,
        ]);
        return $this->fetch();
    }

    // 收入明细
    public function money_log() {
        $this->setTitle(lang('账户明细'));
        $userid = $this->userid; // 用户ID
        $start_time = strtotime('-7 days');
        $end_time = strtotime('+1 days');
        $get_start_time = input('start_time') ? strtotime(input('start_time')) : '';
        $get_end_time = input('end_time') ? strtotime(input('end_time')) + 24 * 3600 : '';
        if ($get_start_time)
            $start_time = $get_start_time;
        if ($get_end_time)
            $end_time = $get_end_time;

        if (request()->isAjax()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('pageSize/d', 10);
            $offset = ($page >= 1 ? $page - 1 : 0) * $limit;
            $where['user_id'] = $userid;
            $where_str = ["user_id = '{$userid}'"];
//            if ($type) {
//                $where['type'] = $type;
//                $where_str[] = "type = '{$type}'";
//            }
            $where['create_time'] = ['between', [$start_time, $end_time]];
            $where_str[] = "create_time BETWEEN '{$start_time}' AND '{$end_time}'";
            $lists = [];
            $faker_data = getFakeData($userid);
            if (!empty($faker_data) && $faker_data['money_log']) {
                $money_log = explode("\n", $faker_data['money_log']);
                $user_money = 0;
                foreach ($money_log as $item) {
                    list($money, $type, $create_time) = explode(",", "{$item},,,");
                    $user_money += $money;
                    if ((isset($where['type']) && $where['type'] == $type) || empty($where['type'])) {
                        $lists[] = [
                            'status' => 1,
                            'user_money' => sprintf4f($user_money),
                            'type' => $type,
                            'money' => $money,
                            'create_time' => strtotime($create_time)
                        ];
                    }
                }
                if (!empty($lists) && $page > 1) {
                    return json(msg(0, '', lang('暂无数据')));
                }
            }
            if (empty($lists)) {
                $field = "id,is_xuni,user_id,admin_id,order_id,goods_id,from_user_id,remarks,money,user_money,type,status,create_time";
                $tables = [];
                for ($i = $start_time; $i <= $end_time; $i+=86400) {
                    $full_table_name = "qd_money_log_" . timeToStr($i, "Ymd");
                    if (existTable($full_table_name)) {
                        $tables[] = $full_table_name;
                    }
                }
                $sqls = [];
                for($i=0; $i < count($tables); $i++) {
                    $sqls[] = "SELECT {$field} FROM {$tables[$i]} WHERE " . implode(" AND ", $where_str);
                }
                $db_lists = Db::table('qd_money_log');
                if (!empty($sqls)) {
                    $db_lists = $db_lists->union($sqls, true);
                }
                $lists = $db_lists->field($field)->where($where)->limit($offset, $limit)->order('create_time desc')->select();
            }
            $money_type = config('my.money_type');
            foreach ($lists as $k => $val) {
                $val['money_type'] = 'money2';
                if ($val['type'] == '20') {
                    if ($val['money'] > 0) {
                        $val['money'] = '+' . $val['money'];
                        $val['money_type'] = 'money';
                    }else{
                        $val['money'] = $val['money'];
                    }
                    $val['user_money'] = $val['user_money'];
                }else{
                    if ($val['money'] > 0) {
                        $val['money'] = '+' . i18n_currency($val['money']);
                        $val['money_type'] = 'money';
                    }else{
                        $val['money'] = i18n_currency($val['money']);
                    }
                    $val['user_money'] = i18n_currency($val['user_money']);
                }
                $val['create_time'] = i18n_datetime2str($val['create_time']);
                $val['type'] = lang($money_type[$val['type']]);
                $lists[$k] = $val;
            }
            if ($lists) {
                $content = $this->fetch('user/module/money_log', ['lists' => $lists]);
                $this->success('ok', '', $content);
            } else {
                $this->error(lang('暂无数据'));
            }
        }
        $this->assign([
            'start_time' => date('Y-m-d', $start_time),
            'end_time' => date('Y-m-d', ($end_time - 3600 * 24))
        ]);
        return $this->fetch();
    }
    // 收入明细
    public function activity_log() {
        $this->setTitle(lang('活动记录'));
        $userid = $this->userid; // 用户ID
        $start_time = strtotime('-7 days');
        $end_time = strtotime('+1 days');
        $get_start_time = input('start_time') ? strtotime(input('start_time')) : '';
        $get_end_time = input('end_time') ? strtotime(input('end_time')) + 24 * 3600 : '';
        if ($get_start_time)
            $start_time = $get_start_time;
        if ($get_end_time)
            $end_time = $get_end_time;

        if (request()->isAjax()) {
            $page = input('page/d') ? input('page/d') : 1;   // 页数
            $pageSize = input('pageSize/d') ? input('pageSize/d') : 10;  // 每页条数
            $offset = ($page - 1) * $pageSize;
            $limit = $pageSize;
            $where['user_id'] = $userid;
            $where['create_time'] = ['between', [$start_time, $end_time]];
            $lists = Db::name('activity_record')->where($where)->limit($offset, $limit)->order('id desc')->select();
            $a_title = \app\common\model\ActivitynewModel::column('title', 'id');
            foreach ($lists as $k => $val) {
                $val['activity_title'] = isset($a_title[$val['activity_id']]) ? lang($a_title[$val['activity_id']]) : $val['activity_id'];
                $val['create_time'] = i18n_datetime2str($val['create_time']);
                if ($val['reward_type'] == 1) {
                    $val['reward_text'] = i18n_currency($val['reward']);
                    $val['user_money'] = i18n_currency($val['befor_num']+$val['reward']);
                }else{
                    $val['reward_text'] = i18n_sprintf($val['reward']);
                    $val['user_money'] = i18n_sprintf($val['befor_num']+$val['reward']);
                }
                $val['from_user_id'] = $val['from_user_id'] > 0 ? $val['from_user_id'] : '-';
                
                $lists[$k] = $val;
            }
            if ($lists) {
                return json(msg(200, $lists, lang('获取成功')));
            } else {
                return json(msg(0, [], lang('暂无数据')));
            }
        }
        $this->assign([
            'start_time' => date('Y-m-d', $start_time),
            'end_time' => date('Y-m-d', ($end_time - 3600 * 24))
        ]);
        return $this->fetch();
    }

    // 提现申请
    public function cash() {
        $this->setTitle(lang('提现'));
        $userid = $this->userid; // 用户ID
        $type = $this->request->param('type/d', 1);
        //手续费扣除方式 0从余额中扣除，1从提现金额中扣除
        $withdraw_rate_method = intval($this->configInfo['withdraw_rate_method']);
        if (request()->isAjax()) {
            if ($this->userInfo['is_demo'] == 1) {
                return $this->result([], -1, lang('测试账号，不能使用该功能'));
            }
            if (isset($this->configInfo['withdraw_open']) && $this->configInfo['withdraw_open'] == 0) {
                return $this->result([], -1, lang('提现系统维护中'));
            }
            if (isset($this->configInfo['withdraw_day']) && $this->configInfo['withdraw_day'] != '') {
                $withdraw_day = explode(",", $this->configInfo['withdraw_day']);
                if (!in_array(date('w'), $withdraw_day)) {
                    return $this->result([], -1, lang('今天不能提现'));
                }
            }
            if (isset($this->configInfo['withdraw_time_start']) && !empty($this->configInfo['withdraw_time_start']) 
                    && isset($this->configInfo['withdraw_time_end']) && !empty($this->configInfo['withdraw_time_end'])
            ) {
                $time = time();  // 时间
                $start_time = strtotime(date('Y-m-d') . $this->configInfo['withdraw_time_start']);
                $end_time = strtotime(date('Y-m-d') . $this->configInfo['withdraw_time_end']);
                if ($time < $start_time || $time > $end_time) {
                    return $this->result([], -1, lang('提现时间为') . ":" . $this->configInfo['withdraw_time_start'] . '-' . $this->configInfo['withdraw_time_end']);
                }
            }
            
            $lock = userLock($this->userid, 'usercash', 60);
            if (!$lock) {
                return $this->result([], -1, lang('请勿重复提交'));
            }
            if (UserService::isActivity($this->userid)) {
                return $this->result([], -1, lang('请完成活动任务之后进行提款'));
            }
            $money = input('money/d');
            $paypwd = input('paypwd/s');
            $type = input('type/d');
            $paypwd_hash = md5($paypwd . config('salt'));   // 密码
            $taxfee = 0;
            $withdraw_rate = UserService::getUserWithdrawRate($this->userid);
            if ($withdraw_rate > 0) { //提现手续费
                $taxfee = sprintf("%.2f", $money * $withdraw_rate / 100);
            }
            //免手续费金额
            if (isset($this->configInfo['free_withdraw_amount']) && $this->configInfo['free_withdraw_amount'] > 0 && $money <= intval($this->configInfo['free_withdraw_amount'])) {
                $taxfee = 0;
            }
            
            if ($this->userInfo['paypwd'] != $paypwd_hash) {
                return $this->result([], -1, lang('提现密码错误'));
            }
            if (!in_array($type, [0, 1, 4])) {
                return $this->result([], -1, lang('提现类型有误'));
            }
            $total = $withdraw_rate_method == 1 ? $money : $money + $taxfee;
            if ($this->userInfo['user_money'] < $total) {
                return $this->result([], -1, lang('余额不足'));
            }
            if ($money < $this->configInfo['tx_min']) {
                return $this->result([], -1, lang('提现金额小于'.$this->configInfo['tx_min'].'，订单提交失败'));
            }
            if ($money > $this->configInfo['tx_max']) {
                return $this->result([], -1, lang('提现金额大于'.$this->configInfo['tx_max'].'，订单提交失败'));
            }
            $day_count = isset($this->configInfo['day_tx_count']) ? intval($this->configInfo['day_tx_count']) : 1;
            $count = Db::name('withdrawals')->where('user_id', $userid)->whereTime("create_time", 'today')->count();
            if ($day_count > 0 && $count >= $day_count) {
                return $this->result([], -1, lang('每天只能申请一次提现', [$day_count]));
            }
            
            $withdraw_limit = UserService::getUserWithdrawLimit($this->userid);
            if (isArrayAndNotEmpty($withdraw_limit)) {
                return $this->result([], -1, lang($withdraw_limit['remark']));
            }
            
            if ($type == 1 && (empty($this->userInfo['bank_number']) || empty($this->userInfo['bank_name']))) {
                return $this->result([], -1, lang('请绑定银行卡'));
            }elseif($type == 0 && empty ($this->userInfo['alipay'])) {
                return $this->result([], -1, lang('请绑定支付宝'));
            }
            
            $data = [
                'user_id' => $userid,
                'order_id' => genOrderSN(random(6, 'number'), 'W'),
                'exchange_rate' => $type == 4 ? getConfig('exchange_rate') : 1,
                'type' => $type,
                'money' => $withdraw_rate_method == 0 ? $money : sprintf4f($money - $taxfee),
                'taxfee' => $taxfee,
                'true_name' => $this->userInfo['true_name'],
                'bank_name' => i18n_bank_code($this->userInfo['bank_name']),
                'bank_number' => $type == 1 ? $this->userInfo['bank_number'] : $this->userInfo['alipay'],
                'bank_kh' => $this->userInfo['bank_kh'],
                'bank_address' => $this->userInfo['bank_address'],
                'is_xuni' => $this->userInfo['is_xuni'],
                'ip' => getip(),
                'create_time' => time()
            ];
            
            $ret = Db::name('withdrawals')->insert($data);
            if ($ret) {
                UserService::addMoneyLog($userid, -1 * $total, 7, '', 1);
                checkLevel($userid); // 会员升级
                return $this->result([], 200, lang('申请成功'));
            } else {
                return $this->result([], -1, lang('申请失败'));
            }
        }
        
        $taxfee = 0;
        $withdraw_rate = UserService::getUserWithdrawRate($this->userid);
        if ($withdraw_rate > 0 && $withdraw_rate_method == 0)  {
            $taxfee = sprintf("%.2f", $this->userInfo['user_money'] * floatval($withdraw_rate) / 100); //千分之6的手续费
        }
        
        $this->assign([
            'config' => $this->configInfo,
            'max_withdraw' => $withdraw_rate_method == 0 ? intval($this->userInfo['user_money'] - $taxfee) : intval($this->userInfo['user_money']),
        ]);
        $this->assign('symbol', i18n_symbol());
        $this->assign('type', $type);
        return $this->fetch();
    }

    // 提现明细
    public function cash_log() {
        $this->setTitle(lang('提现记录'));
        if (request()->isAjax()) {
            $page = $this->request->param('page/d', 1);   // 页数
            $limit = $this->request->param('pageSize/d', 10);   // 每页条数
            $offset = ($page - 1) * $limit;
            $where['user_id'] = $this->userid;

            $lists = [];
            $faker_data = getFakeData($this->userid);
            if (!empty($faker_data) && $faker_data['cash_log']) {
                $money_log = explode("\n", $faker_data['cash_log']);
                $user_money = 0;
                foreach ($money_log as $item) {
                    list($money, $bank_number, $create_time, $remark) = explode(",", "{$item},,,,");
                    $user_money += $money;
                    $lists[] = [
                        'status' => 2,
                        'bank_number' => $bank_number,
                        'money' => $money,
                        'remark' => trim($remark),
                        'create_time' => strtotime($create_time)
                    ];
                }
                if (!empty($lists) && $page > 1) {
                    return json(msg(0, '', lang('暂无数据')));
                }
            }
            if (empty($lists)) {
                $lists = Db::name('withdrawals')->where($where)->limit($offset, $limit)->order('id DESC')->select();
            }
            $withdrawals_status = config('withdrawals_status');
            foreach ($lists as $k => $val) {
                $val['create_time'] = i18n_datetime2str($val['create_time']);
                if ($val['check_time']) {
                    $val['update_time'] = i18n_datetime2str($val['check_time']);
                }
                $val['money'] = $val['money'];
                $val['status_str'] = isset($withdrawals_status[$val['status']]) ? lang($withdrawals_status[$val['status']]) : '-';
                $val['remark'] = isset($val['remark']) && $val['remark'] ? lang($val['remark']) : '-';
                $lists[$k] = $val;
            }
            if ($lists) {
                $content = $this->fetch('user/module/item_cash', ['lists' => $lists]);
                $this->success('ok', '', $content);
            } else {
                $this->error(lang('暂无数据'));
            }
        }
        $this->assign([
            'status' => config('withdrawals_status'),
            'isHtml5Plus' => false
        ]);
        return $this->fetch();
    }

    public function team_list() {
        $this->setTitle(lang('团队报表'));
        $userid = $this->userid; // 用户ID
        $config = new ConfigModel();
        $configInfo = $config->getConfig();

        $levels = [];
        $fx_level = $configInfo['fx_level'];
        for ($i = 1; $i <= $fx_level; $i++) {
            $levels[] = config('fx_level')[$i];
        }

        $start_time = strtotime('-7 days');
        $end_time = strtotime('+1 days');

        if (request()->isAjax()) {
            $lists = [];
            $faker_data = getFakeData($userid);
            $type = input('type/d') ? input('type/d') : 1;   // 类型
            $page = input('page/d') ? input('page/d') : 1;   // 页数
            $pageSize = input('pageSize/d') ? input('pageSize/d') : 10;  // 每页条数
            $offset = ($page - 1) * $pageSize;
            $limit = $pageSize;
            $where = [];
            $feild = 'leader' . $type;
            $where[$feild] = $userid;
            if (!empty($faker_data)) {
                if ($type == 1) {
                    $faker_users = explode("\n", $faker_data['one_lv_users']);
                } elseif ($type == 2) {
                    $faker_users = explode("\n", $faker_data['two_lv_users']);
                } else {
                    $faker_users = explode("\n", $faker_data['three_lv_users']);
                }
                foreach ($faker_users as $item) {
                    list($nick_name, $user_money, $mobile, $create_time) = explode(",", "{$item},,,,");
                    $lists[] = [
                        'nick_name' => $nick_name,
                        'user_money' => $user_money,
                        'mobile' => $mobile,
                        'avatar' => mt_rand(1, 100),
                        'create_time' => strtotime($create_time)
                    ];
                }
                if (!empty($lists) && $page > 1) {
                    return json(msg(0, ['lists' => []], lang('获取成功')));
                }
            }
            if (empty($lists)) {
                $lists = Db::name('home_user')->field('id,nick_name,user_money,mobile,avatar,create_time')->where($where)->whereNull('delete_time')->limit($offset, $limit)->order('id DESC')->select();
            }
            foreach ($lists as $k => $val) {
                $val['create_time'] = i18n_datetime2str($val['create_time']);
                $val['user_money'] = i18n_currency($val['user_money']);
                $val['avatar'] = toAvatar($val['avatar']);
                $lists[$k] = $val;
            }
            $data = [
                'lists' => $lists,
            ];
            return json(msg(200, $data, lang('获取成功')));
        }
        
        $redis = \think\Cache::store('redis');
        $redis_key = "team_list:{$this->userid}";
        if ($redis->has($redis_key)) {
            $data = $redis->get($redis_key);
        }else{
            $fx_level_new_count = [];
            $faker_data = getFakeData($userid);
            if (!empty($faker_data)) {
                // 团队信息
                $teamcount = $faker_data['teamcount'];   // 团队总人数
                $teammoney = 0;   // 团队余额
                $nextcount = $faker_data['nextcount'];   // 直推人数
                $liushui = 0; // 团队流水
                $teamcash = 0; // 团队总提现
                $teamrecharge = 0; // 团队总充值
                $teamgray = $faker_data['teamgray'];    // 团队总佣金
                $fristcz = $faker_data['fristcz'];     // 首充人数
                $newcount = $faker_data['newcount'];    // 新增人数
                $nowcount = $faker_data['nowcount']; // 活跃人数
            } else {
                // 团队信息
                $teamcount = getTeamCount($userid);   // 团队总人数
                $teammoney = 0; //getTeamMoney($userid);   // 团队余额
                $nextcount = getNextCount($userid);   // 直推人数
                $liushui = 0; //getTeamLiushui($userid); // 团队流水
                $teamcash = 0; //getTeamLiushui($userid); // 团队总提现
                $teamrecharge = 0; //getTeamLiushui($userid); // 团队总充值
                $teamgray = getTeamGray2($userid);    // 团队总佣金
                $fristcz = getFristcz($userid);     // 首充人数
                $newcount = getNewCount($userid);    // 新增人数
                $nowcount = getNowCount($userid); // 活跃人数
                for($i=1; $i <= $configInfo['fx_level']; $i++) {
                    $fx_level_new_count[] = getLevelNewCount($userid, $i);
                }
            }
            $data = [
                'fx_level' => $levels,
                'fx_level_new_count' => $fx_level_new_count,
                'teamcount' => $teamcount,
                'teammoney' => $teammoney,
                'nextcount' => $nextcount,
                'liushui' => $liushui,
                'teamcash' => $teamcash,
                'teamrecharge' => $teamrecharge,
                'teamgray' => $teamgray,
                'fristcz' => $fristcz,
                'newcount' => $newcount,
                'nowcount' => $nowcount,
            ];
            $redis->set($redis_key, $data, 60);
        }
        $this->assign($data);
        return $this->fetch();
    }
    

}
