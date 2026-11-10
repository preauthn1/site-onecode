<?php

namespace app\index\controller;

use app\common\model\ActivityModel;
use app\common\model\ActivityRecordModel;

class Activity extends Base {

    public function index() {
        $this->setTitle(lang('活动'));
        if ($this->request->isAjax()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('pageSize/d', 10);
            $offset = ($page >= 1 ? $page - 1 : 0) * $limit;
            $list = ActivityModel::where('status', 1)->limit($offset, $limit)->order('id ASC')->select();
            if (empty($list)) {
                $this->error(lang('没有数据'));
            }
            foreach ($list as $key => $value) {
            }
            $content = $this->fetch('item', ['list' => $list]);
            $this->success('ok', '', $content);
        }
        return $this->fetch();
    }

    public function receive() {
        $lock = userLock($this->userid, 'receive', 60);
        if (!$lock) {
            return $this->error(lang('请勿频繁提交'));
        }
        
        $id = $this->request->param('id/d');
        $info = ActivityModel::where('status', 1)->where('id', $id)->find();
        if (empty($info)) {
            return $this->error(lang('活动信息不存在'));
        }
        
        $record = ActivityRecordModel::where('user_id', $this->userid)->whereIn('status', '0,1')->find();
        if ($record) {
            return $this->error(lang('您已经领取过活动了'));
        }
        
        ActivityRecordModel::create([
            'user_id' => $this->userid,
            'activity_id' => $id,
            'status' => 0
        ]);
        return $this->success(lang('领取成功'));
    }
}
