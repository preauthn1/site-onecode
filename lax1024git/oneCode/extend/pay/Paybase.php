<?php

namespace pay;

use think\Db;
use app\common\model\UserModel;

class Paybase {

    public function complete($order_id, $status, $remark, $trade_no, $amount_true = null) {
        if (!in_array($status, [2, 3])) {
            return 'error';
        }
        Db::startTrans();
        try {
            $order = Db::name('recharge')->where('order_id', $order_id)->lock(true)->find();
            if (!empty($order) && $order['status'] == 1) {
                $remark = '';
                if (!is_null($amount_true) && $amount_true != $order['money']) {
                    $order['money'] = $amount_true;
                    $remark = '实付金额与订单金额不符，按实付金额上分';
                }
                if ($status == 2) { //成功
                    $Ymd = timeToStr(time(), 'Ymd');
                    $full_table_name = "qd_money_log_{$Ymd}";
                    createMoneyLogDayTable($full_table_name);
                    $recharge = Db::table($full_table_name)->where([
                        'order_id' => $order['id'], 
                        'user_id' => $order['user_id'],
                        'type' => 1,
                    ])->find();
                    if (empty($recharge)) {
                        addMoneyLog($order['user_id'], $order['money'], 1, '充值', 0, 0, 0, $order['id']);
                        $model = new UserModel();
                        $user = $model->getOneUser($order['user_id']);
                        checkLevel($user['leader1']); // 会员升级
                        checkLevel($order['user_id']); // 会员升级
                    }
                }

                $update = [
                    "status" => $status,
                    "money" => $order['money'],
                    "remark" => $remark,
                    "pay_time" => timeToStr(),
                ];
                if (!is_null($trade_no)) {
                    $update['trade_no'] = $trade_no;
                }
                Db::name('recharge')->where(['id' => $order['id']])->update($update);
                if ($status == 2) {
                    triggerActivityTask([
                        'user_id' => $order['user_id'],
                        'recharge_id' => $order['id'],
                        'type' => 1,
                    ]);
                    //自己充值触发规则
                    triggerActivityTask([
                        'user_id' => $order['user_id'],
                        'recharge_id' => $order['id'],
                        'trigger_type' => 1,
                        'type' => 6,
                    ]);
                    if($user['leader1'] > 0) {
                        //下级充值触发规则
                        triggerActivityTask([
                            'from_user_id' => $order['user_id'],
                            'user_id' => $user['leader1'],
                            'recharge_id' => $order['id'],
                            'trigger_type' => 2,
                            'type' => 6,
                        ]);
                        //下级首充触发规则
                        triggerActivityTask([
                            'from_user_id' => $order['user_id'],
                            'user_id' => $user['leader1'],
                            'recharge_id' => $order['id'],
                            'trigger_type' => 7,
                            'type' => 6,
                        ]);
                    }
                }
            } else {
                return 'no order';
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
    
    
    protected function redirect($message, $type = 'success') {
        $url = "/payed/{$type}/" . urlencode($message);
        return getWebUrl() . $url;
    }
    protected function success($message) {
        return $this->redirect($message, 'success');
    }
    protected function error($message) {
        return $this->redirect($message, 'error');
    }

}
