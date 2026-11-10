<?php

namespace app\index\controller;

use app\common\model\ConfigModel;
use app\common\model\UserModel;
use think\Controller;
use util\Agent;
class Service extends Nbase {

    public function index() {
        $this->setTitle(lang('客服'));
        $config = new ConfigModel();
        $this->configInfo = $config->getConfig();
        $accounts = [];
        if (isset($this->configInfo['kefu_account']) && !empty($this->configInfo['kefu_account'])) {
            foreach (explode("\n", $this->configInfo['kefu_account']) as $item) {
                list($text, $account) = explode("|", $item);
                $accounts[] = [
                    'text' => trim($text),
                    'type' => $this->getType($text, $account),
                    'account' => trim($account),
                    'url' => $this->getUrl($text, trim($account)),
                ];
            }
        }
        $this->userid  = intval(session('userid', '', 'index'));
        // 获取用户信息
        $userModel = new UserModel();
        $userInfo  = $userModel->getOneUser($this->userid);
        $this->assign([
            'setting' => $this->configInfo,
            'accounts'   => $accounts,
            'userInfo'   => $userInfo,
            'controller' => request()->controller(),
            'isHtml5Plus'   => Agent::isHtml5Plus(),
            'js_version' => config('app_debug') ? time() : config("my.js_version"),
            'css_version' => config('app_debug') ? time() : config("my.css_version"),
        ]);
        return $this->fetch();
    }
    
    private function getType($text, $account) {
        if (strexists(strtolower($text), 'whatsapp') || strexists(strtolower($text), 'whats')) {
            return 'whatsapp';
        }
        if (strexists(strtolower($text), 'line')) {
            return 'line';
        }
        if (strexists(strtolower($text), 'zalo')) {
            return 'zalo';
        }
        if (strexists(strtolower($text), 'fb') || strexists(strtolower($text), 'facebook')) {
            return 'fb';
        }
        if (validEmail($account)) {
            return 'mail';
        }
        return 'kefu';
    }

    private function getUrl($text, $account) {
        if (strexists($account, "http://") || strexists($account, "https://")) {
            return $account;
        }
        if (strexists(strtolower($text), 'whatsapp')) {
            return 'https://wa.me/' . ltrim(str_replace(['+', '-', ' '], '', $account), '0');
        }
        if (validEmail($account)) {
            return "mailto:{$account}";
        }
        return '';
    }
}
