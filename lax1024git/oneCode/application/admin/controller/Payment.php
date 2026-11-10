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

use app\admin\model\PaymentModel;

class Payment extends Base {

    public function index() {
        if (request()->isAjax()) {
            return $this->getdata(1);
        }
        $this->assign('url', url('index'));
        $this->assign('type', 1);
        return $this->fetch();
    }
    
    public function alipay() {
        if (request()->isAjax()) {
            return $this->getdata(2);
        }
        $this->assign('type', 2);
        $this->assign('url', url('alipay'));
        return $this->fetch('index');
    }
    public function third() {
        if (request()->isAjax()) {
            return $this->getdata(3);
        }
        $this->assign('type', 3);
        $this->assign('url', url('third'));
        return $this->fetch('index');
    }
    public function usdt() {
        if (request()->isAjax()) {
            return $this->getdata(4);
        }
        $this->assign('type', 4);
        $this->assign('url', url('usdt'));
        return $this->fetch('index');
    }

    private function getdata($type) {
        $param = input('param.');
        $limit = $param['pageSize'];
        $offset = ($param['pageNumber'] - 1) * $limit;
        $where = ['type' => $type];
        $model = new PaymentModel();
        $result = $model->where($where)->limit($offset, $limit)->order('sort ASC,id desc')->select();
        $total = $model->where($where)->limit($offset, $limit)->count();
        // 拼装参数
        $type_text = config('my.pay_type');
        $pays = config('pay');
        foreach ($result as $key => $vo) {
            $result[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            $result[$key]['type'] = $type_text[$vo['type']];
            $result[$key]['account'] = isset($pays[$vo['account']]) ? $pays[$vo['account']]['name'] : $vo['account'];
            $result[$key]['status'] = $vo['status'] == 1 ? '启用' : '停用';
        }
        $return['total'] = $total;  //总数据
        $return['rows'] = $result;
        return json($return);
    }


    public function add() {
        $id = $this->request->param("id/d");
        $type = $this->request->param("type/d", 1);
        if (request()->isPost()) {
            $param = input('post.');
            unset($param['id']);
            if ($id > 0) {
                insert_operation_log('充值渠道:修改', $param);
                (new PaymentModel())->save($param, ['id' => $id]);
            }else{
                insert_operation_log('充值渠道:添加', $param);
                PaymentModel::create($param);
            }
            if ($type == 3) {
                $url = url('third');
            } elseif ($type == 2) {
                $url = url('alipay');
            } elseif ($type == 4) {
                $url = url('usdt');
            }else{
                $url = url('index');
            }
            return json(msg(1, $url, '提交成功'));
        }
        $info = PaymentModel::where('id', $id)->find();
        if (empty($info)) {
            $info = [];
            $info['type'] = $type;
            $info['status'] = 1;
        }
        $this->assign("info", $info);
        $this->assign('pay', config('pay'));
        return $this->fetch();
    }

    // 删除用户
    public function del() {
        $id = input('param.id');
        $info = PaymentModel::get($id);
        if ($info) {
            insert_operation_log('充值渠道:删除', $info->toArray());
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
                'auth' => 'payment/add',
                'href' => url('payment/add', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'payment/del',
                'href' => "javascript:del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

}
