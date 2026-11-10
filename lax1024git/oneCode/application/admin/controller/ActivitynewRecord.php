<?php

namespace app\admin\controller;

use app\common\model\ActivityRecordModel AS Model;
use app\common\model\ActivitynewModel;
use app\common\model\UserModel;
class ActivitynewRecord extends Base {

    public function index() {
        if (request()->isAjax()) {
            $param = $this->request->param();
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['username'])) {
                $user_id = UserModel::where('mobile', $param['username'])->value('id');
                $where['user_id'] = $user_id ?? $param['username'];
            }
            if ($param['activity_id'] !== '') {
                $where['activity_id'] = $param['activity_id'];
            }
            if (empty($param['startTime']) && empty($param['endTime'])) {
                $param['startTime'] = getDateStr();
                $param['endTime'] = getDateStr();
            }
            if ($param['from_user_id'] !== '') {
                $where['from_user_id'] = $param['from_user_id'];
            }
            if (!empty($param['startTime']) && !empty($param['endTime'])) {
                $start_time = strtotime($param['startTime'] . " 00:00:00");
                $end_time   = strtotime($param['endTime'] . " 23:59:59");
                $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
            }
            if (!empty($param['startTime']) && empty($param['endTime'])) {
                $start_time = strtotime($param['startTime'] . " 00:00:00");
                 $where['create_time'] = ['>=', $start_time];
            }
            if (!empty($param['endTime']) && empty($param['startTime'])) {
                $end_time   = strtotime($param['endTime'] . " 23:59:59");
                 $where['create_time'] = ['<=', $end_time];
            }
            $model = new Model();
            $result = $model->where($where)->limit($offset, $limit)->order('id desc')->select();
            $total = $model->where($where)->limit($offset, $limit)->count();
            $a_title = ActivitynewModel::column('title', 'id');
            $activity_reward_type = config("my.activity_reward_type");
            foreach ($result as $key => &$vo) {
                $vo['activity_id'] = $a_title[$vo['activity_id']];
                $vo['reward_type'] = $activity_reward_type[$vo['reward_type']];
                $vo['reward'] = i18n_sprintf($vo['reward']);
                $vo['befor_num'] = i18n_sprintf($vo['befor_num']);
                $vo['from_user_id'] = $vo['from_user_id'] > 0 ? $vo['from_user_id'] : '-';
            }
            unset($vo);
            $return['total'] = $total;  //总数据
            $return['rows'] = $result;
            $stat = $model->where($where)->group('activity_id')->field('activity_id, sum(reward) AS total')->select();
            if ($stat) {
                foreach ($stat as $k => $v) {
                    $stat[$k]['text'] = $a_title[$v['activity_id']];
                    $stat[$k]['total'] = i18n_sprintf($v['total']);
                }
            }
            $return['stat'] = $stat;
            return json($return);
        }
        $activitys = ActivitynewModel::field('id,title')->select();
        $this->assign('activitys', $activitys);
        return $this->fetch();
    }
}
