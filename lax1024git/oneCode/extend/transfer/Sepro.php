<?php

namespace transfer;

use GuzzleHttp\Client;
use think\Exception;
use app\common\model\BankModel;
use app\common\model\UserModel;
use think\Db;
class Sepro extends Transferbase{
    public $url = "https://pay.sepropay.com/pay/transfer";
    public $get_order_url = "https://pay.sepropay.com/query/transfer";
    public $config = [];
    public function __construct($config) {
        if (empty($config['app_id']) || empty($config['transfer_app_key'])) {
            throw new Exception('config error');
        }
        $this->config = $config;
    }

    public function pay($order) {
        if (isset($order['trade_no']) && !empty($order['trade_no'])) {
            return true;
        }
        $params = [
            "mch_id" => $this->config['app_id'],
            "mch_transferId" => $order['order_id'],
            "transfer_amount" => strval(intval($order['money'])),
            "apply_date" => timeToStr(),
            "bank_code" => $this->getBankCode($order['bank_name']),
            "receive_name" => $order['true_name'],
            "receive_account" => $order['bank_number'],
            "remark" => !isset($order['bank_kh']) || empty($order['bank_kh']) ? $this->getBankIfsc($order['bank_name']) : $order['bank_kh'],
            "back_url" => $this->config['notify_url'],
        ];
        
        $params['sign'] = $this->getSign($params);
        $params['sign_type'] = 'MD5';
//        dump($params);die;
        \think\Log::write(["SeproTransferParams", $params], 'transfer', true);
        try {
            $client = new Client();
            $response = $client->post($this->url, [
                'form_params' => $params,
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
            'mch_id' => $this->config['app_id'],
            'mch_transferId' => $order_id,
        );
        $params['sign'] = $this->getSign($params);
        $params['sign_type'] = 'MD5';
        \think\Log::write(["SeproTransferFindParams", $params], 'transfer', true);
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
            \think\Log::write(["SeproTransferError", $result], 'transfer', true);
            throw new Exception(lang('error'));
        }
        
        \think\Log::write(["SeproTransferResponse", $result], 'transfer', true);
        
        if(isset($result['respCode']) && in_array($result['respCode'], ["SUCCESS"])){
            if ($type == 'pay' && isset($result['tradeNo']) && isset($result['merTransferId']) ) {
                Db::name('withdrawals')->where('order_id', $result['merTransferId'])->update([
                    'trade_no' => $result['tradeNo']
                ]);
            }
            return $type == 'pay' ? true : $result;
        }elseif($result['errorMsg'] === 'MER_REMIT_RECORD_EXSIST'){
            return true;
        }else{
            $msg = isset($result['errorMsg']) ? $result['errorMsg'] : "unkown error";
        }
        throw new Exception($msg);
    }
    
    public function notify() {
        $params = request()->param();
        \think\Log::write(["SeproTransferNotifyParams", $params], 'transfer', true);
        if (empty($params)) {
            return 'requestParams error';
        }
        $sign = $this->getSign(myArrayFilter($params));
        if ($sign != $params["sign"]) {
            return 'sign error';
        }
        $find = $this->find($params["merTransferId"]);
        \think\Log::write(["SeproFindNotifyParams", $find], 'transfer', true);
        if ($find && in_array($find["tradeResult"], [1, 2])) {
            $remark = '';
            if (in_array($find['tradeResult'], [1])) { //成功
                $status = 2;
            }else{ //失败
                $status = 3;
                $remark = '提现转账失败';
            }
            $trade_no = $params['tradeNo'];
            $ret = $this->complete($params["merTransferId"], $status, $remark, $trade_no);
            if ($ret === true) {
                return 'success';
            }
            return $ret;
        }
        return 'error';
    }

    private function getSign($params) {
        unset($params['sign']);
        unset($params['signType']);
        unset($params['channel']);
        ksort($params);
        $md5str = urldecode(http_build_query($params));
        $md5str .= "&key=" . $this->config['transfer_app_key'];
        return md5($md5str);
    }
    
    private function getBankIfsc($code) {
        $extra = BankModel::where('bank_code', $code)->value('extra');
        return $extra ?? $code;
    }
    private function getBankCode($code) {
        $_bank = [
            'CB' => 'IDPT0001',
            'DCBBANK' => 'IDPT0002',
            'FDRL' => 'IDPT0003',
            'HDFC' => 'IDPT0004',
            'PUNB' => 'IDPT0005',
            'IB' => 'IDPT0006',
            'ICIC' => 'IDPT0007',
            'SYNB' => 'IDPT0008',
            'KVBL' => 'IDPT0009',
            'UNBK' => 'IDPT0010',
            'KKBK' => 'IDPT0011',
            'IDFB' => 'IDPT0012',
            'ANDB' => 'IDPT0013',
            'AXIS' => 'IDPT0016',
            'UCO' => 'IDPT0017',
            'NSE' => 'IDPT0019',
            'SBIN' => 'IDPT0021',
            'IOB' => 'IDPT0022',
            'CBI' => 'IDPT0024',
            'BOB' => 'IDPT0025',
        ];
        return isset($_bank[$code]) ? $_bank[$code] : 'IDPT0001';
    }
}
