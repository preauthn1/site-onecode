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

use app\admin\model\FakerModel;

class Faker extends Base {

    // 备份首页列表
    public function index() {

        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['user_id'])) {
                $where['user_id'] = $param['user_id'];
            }
            $model = new FakerModel();
            $result = $model->where($where)->limit($offset, $limit)->order('id desc')->select();
            $total = $model->where($where)->limit($offset, $limit)->count();
            // 拼装参数
            foreach ($result as $key => $vo) {
                $result[$key]['operate'] = showOperate($this->makeButton($vo['id']));
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
            $param = input('post.', '', 'trim');
            unset($param['id']);
            if ($id > 0) {
                (new FakerModel())->save($param, ['id' => $id]);
            }else{
                FakerModel::create($param);
            }
            return json(msg(1, url('index'), lang('提交成功')));
        }
        $info = FakerModel::where('id', $id)->find();
        $this->assign("info", $info);
        return $this->fetch();
    }

    // 删除用户
    public function del() {
        $id = input('param.id');
        FakerModel::where('id', $id)->delete();
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
                'auth' => 'faker/add',
                'href' => url('faker/add', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'faker/del',
                'href' => "javascript:del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

}
