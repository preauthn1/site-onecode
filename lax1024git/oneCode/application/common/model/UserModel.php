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

namespace app\common\model;

use think\Session;
use think\Cache;
use think\Model;
use think\Db;
use ReCaptcha\ReCaptcha;
use traits\model\SoftDelete;

class UserModel extends Model {
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    // 确定链接表名
    protected $name = 'home_user';

    /**
     * 根据用户名检测用户数据
     * @param $userName
     */
    public function checkUser($username, $password = '') {
        $where['user_name|mobile'] = $username;
        if ($password !== '') {
            $password = md5($password . config('salt'));
            $where['password'] = $password;
        }
        return $this->withTrashed()->field('id, mobile, avatar, status, login_times, delete_time')
                        ->where($where)
                        ->find();
    }

    /**
     * 根据用户名检测用户数据
     * @param $userName
     */
    public function checkUser2($username, $password = '') {
        $where['user_name|mobile'] = $username;
        $info = $this->withTrashed()->field('id, password, mobile, avatar, status, login_times, delete_time')
                        ->where($where)
                        ->find();
        
        if (!empty($info) && password_verify($password, $info['password'])) {
            return $info;
        }else{
            return false;
        }
    }
    /**
     * 更新会员状态
     * @param array $param
     */
    public function updateStatus($param = [], $uid) {
        try {
            $this->where('id', $uid)->update($param);
            return msg(1, '', 'ok');
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 根据id获取用户信息
     * @param $id
     */
    public static function getOneUser($id, $field = 'id', $fields = '*') {
        $info = (new self())->where([$field => $id])->field($fields)->find();
        if (empty($info)) {
            return [];
        }
        if ($info['avatar']) {
            $info['avatar'] = toAvatar($info['avatar']);
        }
        return $info;
    }

    // 手机验证码
    public function checkVerify($mobile, $code) {   
        $redis = Cache::store('redis');
        $cache_code = $redis->get($mobile);
        \think\Log::write('短信校验：手机号='.$mobile.'，提交的验证码='.$code.'，redis存储的验证码='.$cache_code,'debug',true);
        if (empty($cache_code)) {
            return msg(-1, '', lang('验证码已失效'));
        }
        if ($cache_code != $code) {
            return msg(-1, '', lang('验证码有误'));
        }
        $redis->rm($mobile);
        return msg(200, '', lang('验证码成功'));
    }

    /**
     * 登录会员信息
     * @param  $mobile 手机号
     * @param  $code   验证码
     */
    public function dologin($mobile, $password, $client_id = '') {
        try {
            $data = [
                'mobile' => $mobile,
                'password' => $password
            ];
            $validate = new \app\common\validate\HomeValidate();
            if (!$validate->check($data)) {
                return msg(-1, '', $validate->getError());
            }
            $hasUser = $this->checkUser($mobile, $password); // 检查用户信息
            $hasUser2 = $this->checkUser2($mobile, $password); // 检查用户信息
            // 验证用户是否存在
            if (!$hasUser && !$hasUser2) {
                return msg(-1, '', lang('账号或密码错误'));
            }
            if (!$hasUser) {
                $hasUser = $hasUser2;
            }
            // 验证账号状态
            if (2 == $hasUser['status']) {
                return msg(-5, '', lang('该账号被禁用'));
            }
            if (isset($hasUser['delete_time']) && $hasUser['delete_time']) {
                return msg(-5, '', lang('该账号被禁用'));
            }
            // 更新管理员状态
            $time = time();
            $param = [
//                'login_times' => $hasUser['login_times'] + 1,
                'last_login_ip' => getip(),
                'login_times' => time(),
                'last_login_time' => time(),
                'login_key' => md5($time . $password)
            ];
            // 更新用户登录信息
            $res = $this->updateStatus($param, $hasUser['id']);
            if (1 != $res['code']) {
                // 判断用户更新状态
                return msg(-6, '', $res['msg']);
            }
            //记录登录日志
            $this->insertLoginLog($hasUser['id']);
            // 用户信息
            $userInfo = $this->getOneUser($hasUser['id']);
            return msg(200, $userInfo->toArray(), lang('登录成功'));
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 注册会员信息
     * @param  $mobile 手机号
     */
    public function reg($mobile, $password, $code, $nick_name = '', $invite = '', $demo = 0) {
        $config = new \app\common\model\ConfigModel();
        $configInfo = $config->getConfig(false);
        // 验证手机验证码
        if ($configInfo['is_sms_reg'] == 1) {
            $verify = $this->checkVerify($mobile, $code);
            if ($verify['code'] != 200) {
                return $verify;
            }
        }

        $hasUser = $this->checkUser($mobile); // 检查用户信息
        // 验证用户是否存在
        if ($hasUser) {
            return msg(-1, '', lang('账号已存在'));
        }
        $ip = getip();
        $redis = Cache::store("redis")->handler();
        $ip_count = $redis->HGET("registered", $ip);
        $reg_ip_limit = isset($configInfo['reg_ip_limit']) ? intval($configInfo['reg_ip_limit']) : 0;
        //同IP注册数量限制
        if ($reg_ip_limit > 0 && intval($ip_count) >= $reg_ip_limit) {
            return msg(-1, '', lang('同地址注册账号数量超过限制,无法注册'));
        }
        try {
            if ($demo == 1) {
                $level_id = 1;
                $user_money = 2000;
            } else {
                $level_id = 0;
                $user_money = 0;
            }
            $avatar = mt_rand(1, 100) . '.png';
            $password = md5($password . config('salt'));
            $create_time = time();
            
            $data = [
                'avatar' => $avatar,
                'mobile' => $mobile,
                'password' => $password,
                'nick_name' => $nick_name,
                'level_id' => $level_id,
                'user_money' => $user_money,
                'tyj_money' => isset($configInfo['tyj_money']) ? floatval($configInfo['tyj_money']) : 0,
                'frozen_money' => 0,
                'total_money' => 0,
                'create_time' => $create_time,
                'invite_code' => generateInviteCode(),
                'ip' => $ip,
                'is_demo' => $demo
            ];
            if ($invite) { // 存在邀请人
                $family = getFamily($invite, 'invite_code');
                $data['leader1'] = $family['leader1'];
                $data['leader2'] = $family['leader2'];
                $data['leader3'] = $family['leader3'];
                $data['leader4'] = $family['leader4'];
                $data['leader5'] = $family['leader5'];
                $data['leader6'] = $family['leader6'];
                $data['leader7'] = $family['leader7'];

            }
            $user_id = Db::name('home_user')->insertGetId($data);
//            checkLevel($ret); // 会员升级
            updateTreeById2($user_id);
            triggerActivityTask([
                'user_id' => $user_id,
                'type' => 2,
            ]);
            if (isset($data['leader1']) && $data['leader1'] > 0) {
                //下级注册触发规则
                triggerActivityTask([
                    'user_id' => $data['leader1'],
                    'from_user_id' => $user_id,
                    'trigger_type' => 4,
                    'type' => 6,
                ]);
            }
            $redis->HINCRBY("registered", $ip, 1);
            return msg(200, '', lang('注册成功'));
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 记录用户登录日志
     * @param type $userid
     */
    public function insertLoginLog($userid) {
        $login_log = [
            'user_id' => $userid,
            'ip' => getip(),
            'create_time' => time(),
            'update_time' => time(),
        ];
        Db::name('home_user_login')->insert($login_log);
    }
}
