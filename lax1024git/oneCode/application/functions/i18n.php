<?php

use app\common\model\BankModel;
use app\common\model\ArticleModel;
use app\common\model\LangModel;
use app\common\model\ConfigModel;
use think\Db;
use think\Lang;

/**
 * 国际化数字格式
 * @param int $number
 */
function i18n_number_format($number) {
    $range = LangModel::where('is_default', 1)->value('lang');
    if (!$range) {
        $range = Lang::range();
    }
    $format_number = config('format.number');
    if (!$range || !isset($format_number[$range])) {
        $range = 'zh-cn';
    }
    $format = $format_number[$range];
    return number_format(floatval($number), $format[0], $format[1], $format[2]);
}

/**
 * 国际化数字格式
 * @param int $number
 */
function i18n_sprintf($number) {
    $range = Lang::range();
    $format_number = config('format.number');
    if (!$range || !isset($format_number[$range])) {
        $range = 'zh-cn';
    }
    $format = $format_number[$range];
    $decimal = $format[0];
    return sprintf4f($number, $decimal);
}

/**
 * 国际化货币格式
 * @param int $number
 */
function i18n_currency($number) {
    $range = LangModel::where('is_default', 1)->value('lang');
    if (!$range) {
        $range = Lang::range();
    }
    $currency = config('format.currency');
    if (!$range || !isset($currency[$range])) {
        $range = 'zh-cn';
    }
    $format = $currency[$range];
    return sprintf($format, i18n_number_format($number));
}

/**
 * 国际化货币代码
 */
function i18n_currency_code() {
    $range = LangModel::where('is_default', 1)->value('lang');
    if (!$range) {
        $range = Lang::range();
    }
    $currency = config('format.currency_code');
    if (!$range || !isset($currency[$range])) {
        $range = 'zh-cn';
    }
    return $currency[$range];
}

function i18n_text() {
    $range = Lang::range();
    $text = config('format.text');
    if (!$range || !isset($text[$range])) {
        $range = 'zh-cn';
    }
    return $text[$range];
}

/**
 * 国际化日期格式
 * @param int $time
 */
function i18n_date2str($time) {
    $range = \think\Lang::range();
    $format = config('format.date');
    if (!$range || !isset($format[$range])) {
        $range = 'zh-cn';
    }
    return date($format[$range], $time);
}

/**
 * 国际化时间格式
 * @param int $time
 */
function i18n_time2str($time) {
    $range = Lang::range();
    $format = config('format.time');
    if (!$range || !isset($format[$range])) {
        $range = 'zh-cn';
    }
    return date($format[$range], $time);
}

/**
 * 国际化时间格式
 * @param int $time
 */
function i18n_datetime2str($time = null) {
    if (is_null($time)) {
        $time = time();
    }
    $range = Lang::range();
    $format = config('format.datetime');
    if (!$range || !isset($format[$range])) {
        $range = 'zh-cn';
    }
    return date($format[$range], $time);
}

function i18n_symbol() {
    $range = LangModel::where('is_default', 1)->value('lang');
    if (!$range) {
        $range = Lang::range();
    }
    $format = config('format.symbol');
    if (!$range || !isset($format[$range])) {
        $range = 'zh-cn';
    }
    return $format[$range];
}

//获取国际区号
function i18n_idc() {
    $range = LangModel::where('is_default', 1)->value('lang');
    if (!$range) {
        $range = Lang::range();
    }
    $format = config('format.idc');
    if (!$range || !isset($format[$range])) {
        $range = 'zh-cn';
    }
    return $format[$range];
}

//获取随即号码
function i18n_rand_mobile() {
    $range = LangModel::where('is_default', 1)->value('lang');
    if (!$range) {
        $range = Lang::range();
    }
    $format = config('format.idc');
    if (!$range || !isset($format[$range])) {
        $range = 'zh-cn';
    }
    if ($range == 'vn') {
        return randomVnMobile();
    } elseif ($range == 'idn') {
        return randomIdnMobile();
    } elseif ($range == 'thb') {
        return randomThbMobile();
    } elseif ($range == 'ind') {
        return randomIndMobile();
    } elseif ($range == 'zh-cn') {
        return randomMobile();
    } else {
        return randomIdnMobile();
    }
}

function i18n_valid_mobile($val, $range = null) {
    if (empty($range)) {
        $range = LangModel::where('is_default', 1)->value('lang');
        if (!$range) {
            $range = Lang::range();
        }
    }

    $format = config('format.mobile');
    if (!$range || !isset($format[$range])) {
        $range = 'zh-cn';
    }

    if (isset($format[$range])) {
        return validRegexp($format[$range], $val);
    } else {
        return validNumber($val);
    }
}

//获取银行列表
function i18n_banks() {
    $range = LangModel::where('is_default', 1)->value('lang');
    if (!$range) {
        $range = Lang::range();
    }
    $format = config('bank');
    if (!$range || !isset($format[$range])) {
        $range = 'zh-cn';
    }
    return $format[$range];
}

//获取银行列表
function i18n_banks2() {
    $range = LangModel::where('is_default', 1)->value('lang');
    if (!$range) {
        $range = Lang::range();
    }
    $banks = BankModel::where('lang', $range)->where('status', 1)->order('sort ASC')->field('name,bank_code,logo')->select();
    if (empty($banks)) {
        $banks = BankModel::where('lang', 'zh-cn')->where('status', 1)->field('name,bank_code,logo')->order('sort ASC')->select();
    }
    if (isArrayAndNotEmpty($banks)) {
        foreach ($banks as &$value) {
            $value['logo'] = toMedia($value['logo']);
        }
        unset($value);
    }
    return $banks;
}

