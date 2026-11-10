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

namespace app\agent\controller;

use app\admin\model\NoticeModel AS Model;
use app\admin\model\MemberModel;

class Notice extends Base {

    // 文章列表
    public function index() {
        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            $where['user_id'] = ['IN', function($query){
                $query->name('home_user')->where('tree_id', 'LIKE', "%/{$this->userid}/%")->field('id');
            }];
            $langs = getLangsColumn();
            $notice = new Model();
            $selectResult = $notice->getNoticeByWhere($where, $offset, $limit);
            $status = config('status');
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['status'] = $status[$vo['status']];
//                $selectResult[$key]['create_time'] = date('Y-m-d H:i:s', $vo['create_time']);
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                $selectResult[$key]['lang'] = $langs[$vo['lang']];
                $selectResult[$key]['content'] = html_entity_decode($vo['content']);
            }
            $return['total'] = $notice->getAllNotice($where);  // 总数据
            $return['rows'] = $selectResult;
            return json($return);
        }

        return $this->fetch();
    }

    public function add() {
        $id = $this->request->param("id/d");
        if (request()->isPost()) {
            $param = input('post.');
            $param['user_id'] = $this->request->param('user_id/d');
            $model = new Model;
            $user = MemberModel::where('id', $param['user_id'])->where('tree_id', 'LIKE', "%/{$this->userid}/%")->find();
            if (empty($user)) {
                $this->error('用户ID有误');
            }
            if ($param['user_id'] > 0) {
            }
            if ($id > 0) {
                $result = $model->isUpdate()->save($param);
            } else {
                $result = $model->allowField(true)->save($param);
            }
            if (false === $result) {
                $this->error($model->getError());
            }
            $this->success(lang('提交成功'), url('index'));
        }
        $info = Model::where('id', $id)->find();
        if (empty($info)) {
            $model = new Model();
            $info = array_fill_keys($model->getTableFields(), '');
            $info['status'] = 1;
        }
        $this->assign("info", $info);
        $this->assign("langs", getLangs());
        $this->assign("status", config('status'));
        return $this->fetch();
    }

    public function del() {
        $id = input('param.id');

        $notice = new Model();
        $flag = $notice->delNotice($id);
        return json(msg($flag['code'], $flag['data'], lang($flag['msg'])));
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id) {
        return [
            '编辑' => [
                'auth' => 'notice/add',
                'href' => url('add', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'notice/del',
                'href' => "javascript:del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

}
