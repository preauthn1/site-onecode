<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\Db;
class CheckLevel extends Command {

    protected function configure() {
        $this->setName('checklevel')->setDescription('会员升级');
        $this->addArgument('uid', Argument::OPTIONAL, "用户ID");
    }

    protected function execute(Input $input, Output $output) {
        $uid = $input->getArgument("uid");
        $where = [];
        if ($uid) {
            $where['id'] = $uid;
        }
        $lists = Db::name('home_user')->where($where)->order("id ASC")->column('id');
        if (!empty($lists)) {
            foreach ($lists as $userid) {
                checkLevel($userid, !$uid);
                $output->writeln("{$userid}===" . date("Y-m-d H:i:s") . " " . floor(umicrotime() * 1000) ."---" . umicrotime());
//                usleep(200*1000);
            }
        }
        $output->info("done！");
    }
    

}