function i18n_bank_valid($bank_code, $bank_number) {
    $range = LangModel::where('is_default', 1)->value('lang');
    if (!$range) {
        $range = Lang::range();
    }
    $format = config('bank_pattern');
    if (!$range || !isset($format[$range])) {
        $range = 'zh-cn';
    }
    $pattern = $format[$range];
    if (isset($pattern[$bank_code]) && $pattern[$bank_code] != '') {
        $regexp = $pattern[$bank_code];
        return validRegexp($regexp, $bank_number);
    } else {
        return $bank_number != '';
    }
}

function i18n_bank_valid2($bank_code, $bank_number) {
    if (empty($bank_code)) {
        return false;
    }
    $range = LangModel::where('is_default', 1)->value('lang');
    if (!$range) {
        $range = Lang::range();
    }
    $bank_number_rule = BankModel::where('lang', $range)->where('status', 1)->where('bank_code', $bank_code)->value('bank_number_rule');
    if (empty($bank_number_rule)) {
        return i18n_bank_valid($bank_code, $bank_number);
    }
    if (is_numeric($bank_number_rule)) {
        return strlen($bank_number) == $bank_number_rule;
    } else {
        return validRegexp($bank_number_rule, $bank_number);
    }
}

function i18n_bank_code($bank_code) {
    if (empty($bank_code)) {
        return '';
    }
    $range = LangModel::where('is_default', 1)->value('lang');
    if (!$range) {
        $range = Lang::range();
    }
    $code = BankModel::where('lang', $range)->where('status', 1)->where('bank_code', $bank_code)->value('bank_code');
    if (empty($code)) {
        $code = BankModel::where('lang', $range)->where('status', 1)->where('name', $bank_code)->value('bank_code');
    }
    return empty($code) ? $bank_code : $code;
}

//获取recaptcha语言
function i18n_recaptcha() {
    $range = Lang::range();
    $format = config('format.recaptcha');
    if (!$range || !isset($format[$range])) {
        $range = 'zh-cn';
    }
    return $format[$range];
}

/**
 * 根据当前语言获取公告
 * @return type
 */
function i18n_notice($range = null) {
    if (is_null($range)) {
        $range = Lang::range();
    }
    if (!$range) {
        $range = 'zh-cn';
    }
    $model = new ConfigModel();
    $config = $model->getConfig();
    $where = [
        'status' => 1,
        'user_id' => 0
    ];
    if ($config['checkout_language']) {
        $where['lang'] = $range;
    }
    $info = Db::name('notice')->where($where)->find();
    if (empty($info)) {
        if ($config['checkout_language']) {
            $where['lang'] = 'zh-cn';
        }
        $info = Db::name('notice')->where($where)->find();
    }
    if (empty($info)) {
        return null;
    }
    $info['content'] = html_entity_decode($info['content']);
    return $info;
}

/**
 * 根据当前语言获取首页菜单
 * @param type $is_nav 1 首页 2 个人中心
 * @param type $range
 * @return type
 */
function i18n_home_navs($is_nav = 1, $range = null) {
    if (is_null($range)) {
        $range = Lang::range();
    }
    if (!$range) {
        $range = 'zh-cn';
    }
    $model = new ConfigModel();
    $config = $model->getConfig();
    $where = [
        'is_nav' => $is_nav, 'status' => 1
    ];
    if ($config['checkout_language']) {
        $where['lang'] = $range;
    }
    $navs = ArticleModel::where($where)->order('sort ASC, id DESC')->select();
    if (empty($navs)) {
        if ($config['checkout_language']) {
            $where['lang'] = 'zh-cn';
        }
        $navs = ArticleModel::where($where)->order('sort ASC, id DESC')->select();
    }
    if (empty($navs)) {
        return [];
    }
    foreach ($navs as $key => $vo) {
        if ($navs[$key]['url'] == '') {
            $navs[$key]['url'] = url("article/index", ['id' => $vo['id']]);
        } elseif (!strexists($vo['url'], 'http')) {
            $navs[$key]['url'] = url($vo['url']);
        }
        $navs[$key]['thumb'] = toMedia($vo['thumb']);
    }
    return $navs;
}

/**
 * 根据当前语言获取banner
 * @return type
 */
function i18n_home_banner($range = null) {
    if (is_null($range)) {
        $range = Lang::range();
    }
    if (!$range) {
        $range = 'zh-cn';
    }

    $model = new ConfigModel();
    $config = $model->getConfig();
    $where = [
        'status' => 1,
    ];
    if ($config['checkout_language']) {
        $where['lang'] = $range;
    }
    $banner = Db::name('banner')->where($where)->order('sort ASC, id DESC')->select();
    if (empty($banner)) {
        if ($config['checkout_language']) {
            $where['lang'] = 'zh-cn';
        }
        $banner = Db::name('banner')->where($where)->order('sort ASC, id DESC')->select();
    }
    foreach ($banner as $k => $val) {
        $banner[$k]['pic'] = toMedia($val['pic']);
    }

    return $banner;
}

function lang2($key, $default = null) {
    if (empty($key)) {
        return !is_null($default) ? $default : '';
    }
    $text = lang($key);
    if ($text == $key && !is_null($default)) {
        return $default;
    } else {
        return $text;
    }
}

function i18n_cssbg($key) {
    $bgurl = toMedia(lang2($key, ''), '');
    if ($bgurl) {
        return "background-image: url({$bgurl});";
    }
    return '';
}
