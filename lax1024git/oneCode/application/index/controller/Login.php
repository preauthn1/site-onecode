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

namespace app\index\controller;

use app\common\model\UserModel;
use think\Controller;
use org\Verify;  // 验证码
use think\Db;
use think\Cache;
use util\Agent;
use think\Env;

class Login extends Controller {

    public $configInfo = [];

    public function _initialize() {
        $config = new \app\common\model\ConfigModel();
        $this->configInfo = $config->getConfig();
        if (!empty($this->request->domainInfo)) {
            $this->configInfo['title'] = $this->request->domainInfo['title'];
            if (isset($this->request->domainInfo['logo']) && $this->request->domainInfo['logo']) {
                $this->configInfo['logo'] = toMedia($this->request->domainInfo['logo']);
            }
            if (isset($this->request->domainInfo['android_url']) && $this->request->domainInfo['android_url']) {
                $this->configInfo['android_url'] = toMedia($this->request->domainInfo['android_url']);
            }
            if (isset($this->request->domainInfo['ios_url']) && $this->request->domainInfo['ios_url']) {
                $this->configInfo['ios_url'] = toMedia($this->request->domainInfo['ios_url']);
            }
        }
    }

    public function index() {
        $remember_pwd = Env::get('REMEMBER_PWD', false);
        if ($remember_pwd) {
            $cookie_username = cookie("cookie_username");
            $cookie_pwd = cookie("cookie_pwd");
        } else {
            $cookie_username = '';
            $cookie_pwd = '';
        }
        $this->assign([
            'setting' => $this->configInfo,
            'cookie_username' => $cookie_username,
            'cookie_pwd' => $cookie_pwd,
            'idc' => i18n_idc(),
            'js_version' => config('app_debug') ? time() : config("my.js_version"),
            'css_version' => config('app_debug') ? time() : config("my.css_version"),
        ]);
        $range = \think\Lang::range();
        $langs = getLangs(1);
        $this->assign("range", $range);
        $this->assign("langs", $langs);
        return $this->fetch();
    }

    // 登录操作
    public function doLogin() {
        $mobile = $this->request->param("mobile/s");    // 手机
        $password = $this->request->param("password/s");  // 手机验证码
        $userModel = new UserModel();
        $ret = $userModel->dologin($mobile, $password); // 检查用户信息
        if ($ret['code'] == 200) {
            $remember_pwd = Env::get('REMEMBER_PWD', false);
            if ($remember_pwd) {
                cookie("cookie_username", $mobile);
                cookie("cookie_pwd", $password);
            }
            //登录触发规则
            triggerActivityTask([
                'user_id' => $ret['data']['id'],
                'trigger_type' => 3,
                'type' => 6,
            ]);
            session('username', $ret['data']['mobile'], 'index');
            session('userid', $ret['data']['id'], 'index');
            session('is_demo', 0, 'index');
            cookie("index_login_key", $ret['data']['login_key']);
            return json(msg(200, $ret['data'], lang('登录成功')));
        } else {
            return json(msg($ret['code'], '', $ret['msg']));
        }
    }

    // 注册
    public function reg() {
        $invite_code = input('inviteCode') ? input('inviteCode') : '';
        if (!$invite_code && session('?invite_code')) {
            $invite_code = session('invite_code');
        }
        session('invite_code', $invite_code);
        $recapatcha_config = config('recaptcha');
        $this->assign([
            'invite_code' => $invite_code,
            'is_sms' => $this->configInfo['is_sms_reg'] == 1,
            'is_sms_verify' => isset($this->configInfo['send_sms_verify']) && $this->configInfo['send_sms_verify'] == 0,
            'setting' => $this->configInfo,
            'recapatcha' => $recapatcha_config,
            'idc' => i18n_idc(),
            'js_version' => config('app_debug') ? time() : config("my.js_version"),
            'css_version' => config('app_debug') ? time() : config("my.css_version"),
        ]);
        $download_url = getWebUrl() . "/download/{$invite_code}";
        $range = \think\Lang::range();
        $langs = getLangs(1);
        $this->assign("range", $range);
        $this->assign("langs", $langs);
        $this->assign("download_url", $download_url);
        $reg_bg = lang2("reg_bg", '');
        $this->assign("bg_url", $reg_bg);
        return $this->fetch();
    }

