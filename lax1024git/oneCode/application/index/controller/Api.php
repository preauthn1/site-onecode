<?php

namespace app\index\controller;

use app\common\model\ConfigModel;
use think\Controller;
use think\Cache;
use sms\CSms;
use think\helper\Str;
use think\Log;
use ReCaptcha\ReCaptcha;

class Api extends Controller {

    // 发送短信
    public function sendSms($mobile) {
        $redis = Cache::store('redis')->handler();
        $redis2 = Cache::store('redis');
        if (!validRegexp('/^0[1-9]\d*$/', $mobile)) {
            return json(msg(-1, "", lang('手机号格式有误')));
        }
        $configModel = new ConfigModel();
        $configInfo = $configModel->getConfig(false);
        if (!isset($configInfo['send_sms_verify']) || $configInfo['send_sms_verify'] == 1) {
            $smsverify = $this->request->param('smsverify/s', 'smsverify');
            if (!$redis2->has("smsverify:{$smsverify}")) {
                return json(msg(-1, '', lang('非法请求')));
            }
            $redis2->rm("smsverify:{$smsverify}");
        }
        $ttl = $redis->ttl($mobile);
        if ($ttl > 540) {
            return json(msg(-1, $ttl, lang('请勿频繁请求')));
        }
        $code = $redis->get($mobile);
        if (!$code) {
            $code = random(6, 'number');  // 验证码
        }

        $sms_config = config("sms");
        if (empty($sms_config)) {
            return json(msg(-1, '', lang('未配置短信渠道，请联系客服')));
        }
        $key = array_rand($sms_config);
        $config = $sms_config[$key];
        if (!isset($config['sign'])) {
            $config['sign'] = $configInfo['sms_sign'] ?? $configInfo['name'];
        }
        try {
            $sms_api = new CSms($config, Str::studly($key));
//            if (validMobile($mobile)) {//国内号码
//                $res = $sms_api->sendSms($mobile, $code);
//            } else {// 国际号码
                $_mobile = ltrim($mobile, '0');
                $res = $sms_api->sendGjSms(i18n_idc() . $_mobile, $code);
//                $res = $sms_api->sendGjSms('84' . $_mobile, $code);
//            }
            if ($res) {
                $redis->set($mobile, $code, 600);
                return json(msg(200, '', lang('发送成功')));
            } else {
                $msg = '短信发送失败:' . $sms_api->getError();
                Log::write("sms send={$mobile}==" . $msg, "debug", true);
                return json(msg(-1, $msg, lang('发送失败')));
            }
        } catch (\Exception $ex) {
            Log::write($ex->getMessage(), "error", true);
            Log::write($ex->getTraceAsString(), "error", true);
            return json(msg(-1, '', lang('发送失败')));
        }
    }

    public function verify() {
        $datas = $this->request->param();
        if (empty($datas)) {
            return false;
        }
        $sum = array_sum($datas);
        $avg = $sum * 1.0 / count($datas);
        $sum2 = 0;
        for ($i = 0; $i < count($datas); $i++) {
            $sum2 += pow($datas[$i] - $avg, 2);
        }
        $stddev = $sum2 / count($datas);
        $success = $stddev != 0;
        $token = '';
        if ($success) {
            $token = random(32);
            Cache::store('redis')->set("smsverify:{$token}", 1, 60);
        }
        return json(msg($success, $token, lang('success')));
    }

}
