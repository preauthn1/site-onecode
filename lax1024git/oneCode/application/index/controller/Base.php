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
use think\Cache;
use think\Request;
use think\Db;
use util\Agent;

class Base extends Controller {

    public $userInfo = [];
    public $userLevel = [];
    public $configInfo = [];
    public $userid = 0;
    public $tyj_expire_tips = false;

    public function _initialize() {
        $invite_code = input('inviteCode') ? input('inviteCode') : '';
        if (!$invite_code && session('?invite_code')) {
            $invite_code = session('invite_code');
        }
        if ($invite_code) {
            session('invite_code', trim($invite_code));
        }
        $config = new \app\common\model\ConfigModel();
        $this->configInfo = $config->getConfig();
        if (!empty($this->request->domainInfo)) {
            $this->configInfo['title'] = $this->request->domainInfo['title'];
            $this->configInfo['logo'] = $this->request->domainInfo['logo'];
        }
        if (!$this->isLogin()) {
            $loginUrl = url('login/index');
            if (request()->isAjax()) {
                echo json_encode(msg(111, $loginUrl, lang('登录超时')));
                die;
            }
            $this->redirect($loginUrl);
        }
//        $userModel = new UserModel();
//        $this->userInfo = $userModel->getOneUser($this->userid);
        $this->userLevel = getUserLevel($this->userInfo['level_id']);
        if (empty($this->userLevel)) {
            checkLevel($this->userid);
        }

        if ($this->userInfo['tyj_money'] > 0 && $this->configInfo['tyj_expire'] > 0) { //清理体验金
            $tyj_expire = time() - ($this->configInfo['tyj_expire'] * 24 * 3600);
            if ($this->userInfo['create_time'] < $tyj_expire) { //体验金已过期
                Db::name('home_user')->where('id', $this->userInfo['id'])->update(['tyj_money' => 0]);
                Cache::store('redis')->set('tyj_expire_tips' . $this->userInfo['id'], 1);
            }
        }
        $this->tyj_expire_tips = cache('tyj_expire_tips' . $this->userInfo['id']);
        $web_url = getWebUrl();
        $download_url = $web_url . "/download/{$invite_code}";
        $this->assign([
            'controller' => request()->controller(),
            'action' => request()->action(),
            'userInfo' => $this->userInfo,
            'userLevel' => $this->userLevel,
            'setting' => $this->configInfo,
            'isHtml5Plus' => Agent::isHtml5Plus(),
            'tyj_expire_tips' => $this->tyj_expire_tips,
            'download_url' => $download_url,
            'js_version' => config('app_debug') ? time() : config("my.js_version"),
            'css_version' => config('app_debug') ? time() : config("my.css_version"),
            'lang_range' => \think\Lang::range()
        ]);
    }

    public function getGoodsShareUrl($goods_id) {
        $weburl = getWebUrl();
        $configInfo = $this->configInfo;
        $web_clip_url = "{$weburl}/mobileconfig";
        if (Agent::osType() == Agent::OS_TYPE_ANDROID) {
            $download_url = $configInfo['android_url'];
        } else {
            $download_url = $configInfo['ios_url'] != '' ? $configInfo['ios_url'] : $web_clip_url;
        }
        return $download_url;
//        $params = ['id' => $goods_id];
//        if ($this->userInfo) {
//            $params['inviteCode'] = $this->userInfo['invite_code'];
//        }elseif(session('?invite_code')){
//            $params['inviteCode'] = session('invite_code');
//        }
//        return url('goods/info', ['id' => $goods_id, 'inviteCode' => $this->userInfo['invite_code']], true, true);
    }
    
    // 判断登录状态
    public function isLogin() {
        $login_key = cookie("index_login_key");
        $userModel = new UserModel();
        if (!session('?username', '', 'index') || !session('?userid', '', 'index')) {
            if ($login_key != '') {
                $this->userInfo = $userModel->where('login_key', $login_key)->find();
                if ($this->userInfo) {
                    if ($this->userInfo['avatar']) {
                        $this->userInfo['avatar'] = toAvatar($this->userInfo['avatar']);
                    }
                    $this->setSession($this->userInfo);
                    $param = [
                        'last_login_ip' => getip(),
                        'last_login_time' => time(),
                    ];
                    // 更新用户登录信息
                    $userModel->updateStatus($param, $this->userInfo['id']);
                    $userModel->insertLoginLog($this->userInfo['id']);
                }
            }
        }
        // 判断用户信息
        if (!session('?username', '', 'index') || !session('?userid', '', 'index')) {
            $this->clearSession();
            return false;
        }
        $this->userid = intval(session('userid', '', 'index'));
        if (!$this->userInfo) {
            $this->userInfo = $userModel->getOneUser($this->userid);
        }
        if (!$this->userInfo) {
            $this->clearSession();
            return false;
        }
        // 验证账号状态
        if (2 == $this->userInfo['status']) {
            $this->clearSession();
            $param = [
                'login_key' => random(32)
            ];
            // 更新用户登录信息
            $userModel->updateStatus($param, $this->userInfo['id']);
            return false;
        }
        if (strtolower($this->request->controller()) == 'goods') {
            $user_money = $this->userInfo['user_money'] + $this->userInfo['tyj_money'];
            if (isset($this->configInfo['yeb_qd']) && $this->configInfo['yeb_qd'] == 1) {
                $yeb_total = Db::name('yuebao')->where('user_id', $this->userInfo['id'])->where('status', 1)->sum('principal');
                $user_money += $yeb_total;
            }
            $this->userInfo['user_money'] = sprintf4f($user_money);
        }
        $this->userInfo['tyj_money'] = sprintf4f($this->userInfo['tyj_money']);
        if ($login_key != $this->userInfo['login_key']) {
            if ($this->userInfo['is_demo']) {
                clearUderInfo($this->userInfo['id']);
            }
            $this->clearSession();
            return false;
        }
        return true;
    }

    private function setSession($info) {
        session('username', $info['mobile'], 'index');
        session('userid', $info['id'], 'index');
        session('is_demo', $info['is_demo'], 'index');
    }

    private function clearSession() {
        session('username', null, 'index');
        session('userid', null, 'index');
        session('checkTimes', null, 'index');
        session('is_demo', null, 'index');
    }

    /**
     * 设置页面信息
     * @param type $title 网页标题
     * @param bool $isLeft 是否显示左侧箭头
     * @param string $leftUrl 左侧链接
     * @param bool $isRight 是否显示右侧图标
     * @param string $rightCon 右侧图标html
     * @param string $rightUrl 右侧链接
     * @param string $onclick 右侧点击事件
     */
    public function setTitle($title, $isLeft = true, $leftUrl = '', $isRight = false, $rightCon = '', $rightUrl = '', $onclick = '') {
        if (!$leftUrl) {
            $leftUrl = 'javascript:history.back(0)';
        }
        if (!$rightUrl) {
            $rightUrl = 'javascript:;';
        }
        $this->assign([
            'site_title' => $title,
            'isLeft' => $isLeft,
            'leftUrl' => $leftUrl,
            'isRight' => $isRight,
            'rightCon' => $rightCon,
            'rightUrl' => $rightUrl,
            'onclick' => $onclick
        ]);
    }

}