    // 注册
    public function doReg() {
        if ($this->configInfo['front_image_validate_close'] == 1) {
            $captcha = $this->request->param("captcha/s"); // 图片验证码
            $verify = new Verify();
            if (!$verify->check($captcha, 'index')) {
                return json(msg(-1, '', lang('图像验证码错误')));
            }
        }
        $mobile = $this->request->param("mobile/s");           // 手机
        if (empty($mobile)) {
            return json(msg(-1, "", lang('手机号不能为空')));
        }
        if (!isset($this->configInfo['account_rule']) || $this->configInfo['account_rule'] == 0) {
            if (!i18n_valid_mobile($mobile)) {
                return json(msg(-1, "", lang('手机号格式有误')));
            }
        } elseif ($this->configInfo['account_rule'] == 1) {
            if (!validEmail($mobile)) {
                return json(msg(-1, "", lang('邮箱格式有误')));
            }
        } elseif ($this->configInfo['account_rule'] == 2) {
            if (!validEmail($mobile) && !i18n_valid_mobile($mobile)) {
                return json(msg(-1, "", lang('账号格式有误')));
            }
        } else {
            if (!validNumberLetter($mobile) || strlen($mobile) < 8) {
                return json(msg(-1, "", lang('账号格式有误')));
            }
        }
        $password = $this->request->param("password/s");         // 密码
        if (empty($password)) {
            return json(msg(-1, "", lang('密码不能为空')));
        }
        $repassword = $this->request->param("repassword/s");       // 密码
        if ($password != $repassword) {
            return json(msg(-1, '', lang('两次密码不一致')));
        }
        $code = $this->request->param("verificationCode/s"); // 手机验证码
        $nick_name = $this->request->param("nick_name/s");        // 昵称
        if (strlen($nick_name) > 20) {
            return json(msg(-1, '', lang('昵称有误')));
        }
        $invite = $this->request->param("invite_code/s", "", "trim");      // 邀请码
        if (empty($invite) && (!isset($this->configInfo['invite_code_required']) || $this->configInfo['invite_code_required'] == 1)) {
            return json(msg(-1, "", lang('请填写邀请码')));
        }
        if ($invite !== '') {
            $invite_user = Db::name('home_user')->where('invite_code', $invite)->where('status', 1)->find();
            if (empty($invite_user)) {
                return json(msg(-1, "", lang('邀请码不存在')));
            }
        }

        $userModel = new UserModel();
        $ret = $userModel->reg($mobile, $password, $code, $nick_name, $invite); // 检查用户信息
        if ($ret['code'] == 200) {
            return json(msg(200, $ret['data'], lang('注册成功')));
        } else {
            return json(msg(-1, '', $ret['msg']));
        }
    }

    // 试玩
    public function demo_reg() {
        $userModel = new UserModel();
        $mobile = random(11, 'number'); // 手机
        $code = 1; // 手机验证码
        $nick_name = 'CS' . random(5, 'number'); // 昵称
        $invite = ''; // 邀请码
        $not_verify = 1;
        $userModel = new UserModel();
        $ret = $userModel->reg($mobile, $mobile, $code, $not_verify, $nick_name, $invite, 1); // 检查用户信息
        if ($ret['code'] == 200) {
            $res = $userModel->dologin($mobile, $mobile); // 检查用户信息
            if ($res['code'] == 200) {
                session('username', $res['data']['mobile'], 'index');
                session('userid', $res['data']['id'], 'index');
                session('checkTimes', 86400, 'index');
                session('is_demo', 1, 'index');
                return json(msg(200, $res['data'], lang('登录成功')));
            } else {
                return json(msg($res['code'], '', $res['msg']));
            }
        } else {
            return json(msg(-1, '', $ret['msg']));
        }
    }

