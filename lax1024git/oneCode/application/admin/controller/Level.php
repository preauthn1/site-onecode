<?php

// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\model\LevelModel;
use app\admin\model\ConfigModel;
use think\Db;

class Level extends Base {

    //用户列表
    public function index() {
        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['searchText'])) {
                $where['level_name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $level = new LevelModel();
            $selectResult = $level->getLevelByWhere($where, $offset, $limit);
            $status = config('status');
            // 拼装参数
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['status'] = $status[$vo['status']];
                $selectResult[$key]['img'] = $vo['img'] ? '<img src="' . toMedia($vo['img']) . '" width="40px" height="40px">' : '';
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['level_id']));
            }
            $return['total'] = $level->getAllLevel($where);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
        return $this->fetch();
    }

    public function add() {
        $id = $this->request->param("id/d");
        $model = new LevelModel();
        if (request()->isPost()) {
            $param = input('post.');
            if ($id > 0) {
                $result = $model->where('level_id',$id)->update($param);
            } else {
                $result = $model->allowField(true)->save($param);
            }
            if (false === $result) {
                $this->error($model->getError());
            } else {
                insert_operation_log('会员等级:' . ($id > 0 ? '修改' : '添加'), $param);
            }
            $this->success(lang('提交成功'), url('index'));
        }
        $info = $model->where('level_id', $id)->find();
        if (empty($info)) {
            $sort = LevelModel::max('sort');
            $info = [
                'status' => 1,
                'sort' => intval($sort) + 1
            ];
        }
        $this->assign("info", $info);
        $this->assign("status", config('status'));
        return $this->fetch();
    }

    // 删除用户
    public function levelDel() {
        $id = input('param.id');
        $role = new LevelModel();
        $flag = $role->delLevel($id);
        return json(msg($flag['code'], $flag['data'], lang($flag['msg'])));
    }

    public function setting() {
        $id = $this->request->param("id/d");
        $level = new LevelModel();
        $info = $level->getOneLevel($id);
        if (empty($info)) {
            $this->error(lang('等级信息有误'));
        }
        if (request()->isPost()) {
            $param = $this->request->param();
            unset($param['id']);
            $ret = $level->save([
                'setting' => $param
                    ], ['level_id' => $id]);
            if ($ret !== false) {
                $redis = \think\Cache::store('redis');
                $redis_key = "home_user_level{$id}";
                $redis->rm($redis_key);
                insert_operation_log('会员等级:修改设置', $param);
                $this->success(lang('设置成功'));
            } else {
                $this->error(lang('设置失败'));
            }
        }
        $config = new ConfigModel();
        $this->assign([
            'info' => $info,
            'config' => $config->getConfig(),
        ]);
        return $this->fetch();
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id) {
        return [
            '编辑' => [
                'auth' => 'level/add',
                'href' => url('level/add', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
//            '设置' => [
//                'auth' => 'level/setting',
//                'href' => url('level/setting', ['id' => $id]),
//                'btnStyle' => 'info',
//                'icon' => 'fa fa-gear'
//            ],
                /* '删除' => [
                  'auth' => 'level/leveldel',
                  'href' => "javascript:levelDel(" .$id .")",
                  'btnStyle' => 'danger',
                  'icon' => 'fa fa-trash-o'
                  ] */
        ];
    }

}
