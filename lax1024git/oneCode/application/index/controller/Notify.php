<?php

namespace app\index\controller;
use app\admin\model\PaymentModel;
use think\helper\Str;

class Notify {

    public function pay($pay_id) {
        $payinfo = PaymentModel::where('id', $pay_id)->find();
        if (empty($payinfo)) {
            return 'fail';
        }
        
        $pays = config("pay");
        $className = "\\pay\\" . Str::studly($payinfo['account']);
        if (!isset($pays[$payinfo['account']]) || !class_exists($className)) {
            return 'fail';
        }
        $config = $pays[$payinfo['account']];
        $config['pay_type'] = $payinfo['name'];

        $ip = getip();
        if (isArrayAndNotEmpty($config['ip']) && !in_array($ip, $config['ip'])) {
            return 'fail ip';
        }
        $pay = new $className($config);
        return $pay->notify();
    }
    
    public function transfer($channel) {
        $pays = config("pay");
        $className = "\\transfer\\" . Str::studly($channel);
        if (!isset($pays[$channel]) || !class_exists($className)) {
            return 'fail';
        }
        $config = $pays[$channel];
        $ip = getip();
        if (isArrayAndNotEmpty($config['ip']) && !in_array($ip, $config['ip'])) {
            return 'fail ip';
        }
        $pay = new $className($config);
        return $pay->notify();
    }
}