    // 忘记密码列表
    public function password_list() {
        $this->assign([
            'setting' => $this->configInfo
        ]);
        return $this->fetch();
    }

    // 修改密码
    public function password_card() {
        $this->assign([
            'setting' => $this->configInfo
        ]);
        return $this->fetch();
    }

    public function password_mobile() {
        $step = $this->request->param("step/d", 1);
        $token = $this->request->param('token/s', "");
        if ($this->request->isAjax()) {
            if ($step == 1) {
                $mobile = $this->request->param('mobile/s');
                $code = $this->request->param('code/s');
                $userModel = new UserModel();
                $ret = $userModel->checkVerify($mobile, $code); // 检查验证码信息
                if ($ret['code'] == 200) {
                    $token = random(32);
                    Cache::store("redis")->set($token, $mobile, 600);
                    $ret['data'] = url('password_mobile', ['step' => 2, 'token' => $token]);
                }
                return $ret;
            } elseif ($step == 2) {
                $mobile = "";
                if (Cache::store("redis")->has($token)) {
                    $mobile = Cache::store("redis")->get($token);
                }
                $password = $this->request->param('password');
                $repassword = $this->request->param('repassword');
                if (!$mobile || !$password) {
                    return json(msg(-1, '', lang('缺少必要参数')));
                }
                if ($password != $repassword) {
                    return json(msg(-1, '', lang('两次密码输入不一致')));
                }
                $info = Db::name('home_user')->where(['mobile' => $mobile])->find();
                if (!$info) {
                    return json(msg(-1, '', lang('抱歉，账号不存在')));
                }
                $ret = Db::name('home_user')->where(['id' => $info['id']])->update([
                    'password' => md5($password . config('salt'))
                ]);
                if ($ret) {
                    Cache::store("redis")->rm($token);
                    return json(msg(200, '', lang('修改成功')));
                } else {
                    return json(msg(-1, '', lang('修改失败')));
                }
            }
        }
        if (!Cache::store("redis")->has($token)) {
            $step = 1;
        }
        $recapatcha_config = config('recaptcha');
        $this->assign("step", $step);
        $this->assign("token", $token);
        $this->assign([
            'setting' => $this->configInfo,
            'is_sms_verify' => isset($this->configInfo['send_sms_verify']) && $this->configInfo['send_sms_verify'] == 0,
            'js_version' => config('app_debug') ? time() : config("my.js_version"),
            'css_version' => config('app_debug') ? time() : config("my.css_version"),
            'recapatcha' => $recapatcha_config
        ]);
        return $this->fetch();
    }

    // 验证手机验证码
    public function checkVerify($mobile, $code) {
        $userModel = new UserModel();
        $ret = $userModel->checkVerify($mobile, $code); // 检查用户信息
        if ($ret['code'] == 200) {
            return json(msg(200, '', lang('验证成功')));
        } else {
            return json(msg(-1, '', $ret['msg']));
        }
    }

    // 验证码
    public function captcha() {
        $verify = new Verify();
        $verify->imageH = 32;
        $verify->imageW = 100;
        $verify->length = 4;
        $verify->useNoise = false;
        $verify->fontSize = 14;
        return $verify->entry('index');
    }

