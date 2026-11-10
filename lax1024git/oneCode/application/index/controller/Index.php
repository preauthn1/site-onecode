<?php

namespace app\index\controller;

use app\common\model\CateModel;
use app\common\model\GoodsModel;
use app\services\CommonService;
use app\services\OrderService;
use think\Db;
use think\Request;

class Index extends Nbase {

    // 首页
    public function index() {
        $this->setTitle(lang('首页'));

        $grab_order_mode = getConfig('grab_order_mode');
        if ($grab_order_mode == 2 && $this->userid > 0) { //CP共同完成
            $order_info = OrderService::getOrderByUser($this->userid);
            if($order_info) {    //有需要参与的拼单就显示商品页
                return redirect(url('goods/info', ['id' => $order_info['goods_id']]));
            }
        }

        // banner
        $banner = i18n_home_banner();
        // 公告
        $notice = i18n_notice();
        $navs = i18n_home_navs();
        $notice_list = i18n_home_navs(3);
        $ad_list = i18n_home_navs(4);
        $cates = CateModel::order('sort ASC')->select();
        $part = CommonService::getGoodsPricePartConfig();

        //greenUI才显示
        $goods_list = GoodsModel::where('is_discount', 1)->limit(2)->order('goods_id DESC')->select();
        foreach ($goods_list as $key => $value) {
            $goods_list[$key]['share_url'] = $this->getGoodsShareUrl($value['goods_id']);
        }

        //客服列表
        $kefu_account = [];
        if (isset($this->configInfo['kefu_account']) && !empty($this->configInfo['kefu_account'])) {
            foreach (explode("\n", $this->configInfo['kefu_account']) as $item) {
                list($text, $account) = explode("|", $item);
                $kefu_account[] = [
                    'text' => trim($text),
                    'type' => $this->getType($text, trim($account)),
                    'account' => trim($account),
                    'url' => $this->getUrl($text, trim($account)),
                ];
            }
        }

        $user_notice = null;
        if ($this->userInfo) {
            $user_notice = Db::name('notice')->where('user_id', $this->userid)->find();
            if ($user_notice) {
                $user_notice['content'] = html_entity_decode($user_notice['content']);
            }
        }

        //获取等级列表
        $level_list = Db::name('home_user_level')->select();

        // 用户榜单
        $this->assign([
            'banner' => $banner,
            'part' => $part,
            'goods_list2' => $goods_list,
            'notice' => $user_notice ? $user_notice : $notice,
            'cates' => $cates,
            'notice_list' => $notice_list,
            'ad_list' => $ad_list,
            'ad_text'   => $ad_list[0]['title'] ?? '',
            'navs' => $navs,
            'kefu_account' => $kefu_account,
            'level_list' => $level_list,
        ]);

        $hide_notice = cookie("hide_notice");
        if (!$hide_notice) {
            cookie("hide_notice", 1, 1800);
        }

        $this->assign('hide_notice', $hide_notice);
        $this->assign('grab_order_mode', $grab_order_mode);
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
        return '#';
    }

    // 客服
    public function kefu() {
        if ($this->configInfo['meiqia_url']) {
            return redirect($this->configInfo['meiqia_url']);
        }
        if (isset($this->configInfo['online_kefu_open']) && $this->configInfo['online_kefu_open'] == 1) {
            return redirect(url('message/index'));
        }
        return redirect(url('index/index'));
    }

    // 公告
    public function notice() {
        $this->setTitle(lang('信息公告'), 1, url('user/index'));
        // 公告
        $notice = i18n_notice();
        $this->assign([
            'info' => $notice
        ]);
        return $this->fetch();
    }

    public function tyj() {
        if ($this->userInfo) {
            \think\Cache::store('redis')->set('tyj_expire_tips' . $this->userInfo['id'], null);
        }
    }

}
