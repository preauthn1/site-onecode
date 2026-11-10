<?php

namespace transfer;

use think\Db;
use app\common\model\UserModel;

class Transferbase {

    public function complete($order_id, $status, $remark, $trade_no = null) {
        if (!in_array($status, [2, 3])) {
            return 'error';
        }
        
        Db::startTrans();
        try {
            $order = Db::name('withdrawals')->where('order_id', $order_id)->lock(true)->find();
            if (!empty($order) && $order['status'] == 4) {
                $money = $order['money'] + $order['taxfee'];
                if ($status == 2) { //成功
                    Db::name('home_user')->where(['id' => $order['user_id']])->setInc('frozen_money', (-1 * $money));
                }else{ //失败
                    addMoneyLog($order['user_id'], $money, 8, '提现转账失败');
                    Db::name('home_user')->where(['id' => $order['user_id']])->setInc('frozen_money', (-1 * $money));
                }
                $update = [
                    'status' => $status, 
                    'remark' => $remark,
                    "trade_no" => $trade_no,
                    "pay_time" => timeToStr(),
                ];
                if (!is_null($trade_no)) {
                    $update['trade_no'] = $trade_no;
                }
                Db::name('withdrawals')->where(['id' => $order['id']])->update($update);
            }
            // 提交事务
            Db::commit();
        } catch (\think\Exception $exc) {
            \think\Log::write($exc->getMessage() . "\n" . $exc->getTraceAsString(), "error");
            // 回滚事务
            Db::rollback();
            return 'fail';
        }
        return true;
    }

}
