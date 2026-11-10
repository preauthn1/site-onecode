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
use think\Db;
use util\Agent;
class Nbase extends Base
{
    public $userInfo = [];
    public $userLevel = [];
    public $configInfo = [];
    public $userid = 0;
    public $tyj_expire_tips = false;
    public function _initialize()
    {
        $invite_code = input('inviteCode') ? input('inviteCode') : '';
        if(!$invite_code && session('?invite_code')){
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
        $this->isLogin();
        if ($this->userInfo) {
            $this->userLevel = getUserLevel($this->userInfo['level_id']);
            if (empty($this->userLevel)) {
                checkLevel($this->userid);
            }

            if ($this->userInfo['tyj_money'] > 0 && $this->configInfo['tyj_expire'] > 0) { //清理体验金
                $tyj_expire = time() - ($this->configInfo['tyj_expire'] * 24 * 3600);
                if ($this->userInfo['create_time'] < $tyj_expire) { //体验金已过期
                    Db::name('home_user')->where('id', $this->userInfo['id'])->update(['tyj_money' => 0]);
                    \think\Cache::store('redis')->set('tyj_expire_tips' . $this->userInfo['id'], 1);
                }
            }
            $this->tyj_expire_tips = cache('tyj_expire_tips' . $this->userInfo['id']);
        }
        $web_url = getWebUrl();
//        $download_url = $web_url . url('login/reg', ['inviteCode' => $this->userInfo['invite_code']]);
        $download_url = $web_url . "/download/{$invite_code}";
        $this->assign([
            'controller' => request()->controller(),
            'action'     => request()->action(),
            'userInfo'   => $this->userInfo,
            'userLevel'   => $this->userLevel,
            'setting'   => $this->configInfo,
            'is_zp_enable'   => $this->configInfo['zp_enable'],
            'isHtml5Plus'   => Agent::isHtml5Plus(),
            'tyj_expire_tips'   => $this->tyj_expire_tips,
            'download_url' => $download_url,
            'js_version' => config('app_debug') ? time() : config("my.js_version"),
            'css_version' => config('app_debug') ? time() : config("my.css_version"),
            'lang_range' => \think\Lang::range()
        ]);
    }

    // 设置页面信息
    public function setTitle($title, $isLeft = 1, $leftUrl = '', $isRight = 0, $rightCon = '', $rightUrl = '', $onclick = '')
    {
        if(!$leftUrl)  $leftUrl  = 'javascript:history.back(0)';
        if(!$rightUrl) $rightUrl = 'javascript:;';
        $this->assign([
            'site_title' => $title,
            'isLeft'     => $isLeft,
            'leftUrl'    => $leftUrl,
            'isRight'    => $isRight,
            'rightCon'   => $rightCon,
            'rightUrl'   => $rightUrl,
            'onclick'    => $onclick
        ]);
    }

}
