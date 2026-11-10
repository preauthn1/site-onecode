<?php

error_reporting(E_ERROR | E_PARSE);

//时间相关函数
require_once(__dir__ . '/functions/time.php');
//验证相关函数
require_once(__dir__ . '/functions/valid.php');
//字符串相关函数
require_once(__dir__ . '/functions/string.php');
//加密相关函数
require_once(__dir__ . '/functions/encrypt.php');
//数组相关函数
require_once(__dir__ . '/functions/array.php');
//其他函数
require_once(__dir__ . '/functions/other.php');
//多语言函数
require_once(__dir__ . '/functions/i18n.php');

use app\common\model\ConfigModel;
use think\Db;
use think\Lang;

/**
 * 统一返回信息
 * @param $code
 * @param $data
 * @param $msg
 */
function msg($code, $data, $msg) {
    return compact('code', 'data', 'msg');
}

function msgError($code, $msg) {
    return msg($code, [], $msg);
}

function getLeaderFieldName() {
    $level = getConfig('fx_level');
    $field = [];
    for ($i = 1; $i <= $level; $i++) {
        $field[] = "leader{$i}";
    }

    return $field;
}

// 判断是否升级
function checkLevel($userId, $cache = false) {
    return true;
}

/**
 * 余额变更记录
 * @param int $user_id 用户ID
 * @param int $money 变更金额
 * @param int $type 变更类型
 * @param string $remarks 备注
 * @param int $is_frozen 是否是冻结金额
 * @param int $from_user_id 来自某个用户ID
 * @param int $goods_id 商品ID
 * @param int $order_id 订单ID
 * @param int $admin_id 管理员ID
 * @return boolean
 */
function addMoneyLog($user_id, $money, $type = 0, $remarks = '', $is_frozen = 0, $from_user_id = 0, $goods_id = 0, $order_id = 0, $admin_id = 0) {
    return \app\services\UserService::addMoneyLog($user_id, $money, $type, $remarks, $is_frozen, $from_user_id, $order_id, $admin_id);
}

/**
 * 创建当日变帐统计表
 * @param type $full_table_name
 */
