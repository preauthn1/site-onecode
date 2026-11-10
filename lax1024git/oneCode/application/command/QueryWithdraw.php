<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\Db;
use think\Cache;
use think\Queue;
class QueryWithdraw extends Command {

    protected function configure() {
        $this->setName('query_withdraw')->setDescription('查询提现订单');
        $this->addArgument('order_id', Argument::REQUIRED, "提现订单id");
    }

    protected function execute(Input $input, Output $output) {
        $id = $input->getArgument("order_id");
        $order = Db::name("withdrawals")->where("id", $id)->lock()->where("status", 4)->find();
        if (empty($order)) {
            throw new \InvalidArgumentException("订单「{$id}」不存在");
        }
        $pays = config("pay");
        if (!empty($order['channel']) 
                && isset($pays[$order['channel']]) 
                && isset($pays[$order['channel']]['transfer_check']) 
                && $pays[$order['channel']]['transfer_check'] == 1) {
            $cache = Cache::store("redis");
            if (!$cache->has("transfer_check_{$id}")) {
                $cache->set("transfer_check_{$id}", 1, 600);
                Queue::later(180, "Agentpay@query", $id, 'agentpay');
            }
        }
        dump($order);
        $output->info("done！");
    }
    

}
