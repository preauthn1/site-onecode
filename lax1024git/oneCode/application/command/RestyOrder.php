<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\console\input\Argument;
class RestyOrder extends Command {

    protected function configure() {
        $this->setName('resty_order')->setDescription('重置队列任务');
        $this->addArgument('date', Argument::OPTIONAL, "日期");
    }

    protected function execute(Input $input, Output $output) {
        $count = 0;
        $orders = Db::name('order')->where("status", 5)->select();
        foreach ($orders as $row) {
            $count++;
            $time = createOrderQueue($row['id'], $row['user_id']);
            $output->info("用户ID：{$row['user_id']}, 订单ID：{$row['id']}, 返佣：{$row['gray']}, 时间：{$time}秒, 计数：{$count}");
            usleep(10*1000);
        }
        $output->info("done！");
    }
    

}