function createMoneyLogDayTable($full_table_name) {
    //创建当日变帐统计表
    if (!existTable($full_table_name)) {
        $sql = <<<EOC
CREATE TABLE `{$full_table_name}` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `is_xuni` tinyint(1) DEFAULT '0' COMMENT '是否虚拟用户',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `order_id` int(10) NOT NULL DEFAULT '0' COMMENT '来自订单ID',
  `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `from_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来自会员ID',
  `remarks` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `money` decimal(20,4) DEFAULT NULL COMMENT '佣金',
  `user_money` decimal(20,4) DEFAULT NULL COMMENT '用户金额',
  `type` tinyint(5) NOT NULL DEFAULT '0' COMMENT '类型',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `create_time_idx` (`create_time`) USING BTREE,
  KEY `idx_money` (`money`) USING BTREE,
  KEY `idx_type` (`type`) USING BTREE,
  KEY `idx_user_id_type_create_time` (`user_id`,`type`,`create_time`) USING BTREE,
  KEY `idx_user_id_type_money` (`user_id`,`type`,`money`) USING BTREE,
  KEY `is_xuni` (`is_xuni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='佣金记录表';
EOC;
        Db::execute($sql);
    }
    $Ymd = timeToStr(strtotime('+1 day'), 'Ymd');
    $tomorrow_full_table_name = "qd_money_log_{$Ymd}";
    if (!existTable($tomorrow_full_table_name)) {
        createMoneyLogDayTable($tomorrow_full_table_name);
    }
}

// 打开文件夹
function headLists() {
    // 设置读取目录
    $dir = '.' . config('view_replace_str')['__COMM__'] . '/images/heads/';
    if (!is_dir($dir))
        return false;

    // 打开文件夹
    $handler = opendir($dir);
    if (!$handler)
        return false;

    $lists = [];
    while (($filename = readdir($handler)) !== false) {
        if ($filename != "." && $filename != ".." && strpos($filename, ".")) {
            $data = [
                'url' => toAvatar($filename),
                'img' => $filename
            ];
            $lists[] = $data;
        }
    }
    return $lists;
}

// 获取用户迭代关系
function getFamily($id, $field = 'invite_code') {
    $info = Db::name('home_user')->where([$field => $id])->field('leader1, leader2, leader3, leader4, leader5, leader6, leader7, id')->find();
    if (!$info)
        return msg(-1, '', '账号不存在');
    $leader1 = $info['id']; // 第1个上级
    $leader2 = $info['leader1']; // 第2个上级
    $leader3 = $info['leader2']; // 第3个上级
    $leader4 = $info['leader3']; // 第4个上级
    $leader5 = $info['leader4']; // 第5个上级
    $leader6 = $info['leader5']; // 第6个上级
    $leader7 = $info['leader6']; // 第7个上级
    $data = [
        'leader1' => $leader1,
        'leader2' => $leader2,
        'leader3' => $leader3,
        'leader4' => $leader4,
        'leader5' => $leader5,
        'leader6' => $leader6,
        'leader7' => $leader7
    ];
    return $data;
}

// 获取团队总人数
function getTeamCount($userid) {
    $count = Db::name('home_user')->whereNull('delete_time')->whereLike("tree_id", "%/{$userid}/%")->count();
    return $count;
}

// 获取直推人数
function getNextCount($userid) {
    $where['leader1'] = $userid;
    $count = Db::name('home_user')->whereNull('delete_time')->where($where)->count();
    return $count;
}

// 团队当天佣金
function getTeamGray2($userid) {
    $redis = think\Cache::store('redis');
    $redis_key = "getTeamGray2:{$userid}";
    if ($redis->has($redis_key)) {
        return $redis->get($redis_key);
    }
    $where['user_id'] = $userid;
    $where['type'] = 2;
    $Ymd = timeToStr(time(), 'Ymd');
    $full_money_table_name = "qd_money_log_{$Ymd}";
    createMoneyLogDayTable($full_money_table_name);
    $fenxiao = Db::table($full_money_table_name)->where($where)->whereTime("create_time", 'today')->sum('money');
    $gray = 0;
    $result = i18n_currency($fenxiao + $gray);
    $redis->set($redis_key, $result, 60);
    return $result;
}

// 首充人数
function getFristcz($userid) {
    if (empty($userid)) {
        return 0;
    }
    $count = Db::name('recharge')->where('user_id', "IN", function($query) use($userid) {
                $query->name('home_user')->whereNull('delete_time')->whereLike("tree_id", "%/{$userid}/%")->field('id');
            })->where("status", 2)->count('DISTINCT user_id');
    return $count;
}

// 获取新增人数
function getNewCount($userid) {
    $count = Db::name('home_user')->whereNull('delete_time')->whereLike("tree_id", "%/{$userid}/%")->whereTime('create_time', "today")->count();
    return $count;
}

// 获取活跃人数
function getNowCount($userid) {
    $count = Db::name('home_user')->whereNull('delete_time')->whereLike("tree_id", "%/{$userid}/%")->whereTime('last_login_time', "today")->count();
    return $count;
}

// 获取新增人数
function getLevelNewCount($userid, $level = '1') {
    if (!in_array($level, [1, 2, 3, 4, 5, 6, 7])) {
        return 0;
    }
    $count = Db::name('home_user')->where("leader{$level}", $userid)->whereTime('create_time', "today")->count();
    return $count;
}

// 分销
function fenxiao($userid, $orderid) {
    if (!$userid || !$orderid)
        return false;
    $userInfo = Db::name('home_user')->where(['id' => $userid, 'status' => 1])->find();
    if (!$userInfo) {
        return false;
    }
    // 已完成订单信息
    $orderInfo = Db::name('order')->where(['id' => $orderid, 'status' => 2])->find();
    if (!$orderInfo)
        return false;

    $config = new ConfigModel();
    $configInfo = $config->getConfig();

    $fx_level = $configInfo['fx_level'];
    for ($i = 1; $i <= $fx_level; $i++) {
        $feilds = 'leader' . $i;
        if (!$userInfo[$feilds]) {
            continue;
        }
        $pre_user = Db::name('home_user')->where(['id' => $userInfo[$feilds], 'status' => 1])->find();
        if ($pre_user) {
            //上级未充值不获得返佣
            if (isset($configInfo['limit_recharge_fx']) && $configInfo['limit_recharge_fx'] == 1) {
                $_count = Db::name('recharge')->where(['user_id' => $pre_user['id'], 'status' => 2])->count('id');
                if ($_count <= 0) {
                    continue;
                }
            }
            $money = sprintf4f(($orderInfo['return_bonus'] * $configInfo['fx_rate_' . $i]) / 100);
            addMoneyLog($pre_user['id'], $money, 2, '下级返佣', 0, $userid, 0, $orderid); // 返佣
        }
    }
}

// 清空账号所有数据
function clearUderInfo($userid) {
    Db::name('order')->where('user_id', $userid)->delete();
    Db::name('home_user_address')->where('user_id', $userid)->delete();
    Db::name('money_log')->where('user_id', $userid)->delete();
    Db::name('recharge')->where('user_id', $userid)->delete();
    Db::name('withdrawals')->where('user_id', $userid)->delete();
    Db::name('yuebao')->where('user_id', $userid)->delete();
    Db::name('yuebao_log')->where('user_id', $userid)->delete();
    Db::name('home_user')->where('id', $userid)->delete();
}

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

function umicrotime() {
    list($usec, $sec) = explode(" ", microtime());
    return (float) $usec;
}

// 获取团队所有用户ID
function getTeamUserid($userid) {
    return Db::name('home_user')->whereLike("tree_id", "%/{$userid}/%")->column("id");
}

// 获取团队所有用户ID
function getTeamUseridByMobile($mobile) {
    $userid = Db::name('home_user')->where('mobile', $mobile)->value("id");
    return getTeamUserid($userid);
}

// 获取所有直属用户ID
function getLeaderUserid($userid) {
    return Db::name('home_user')->where("leader1", $userid)->column("id");
}

// 获取所有直属用户ID
function getLeaderUseridByMobile($mobile) {
    $userid = getUseridByMobile($mobile);
    return getLeaderUserid($userid);
}

// 获取用户ID
function getUseridByMobile($mobile) {
    return Db::name('home_user')->where('mobile', $mobile)->value("id");
}

// 获取造假数据
function getFakeData($userid) {
    $faker_data = Db::name('faker_datas')->where('user_id', $userid)->find();
    if (empty($faker_data)) {
        return [];
    }
    foreach ($faker_data as $key => $value) {
        $faker_data[$key] = trim($value);
    }
    return $faker_data;
}

/**
 * 获取推广用户ID树
 * @param type $id
 * @param array $tree
 * @return type
 */
function getLeaderTree($id, $tree = []) {
    $tree[] = $id;
    $leader1 = getLeader1($id);
    if ($leader1 != 0) {
        return getLeaderTree($leader1, $tree);
    }
    sort($tree);
    return "/" . implode("/", $tree) . "/";
}

/**
 * 获取用户上级ID
 * @param type $id
 */
function getLeader1($id) {
    $leader1 = Db::name('home_user')->where('id', $id)->value('leader1');
    return intval($leader1);
}

/**
 * 更新用户树ID
 * @param type $id
 */
function updateTreeById($id) {
    $tree = getLeaderTree($id);
    Db::name('home_user')->where("id", $id)->update(['tree_id' => $tree]);
}

/**
 * 更新用户树ID
 * @param type $id
 */
function updateTreeById2($id) {
    $user = Db::name('home_user')->where("id", $id)->find();
    $tree_id = Db::name('home_user')->where('id', $user['leader1'])->value('tree_id');
    if ($tree_id) {
        $tree_id .= "{$id}/";
    } else {
        $tree_id = "/{$id}/";
    }
    Db::name('home_user')->where("id", $id)->update(['tree_id' => $tree_id]);
}

/**
 * 保留两位小数不四舍五入
 * @param type $num
 * @param type $decimal 小数精度
 * @param type $floatval 是否使用floatval函数转换
 * @return type
 */
function sprintf4f($num, $decimal = 2, $floatval = false) {
    $val = sprintf("%.{$decimal}f", substr(sprintf("%." . ($decimal + 1) . "f", $num), 0, -1));
    return $floatval ? floatval($val) : $val;
}

function getUserLevel($level_id) {
    $redis = \think\Cache::store('redis');
    $redis_key = "home_user_level{$level_id}";
    if ($redis->has($redis_key)) {
        return $redis->get($redis_key);
    }
    $data = app\admin\model\LevelModel::where('level_id', $level_id)->find();
    if ($data) {
        $redis->set($redis_key, $data->toArray(), 60);
    }
    return $data;
}

function createOrderQueue($order_id, $user_id, $date = null) {
    $delay = random_int(1, 5);
    if (is_null($date)) {
        $date = timeToStr();
    }
    $job = "Order";
    \think\Queue::later($delay, $job, [
        'order_id' => $order_id,
        'user_id' => $user_id,
        'date' => $date,
    ]);
    return $delay;
}

function getWebUrl() {
    $web_url = request()->domain();
    if (!$web_url) {
        $web_url = \think\Env::get('web_url');
    }
    return $web_url;
}

/**
 * 获取静态资源url
 * @return type
 */
function getStaticUrl() {
    $static_url = \think\Env::get('STATIC_URL');
    if (!$static_url) {
        $static_url = getWebUrl();
    }
    return $static_url;
}

/**
 * 生成资源文件地址
 * @param type $path 文件路径
 * @param type $dir 目录
 * @param type $url_type 0:返回相对路径，1返回远程访问地址，2返回本地绝对路径
 * @return string
 */
function toMedia($path, $dir = '/upload/', $url_type = 0) {
    if (empty($path) || !is_string($path)) {
        return "";
    }

    if (is_numeric($dir)) {
        $url_type = $dir;
        $dir = '/upload/';
    }
    $t = strtolower($path);
    if (strexists($t, 'http://') || strexists($t, 'https://')) {
        return $path;
    }
//    $request = request();
    $url = $dir . ltrim($path, '/');
    if ($url_type == 1) {
        return toWebUrl($url);
    } elseif ($url_type == 2) {
        return ROOT_PATH . "public/" . ltrim($url, '/');
    }
    return $url;
}

function toWebUrl($uri) {
    $web_url = getStaticUrl();
    return rtrim($web_url, '/') . "/" . ltrim($uri, '/');
}

/**
 * 生成头像地址
 * @param string $avatar
 * @return string
 */
function toAvatar($avatar) {
    if (empty($avatar)) {
        return "";
    }
    $avatar = rtrim($avatar, ".png");
    $view_replace_str = config('view_replace_str');
    if (is_numeric($avatar)) {
        return toMedia("images/heads/{$avatar}.png", $view_replace_str['__COMM__'] . "/") . "?" . config("my.img_version");
    } else {
        return $avatar;
    }
}

/*
 * 获取充值数据之和
 * @param int|array $pay_type 充值类型 1 线下银行转账 3线上支付
 * @param string $time 充值时间查询字符串 today month year
 * @return number
 */
function getTotalRecharge($pay_type = 1, $time = 'today', $is_xuni = 0) {
    $model = new \app\admin\model\RechargeModel();
    $model = $model->where(['status' => 2, 'is_xuni' => $is_xuni]);

    if ($time != '') {
        $model = $model->whereTime('create_time', $time);
    }
    if (in_array($pay_type, [1, 2, 3, 4])) {
        $model = $model->where(['type' => $pay_type]);
    }
    $result = $model->sum('money');
    return i18n_sprintf($result);
}

/**
 * 获取盘内余额
 */
function getTotalRemainMoney() {
    $result = (new app\common\model\UserModel)
            ->where(['is_xuni' => 0])
            ->sum('user_money');
    return $result;
}

/**
 * 获取提现数据之和
 * @param string $time 提现时间查询字符串 today month year
 * @param string $sum_field 相加的字段名
 * @param int $status 提现状态 1 申请中，2 成功 3 付款中 4 审核失败
 * @return int
 */
function getTotalWithdraw($time = 'today', $sum_field = 'money', $status = 2,$is_xuni=0) {
    $result = 0;
    if (in_array($sum_field, ['money', 'taxfee'])) {
        $model = new app\admin\model\WithdrawModel();

        if ($time != '') {
            $model = $model->whereTime('check_time', $time);
        }
        $result = $model
                ->where(['status' => $status, 'is_xuni' => $is_xuni])
                ->sum($sum_field);
    }
    return i18n_sprintf($result);
}

/**
 * 刷单成功的i流水总额
 * @param type $time 查询时间字符串 today month year
 * @param type $sum_field 相加的字段名
 * @return int
 */
function getTotalMoneyOrder($time = 'today', $sum_field = 'retail_price') {
    $result = 0;
    if (in_array($sum_field, ['retail_price', 'return_bonus'])) {
        if (in_array($time, ['today', 'yesterday'])) {
            $result = Db::name('order')->where('is_xuni', 0)->where('status', 2)->whereTime('create_time', $time)->sum($sum_field);
        } else {
            $model = new app\admin\model\DayReportModel();
            if ($time != '') {
                $model = $model->whereTime('stat_date', $time);
            }
            $result = $model->where('user_id', 0)->sum($sum_field == "retail_price" ? 'order_price' : 'order_gray');
        }
    }
    return i18n_sprintf($result);
}

/**
 * 获取会员任务佣金数据
 * @param string $time 查询时间字符串 today month year
 * @param int $type 金额变动的类型
 * @return int
 */
function getTotalGray($time = 'today', $type = 4) {
    if (in_array($time, ['today', 'yesterday'])) {
        $full_table_name = getMoneyLogTableByDay($time);
        $result = Db::table($full_table_name)->where('type', $type)->where('is_xuni', 0)->whereTime('create_time', $time)->sum('money');
    } else {
        $model = new app\admin\model\DayReportModel();
        if ($time != '') {
            $model = $model->whereTime('stat_date', $time);
        }
        $result = $model->where('user_id', 0)->sum($type == 4 ? 'order_gray' : 'manual_money');
    }
    return i18n_sprintf($result);
}

function getLangs($status = null) {
    $where = [];
    if (!is_null($status)) {
        $where = ['status' => $status];
    }
    $langs = \app\common\model\LangModel::where($where)->order('is_default DESC, id ASC')->select();
    return $langs;
}

function getLangsColumn($status = null) {
    $where = [];
    if (!is_null($status)) {
        $where = ['status' => $status];
    }
    $langs = \app\common\model\LangModel::where($where)->order('is_default DESC, id ASC')->column('title', 'lang');
    return $langs;
}

/**
 * 判断表名是否存在
 * @param string $full_table_name
 * @param string $config
 * @return bool
 */
function existTable($full_table_name, $config = "") {
    $exist = Db::connect($config)->query("show tables like '%{$full_table_name}%'");
    return empty($exist) ? false : true;
}

function getMoneyLogTableByDay($type = 'today') {
    if ($type == 'today') {
        $Ymd = timeToStr(time(), 'Ymd');
    } elseif ($type == 'yesterday') {
        $Ymd = timeToStr(strtotime("-1 day"), 'Ymd');
    } else {
        $Ymd = timeToStr(getMkTime($type), 'Ymd');
    }
    $full_table_name = "qd_money_log_{$Ymd}";
    createMoneyLogDayTable($full_table_name);
    return $full_table_name;
}

/**
 * 触发活动脚本
 * @param type $params
 * @return boolean
 */
function triggerActivityTask($params) {
    if (!isArrayAndNotEmpty($params)) {
        return false;
    }
    \think\Hook::exec('app\\behavior\\ActivityTask', 'run', $params);
}

if (!function_exists('lang')) {

    /**
     * 获取语言变量值
     * @param string    $name 语言变量名
     * @param array     $vars 动态变量值
     * @param string    $lang 语言
     * @return mixed
     */
    function lang($name, $vars = [], $lang = '') {
        if (empty($name)) {
            return '';
        }
        $auto_lang = \think\Env::get("auto_lang", false);
        if ($auto_lang) {
            $info = \app\common\model\LangItemModel::where('title', $name)->find();
            if (!$info) {
                app\common\model\LangItemModel::create([
                    'title' => $name
                ]);
            }
        }
        return Lang::get($name, $vars, $lang);
    }

}

/**
 * 生成邀请码
 * @param type $prefix
 */
function generateInviteCode($prefix = '') {
    $inviteCode = random(5, 'number');
    $isExist = Db::name('home_user')->where("invite_code", $inviteCode)->count();
    if ($isExist) {
        return generateShareCode($prefix);
    }
    return $inviteCode;
}

/**
 * 获取配置
 * @param type $field
 * @param type $cache
 * @param type $default
 * @return type
 */
function getConfig($field = null, $cache = true, $default = null) {
    $redis = \think\Cache::store('redis');
    $redis_key = config("my.redis_key.config");
    $result = [];
    if ($redis->has($redis_key) && $cache) {
        $result = $redis->get($redis_key);
    }
    if (!isArrayAndNotEmpty($result)) {
        $config = new ConfigModel();
        $all = $config->all();
        foreach ($all as $row) {
            $result[$row['field']] = $row['value'];
        }
        $redis->set($redis_key, $result, 3600);
    }
    if (is_null($field)) {
        return $result;
    }
    if (isset($result[$field])) {
        return $result[$field];
    }
    
    return $default;
}

/**
 * 法币转USDT
 * @param int $money 法币金额
 * @return real BTC金额
 */
function fiatToUsdt($money, $exchange_rate = null) {
    if (is_null($exchange_rate)) {
        $exchange_rate = getConfig('exchange_rate');
    }
    if ($exchange_rate && $exchange_rate > 0) {
        return floatval(sprintf("%.8f", $money / $exchange_rate));
    }
    return $money;
}

/**
 * USDT转法币
 * @param int $money BTC金额
 * @return real 法币金额
 */
function usdtToFiat($money, $exchange_rate = null) {
    if (is_null($exchange_rate)) {
        $exchange_rate = getConfig('exchange_rate');
    }
    if ($exchange_rate && $exchange_rate > 0) {
        return sprintf4f($money * $exchange_rate, 2, true);
    }
    return $money;
}

function js_version(){
    return 'v=' . (config('app_debug') ? time() : config("my.js_version"));
}
function css_version(){
    return 'v=' . (config('app_debug') ? time() : config("my.css_version"));
}