    // 退出操作
    public function loginOut() {
        $loginUrl = url('login/index');
        $userid = session('userid', '', 'index');
        $userModel = new UserModel();
        $userInfo = $userModel->getOneUser($userid);
        // 判断登录状态
        if (!$userInfo || ($userInfo['login_times'] + 604800) < time()) {
            $this->redirect($loginUrl);
        }
        $param = [
            'index_login_key' => random(32)
        ];
        $res = $userModel->updateStatus($param, $userInfo['id']);
        if ($res) {
            if ($userInfo['is_demo']) {
                clearUderInfo($userInfo['id']);
            }
            session('username', null, 'index');
            session('userid', null, 'index');
            session('checkTimes', null, 'index');
            session('is_demo', null, 'index');
            cookie('index_login_key', null);
            $this->redirect($loginUrl);
        }
    }

    public function mobileconfig($inviteCode = '') {
        $filename = md5("app_v2_inv{$inviteCode}:" . $this->request->domain());
        $file = "mobileconfig/{$filename}.mobileconfig";
        if (file_exists(toMedia($file, 2))) {
            return redirect(toMedia($file));
        }
        if ($this->configInfo['logo']) {
            $data = file_get_contents(toMedia($this->configInfo['logo'], 2));
        } else {
            $data = file_get_contents(ROOT_PATH . "/public/static/common/mobileconfig/icon.png");
        }
        $icon = wordwrap(base64_encode($data), 77, "\n", true);
        $web_url = getWebUrl();
        $url = $web_url . ($inviteCode ? "?inviteCode=$inviteCode" : "");
        $template_mobileconfig = file_get_contents(ROOT_PATH . "/public/static/common/mobileconfig/template.mobileconfig");
        if (empty($template_mobileconfig)) {
            return 406;
        }
        $mobileconfig = str_replace([
            "{{URL}}", "{{ICON}}", "{{CHANNEL_NAME}}", "{{UUID}}"
                ], [
            $url, $icon, $this->configInfo['title'], uuid()
                ], $template_mobileconfig);
        if (!file_exists(toMedia($file, 2))) {
            file_put_contents(toMedia($file, 2), $mobileconfig);
        }
        $download = new \app\common\response\Download($mobileconfig, 206);
        $download->contentType("application/x-apple-aspen-config");
        $download->noCache();
        $download->isContent();
        return $download->name("app{$inviteCode}.mobileconfig");
    }

    public function download($inviteCode = '') {
        $weburl = getWebUrl();
        $share_url = $weburl . "/download/{$inviteCode}";
        $h5url = $weburl . ($inviteCode != "" ? "?inviteCode={$inviteCode}" : "");
        $web_clip_url = "{$weburl}/mobileconfig/{$inviteCode}";
        $download_url = $h5url;
        if (Agent::osType() == Agent::OS_TYPE_ANDROID) {
            $download_url = $this->configInfo['android_url'];
        } else {
            $download_url = $this->configInfo['ios_url'] != '' ? $this->configInfo['ios_url'] : $web_clip_url;
        }
        $this->assign([
            'invite_code' => $inviteCode,
            'h5url' => $h5url,
            'share_url' => $share_url,
            'web_clip_url' => $web_clip_url,
            'domain' => $this->request->host(),
            'android_url' => $this->configInfo['android_url'],
            'ios_url' => $this->configInfo['ios_url'],
            'download_url' => $download_url,
            'setting' => $this->configInfo,
            'js_version' => config('app_debug') ? time() : config("my.js_version"),
            'css_version' => config('app_debug') ? time() : config("my.css_version"),
        ]);
        return $this->fetch();
    }

    public function check_lang() {
        $lang = $this->request->param('lang/s');
        if (isset($this->configInfo['checkout_language']) && $this->configInfo['checkout_language'] == 1) {
            $allow_lang = \app\common\model\LangModel::column('lang');
            if (in_array($lang, $allow_lang)) {
                cookie('think_var', $lang);
            }
        }
        return json(msg(200, '', lang('ok')));
    }

    public function payed() {
        $message = $this->request->param('message/s', '');
        $type = $this->request->param('type/s');
        $url = url('index/index');
        if ($type === 'success') {
            $this->success($message, $url);
        } else {
            $this->error($message, $url);
        }
    }

}
