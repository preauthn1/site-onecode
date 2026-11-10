<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\console\input\Option;
use think\Cache;
use think\Queue;
use think\Db;
class UserReport extends Command {
    protected function configure() {
        $this->setName('userreport')->setDescription('创建会员报表队列任务,请勿重复执行');
        $this->addArgument('date', Argument::OPTIONAL, "日期,默认当天")
            ->addArgument('uid', Argument::OPTIONAL, "指定用户ID")
            ->addOption('delete', 'd', Option::VALUE_NONE, '删除队列');
    }

    protected function execute(Input $input, Output $output) {
        $date = $input->getArgument("date");
        $uid = $input->getArgument("uid");
        $select = config("queue.select");
        if ($input->hasOption("delete")) {
            Cache::store("redis{$select}")->set("deleteQueue:user_report", true, 7*86400);
            $output->writeln("创建删除会员报表队列任务成功");
        }else{
            Cache::store("redis{$select}")->rm("deleteQueue:user_report");
            Queue::push("UserReport@create", ['date' => $date, 'uid' => intval($uid)], 'userreport');
            $output->writeln("创建会员报表队列任务成功");
        }
        
        $output->info("done！");
    }
}
