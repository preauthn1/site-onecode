<?php

namespace app\admin\controller;

use app\common\model\ActivityModel AS Model;
class Activity extends Base {

    public function index() {
        if (request()->isAjax()) {
            $param = $this->request->param();
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (isset($param['title']) && $param['title'] != '') {
                $where['title'] = ['LIKE', "%{$param['title']}%"];
            }
            $model = new Model();
            $result = $model->where($where)->limit($offset, $limit)->order('id DESC')->select();
            $total = $model->where($where)->limit($offset, $limit)->count();
            $status = [
                '0' => '停用',
                '1' => '启用',
            ];
            foreach ($result as $key => $vo) {
                $result[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                $result[$key]['status'] = getStatusText($vo['status'], $status[$vo['status']]);
                $result[$key]['thumb'] = $vo['thumb'] ? '<img src="' . toMedia($vo['thumb']) . '" width="40px" height="40px">' : '-';
            }
            $return['total'] = $total;  //总数据
            $return['rows'] = $result;
            return json($return);
        }
        return $this->fetch();
    }
    
    public function add() {
        $id = $this->request->param("id/d");
        if (request()->isPost()) {
            $param = input('post.');
            $model = new Model;
            if ($id > 0) {
                $result = $model->allowField(true)->isUpdate()->save($param);
            } else {
                $result = $model->allowField(true)->save($param);
            }
            if (false === $result) {
                $this->error($model->getError());
            }
            $url = url('index');
            $this->success(lang('提交成功'), $url);
        }
        $info = Model::where('id', $id)->find();
        if (empty($info)) {
            $model = new Model();
            $info = array_fill_keys($model->getTableFields(), '');
            $info['status'] = 1;
        }
        $this->assign("info", $info);
        return $this->fetch();
    }

    // 删除用户
    public function del() {
        $id = input('param.id');
        $info = Model::get($id);
        if ($info) {
            $info->delete();
        }
        return json(msg(1, url('index'), lang('删除成功')));
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id) {
        return [
            '编辑' => [
                'auth' => 'activity/add',
                'href' => url('add', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'activity/del',
                'href' => "javascript:del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
