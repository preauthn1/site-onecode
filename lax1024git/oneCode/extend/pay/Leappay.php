<?php

namespace pay;

use GuzzleHttp\Client;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use pay\Paybase;
class LeapPay extends Paybase{
    public $url = "http://payshuadana.payto89.com/order/place";
//    public $url = "http://payshuadana.payto89.com/order/getUrl";
    public $config = [];
    public function __construct($config) {
        if (empty($config['app_id']) || empty($config['app_key'])) {
            throw new Exception('config error');
        }
        $this->config = $config;
    }

    public function pay($order) {
        $params = array(
            "mch_id" => $this->config['app_id'],
            "ptype" => $this->config['pay_type'],
            "order_sn" => $order['order_id'],
            "money" => intval($order['money']),
            "goods_desc" => "goods_desc",
            "client_ip" => getip(),
            'format' => 'format',
            "notify_url" => $this->config['notify_url'],
            "time" => time(),
        );
        
        $params['sign'] = $this->getSign($params);
        \think\Log::write(["LeapPayParams", $params], 'pay', true);
        $assign = ['params' => $params, "gateway" => $this->url];
        return view(__DIR__ . "/wap.html", $assign);
        /*
        try {
            $client = new Client();
            $response = $client->get($this->url, [
                'form_params' => ['json' => json_encode($params)]
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $ex) {
            $response = $ex->getResponse();
        }
        return $this->validateResponse($response);*/
    }
    
    public function validateResponse($response) {
        $contents = $response->getBody()->getContents();
        $result = json_decode($contents, true);
        if (empty($result)) {
            \think\Log::write(["LeapPayError", $result], 'pay', true);
            throw new Exception(lang('error'));
        }
        
        \think\Log::write(["LeapPayResponse", $result], 'pay', true);
        if(isset($result['code']) && in_array($result['code'], [1,200])){
            return redirect($result['data']['url']) ;
        }else{
            throw new Exception(isset($result['msg']) ? $result['msg'] : "unkown error");
        }
    }
    
    public function notify() {
        $params = request()->param();
        \think\Log::write(["LeapPayNotifyParams", $params], 'pay', true);
        $sign = $this->getSign(myArrayFilter($params));
        if ($sign != $params["sign"]) {
            return 'sign error';
        }
        
        if (in_array($params["state"], [3,4])) {
            $remark = '';
            if (in_array($params['state'], [4])) { //成功
                $status = 2;
            }else{ //失败
                $status = 3;
                $remark = 'Payment Fail';
            }
            $trade_no = $params['pt_order'];
            $amount_true = null;
            $ret = $this->complete($params["sh_order"], $status, $remark, $trade_no, $amount_true);
            if ($ret === true) {
                return 'success';
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
        return md5($md5str);
    }
}
