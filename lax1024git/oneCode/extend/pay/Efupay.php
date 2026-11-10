<?php

namespace pay;

use GuzzleHttp\Client;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use pay\Paybase;
class Efupay extends Paybase{
    public $url = "http://efupays.com:8084/api/pay/V2";
    public $config = [];
    public function __construct($config) {
        if (empty($config['app_id']) || empty($config['app_key'])) {
            throw new Exception('config error');
        }
        $this->config = $config;
    }

    public function pay($order) {
        $params = array(
            "version" => 'V2',
            "signType" => 'MD5',
            "merchantNo" => $this->config['app_id'],
            "date" => date("YmdHis"),
            "channleType" => $this->config['pay_type'],
            "noticeUrl" => $this->config['notify_url'],
            "orderNo" => $order['order_id'],
            "bizAmt" => sprintf("%.2f", $order['money']),
            "customNo" => $order['user_id'],
            "returnUrl" => getWebUrl(),
        );
//        dump($params);
        $params['sign'] = $this->getSign($params);
        \think\Log::write(["EfupayParams", $params], 'pay', true);
        try {
            $client = new Client();
            $response = $client->post($this->url, [
                'json' => $params
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $ex) {
            $response = $ex->getResponse();
            if (is_null($response)) {
                throw new Exception($ex->getMessage());
            }
        }
        return $this->validateResponse($response);
    }
    
    public function validateResponse($response) {
        $contents = $response->getBody()->getContents();
//        dump($contents);
        $result = json_decode($contents, true);
//        dump($result);die;
        if (empty($result)) {
            \think\Log::write($contents, "EfupayError", true);
            throw new Exception(lang('error'));
        }
        \think\Log::write(["EfupayResponse", $result], 'pay', true);
        if(isset($result['code']) && $result['code'] == 0){
            $detail = $result['detail'];
            if ($detail['PayURL']) {
                return redirect($detail['PayURL']);
            }else{
                return response($detail['PayHtml']);
            }
        }else{
            \think\Log::write($result, "EfupayError", true);
            throw new Exception(isset($result['msg']) ? $result['msg'] : "unkown error");
        }
    }

    public function notify() {
        $params = request()->param();
        \think\Log::write(["EfupayNotifyParams", $params], 'pay', true);
        $sign = $this->getSign(myArrayFilter($params));
        if ($sign != $params["sign"]) {
            \think\Log::write(["EfupayNotifyParamsErrorSign", $sign], 'pay', true);
            return 'sign error';
        }
        if ($params["status"] != "0") {
            $remark = '';
            if (in_array($params['status'], [1])) { //成功
                $status = 2;
            }else{ //失败
                $status = 3;
                $remark = 'Payment Fail';
            }
            $trade_no = $params['platform_order_id'];
            $amount_true = null;
            $ret = $this->complete($params["orderNo"], $status, $remark, $trade_no, $amount_true);
            if ($ret === true) {
                return 'SUCCESS';
            }
            return $ret;
        }
        return 'error';
    }

    private function getSign($params) {
        unset($params['sign']);
        unset($params['pay_id']);
        ksort($params);
        $md5str = urldecode(http_build_query($params));
        $md5str .= $this->config['app_key'];
        return md5($md5str);
    }
}
