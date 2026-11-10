<?php

namespace pay;

use GuzzleHttp\Client;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use pay\Paybase;
class Inpay extends Paybase{
    public $url = "https://www.inpays.in/openApi/pay/createOrder";
    public $get_order_url = "https://www.inpays.in/openApi/pay/queryOrder";
    public $config = [];
    public function __construct($config) {
        if (empty($config['app_id']) || empty($config['app_key'])) {
            throw new Exception('config error');
        }
        $this->config = $config;
    }

    public function pay($order) {
        $params = array(
            'merchant'        => $this->config['app_id'],
            'orderId' => $order['order_id'],
            'amount'       => sprintf("%.2f", $order['money']),
            'customName' => $this->config['real_name'],
            'customMobile' => $this->config['mobile'],
            'customEmail' => $this->config['mobile'] . "@abc.com",
            'notifyUrl' => $this->config['notify_url'],
            'callbackUrl'  => $this->success(lang('付款成功')),
        );
        if ($this->config['pay_type']) {
            $params['channelType'] = $this->config['pay_type'];
        }
        $params['sign'] = $this->getSign($params);
//        dump($params);die;
        \think\Log::write(["InPayParams", $params], 'pay', true);
        try {
            $client = new Client();
            $response = $client->post($this->url, [
                'form_params' => $params
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $ex) {
            $response = $ex->getResponse();
            if (is_null($response)) {
                throw new Exception($ex->getMessage());
            }
        }
        return $this->validateResponse($response);
    }
    
    public function find($order_id) {
        $params = array(
            'merchant' => $this->config['app_id'],
            'orderId' => $order_id,
        );
        $params['sign'] = $this->getSign($params);
        \think\Log::write(["InPayFindParams", $params], 'pay', true);
        try {
            $client = new Client();
            $response = $client->post($this->get_order_url, [
                'form_params' => $params
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $ex) {
            $response = $ex->getResponse();
            if (is_null($response)) {
                throw new Exception($ex->getMessage());
            }
        }
        return $this->validateResponse($response, 'find');
    }
    
    public function validateResponse($response, $type = 'pay') {
        $contents = $response->getBody()->getContents();
//        dump($contents);
        $result = json_decode($contents, true);
//        dump($result);die;
        if (empty($result)) {
            \think\Log::write(["InPayError", $contents], 'pay', true);
            throw new Exception(lang('error'));
        }
        \think\Log::write(["InPayResponse", $result], 'pay', true);
        if ($type == 'find') {
            if(isset($result['success']) && $result['success']){
                return $result['data'];
            }else{
                $msg = isset($result['errorMessages']) ? $result['errorMessages'] : "unkown error";
            }
        }else{
            if(isset($result['success']) && $result['success'] && isset($result['data']['url'])){
                return redirect($result['data']['url']);
            }else{
                $msg = isset($result['errorMessages']) ? $result['errorMessages'] : "unkown error";
            }
        }
        throw new Exception($msg);
    }

    public function notify() {
        $params = request()->param();
        \think\Log::write(["InPayNotifyParams", $params], 'pay', true);
        $sign = $this->getSign(myArrayFilter($params));
        if ($sign != $params["sign"]) {
            return 'sign error';
        }
        if (!isset($params["orderId"])) {
            return 'orderId error';
        }
        $find = $this->find($params["orderId"]);
        \think\Log::write(["InPayFindNotifyParams", $find], 'pay', true);
        if ($find && in_array($find["status"], ['PAY_FAIL', 'PAY_SUCCESS'])) {
            $remark = '';
            if (in_array($find['status'], ['PAY_SUCCESS'])) { //成功
                $status = 2;
            }else{ //失败
                $status = 3;
                $remark = 'Payment Fail';
            }
            $trade_no = $find['platOrderId'];
            $amount_true = null;
            $ret = $this->complete($params["orderId"], $status, $remark, $trade_no, $amount_true);
            if ($ret === true) {
                return 'ok';
            }
            return $ret;
        }
        return 'fail';
    }

    private function getSign($params) {
        unset($params['sign']);
        unset($params['pay_id']);
        ksort($params);
        $md5str = urldecode(http_build_query($params));
        $md5str .= "&key=" . $this->config['app_key'];
        return strtolower(md5($md5str));
    }
}
