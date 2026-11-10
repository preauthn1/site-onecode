<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\Queue;
class RestyActivity extends Command {

    protected function configure() {
        $this->setName('resty_activity')->setDescription('重新活动发放奖励');
        $this->addArgument('type', Argument::REQUIRED, "活动类型")
            ->addArgument('user_id', Argument::REQUIRED, "用户ID")
            ->addArgument('recharge_id', Argument::OPTIONAL, "recharge id");
    }

    protected function execute(Input $input, Output $output) {
        $user_id = $input->getArgument("user_id");
        $recharge_id = $input->getArgument("recharge_id");
        $type = $input->getArgument("type");
        if(is_null($user_id) || !in_array($type, [1,2,3])) {
            throw new \InvalidArgumentException('参数有误');
        }
        if(in_array($type, [1,3]) && is_null($recharge_id)) {
            throw new \InvalidArgumentException('参数有误');
        }
        $params = [
            'user_id' => $user_id,
            'type' => $type,
        ];
        if(is_numeric($recharge_id)) {
            $params['recharge_id'] = $recharge_id;
        }
        if ($params['type'] == 1) { //充值活动
            Queue::later(3, "ActivityTask@type1", $params, 'activity');
        }elseif($params['type'] == 2) { //邀请活动
            Queue::later(3, "ActivityTask@type2", $params, 'activity');
        }elseif($params['type'] == 3) { //邀请充值活动
            Queue::later(3, "ActivityTask@type3", $params, 'activity');
        }
        $output->info("done！");
    }
    

}
