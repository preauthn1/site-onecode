<?php

namespace transfer;

use GuzzleHttp\Client;
use think\Exception;
use think\Db;
use app\common\model\UserModel;
use transfer\Transferbase;

class Thaipay extends Transferbase{
    public $url = "https://api.asiatopayment.com/Behalfpay/submitOrder";
    public $get_order_url = "https://api.asiatopayment.com/Behalfpay/query";
    public $config = [];
    public function __construct($config) {
        if (empty($config['app_id']) || empty($config['app_key'])) {
            throw new Exception('config error');
        }
        $this->config = $config;
    }

    public function pay($order) {
        if (isset($order['trade_no']) && !empty($order['trade_no'])) {
            return true;
        }
        $user = UserModel::where('id', $order['user_id'])->find();
        $countryCode = $this->getCurrencyCode();
        $params = array(
            "appid" => $this->config['app_id'],
            "currency_code" => $countryCode,
            "trade_no" => $order['order_id'],
            "money" => sprintf("%.2f", $order['money']),
            "payee_name" => $order['true_name'],
            "card_no" => $order['bank_number'],
            "bank_code" => $order['bank_name'],
            "bank_name" => $this->getBankName($order['bank_name']),
            "bank_branch" => "bank_branch",
            "bank_address" => "bank_address",
            "province" => "province",
            "city" => "city",
            "union_code" => "union_code",
            "bank_mobile" => $user['mobile'],
            "time" => strval(time()),
            "notify_url" => $this->config['notify_url'],
            "sign_type" => 'MD5',
        );

        $params['sign'] = $this->getSign($params);
//        dump($params);
        \think\Log::write(["ThaipayTransferParams", $params], 'transfer', true);
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
    
    
    public function find($trade_no, $orderid) {
        $params = array(
            'appid' => $this->config['app_id'],
            'trade_no' => $trade_no,
            'orderid' => $orderid,
            "time" => strval(time()),
            'sign_type' => 'MD5',
        );
        $params['sign'] = $this->getSign($params);
//        dump($params);
        \think\Log::write(["ThaipayTransferFindParams", $params], 'transfer', true);
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
            \think\Log::write(["ThaipayTransferError", $contents], 'transfer', true);
            throw new Exception(lang('error'));
        }
        \think\Log::write(["ThaipayTransferResponse", $result], 'transfer', true);
        if($result['error'] < 0){
            if ($type == 'pay' && isset($result['data']['trade_no']) && isset($result['data']['orderid'])) {
                Db::name('withdrawals')->where('order_id', $result['data']['trade_no'])->update([
                    'trade_no' => $result['data']['orderid']
                ]);
            }
            return $type == 'pay' ? true : $result['data'];
            
        }else{
            if ($result['error'] == '8030109') {
                return true;
            }
            throw new Exception(isset($result['msg']) ? $result['msg'] : "unkown error");
        }
    }
    
    public function notify() {
        $params = request()->param();
        \think\Log::write([$params, "ThaipayNotifyParams"], 'transfer', true);
        $sign = $this->getSign(myArrayFilter($params));
        if ($sign != $params["sign"]) {
            \think\Log::write([$sign, "ThaipayNotifyParamsErrorSign"], 'transfer', true);
            return 'sign error';
        }
        $find = $this->find($params["trade_no"], $params["orderid"]);
        \think\Log::write(["ThaipayTransferFindResult", $find], 'transfer', true);
        if ($find && in_array($find["order_status"], [3, 4])) {
            $remark = '';
            if (in_array($find['order_status'], [3])) { //成功
                $status = 2;
            }else{ //失败
                $status = 3;
                $remark = '提现转账失败';
            }
            $trade_no = null;
            $ret = $this->complete($find["trade_no"], $status, $remark, $trade_no);
            if ($ret === true) {
                return 'success';
            }
            return $ret;
        }
        return 'fail';
    }

    private function getSign($params) {
        unset($params['sign']);
        unset($params['sign_type']);
        unset($params['channel']);
        ksort($params);
        $md5str = urldecode(http_build_query($params));
        $md5str .= $this->config['app_key'];
        return (md5($md5str));
    }

    private function getBankName($code) {
        $banks = [
            "SCB" => "THE SIAM COMMERCIAL BANK PUBLIC COMPANY",
            "KBANK" => "KASIKORNBANK PCL",
            "KTB" => "KRUNG THAI BANK PUBLIC COMPANY LTD.",
            "BBL" => "BANGKOK BANK PUBLIC COMPANY LTD.",
            "TMB" => "TMB BANK PUBLIC COMPANY LTD.",
            "GSB" => "THE GOVERNMENT SAVING BANK",
            "BAY" => "BANK OF AYUDHAYA PUBLIC COMPANY LTD.",
            "CIMB" => "CIMB THAI BANK PUBLIC COMPANY LTD.",
            "TBANK" => "Thanachart Bank Public Company Limited",
            "BAAC" => "BANK FOR AGRICULTURE AND AGRICULTURAL CO-OPERATIVES",
            "LHBANK" => "Land and Houses Bank",
            "GHB" => "GOVERNMENT HOUSING BANK",
        ];
        return isset($banks[$code]) ? $banks[$code] : $code;
    }

    private function getCurrencyCode() {
        $code = i18n_currency_code();
        $res = [
            'CNY' => 'CNY',
            'USDT' => 'USDT',
            'LAK' => 'LAK',
            'RP' => 'IDR',
            'INR' => 'INR',
            'VND' => 'VND',
            'THB' => 'THB',
            'RM' => 'MYR',
            'NGA' => 'NGN',
        ];
        
        return isset($res[$code]) ? $res[$code] : $code;
    }
}
