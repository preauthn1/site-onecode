<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use app\admin\model\DayReportModel;
class DayReport extends Command {

    protected function configure() {
        $this->setName('day_report')->setDescription('日报表');
        $this->addArgument('date', Argument::OPTIONAL, "日期")
             ->addArgument('uid', Argument::OPTIONAL, "用户ID");
    }

    protected function execute(Input $input, Output $output) {
        $date = $input->getArgument("date");
        $uid = $input->getArgument("uid");
        if (is_null($date)) {
            $date = getDateStr(strtotime("-1 day"));
        }
        $model = new DayReportModel();
        if ($uid) {
            $output->info("用户「{$uid}」日报表：「{$date}」！");
            $model->agent_stat($date, true, $uid, 'all');
        }else{
            $output->info("日报表：「{$date}」！");
            $model->stat($date, true, 'all');
        }
        
        $output->info("done！");
    }
    

}
