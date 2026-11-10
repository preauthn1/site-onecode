<?php

namespace app\admin\controller;

use app\common\model\BankModel AS Model;
class Bank extends Base {

    public function index() {
        if (request()->isAjax()) {
            $langs = getLangsColumn();
            $param = $this->request->param();
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (isset($param['lang']) && $param['lang'] != '') {
                $where['lang'] = $param['lang'];
            }elseif(isArrayAndNotEmpty($langs)){
                $where['lang'] = ["IN", array_keys($langs)];
            }
            if (isset($param['name']) && $param['name'] != '') {
                $where['name|bank_code'] = ['LIKE', "%{$param['name']}%"];
            }
            $model = new Model();
            $result = $model->where($where)->limit($offset, $limit)->order('lang DESC,sort ASC')->select();
            $total = $model->where($where)->limit($offset, $limit)->count();
            foreach ($result as $key => $vo) {
                $result[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                $result[$key]['status'] = $vo['status'] == 1 ? lang('启用') : lang('停用');
                $result[$key]['lang'] = isset($langs[$vo['lang']]) ? $langs[$vo['lang']] : '-';
                $result[$key]['logo'] = $vo['logo'] ? '<img src="' . toMedia($vo['logo']) . '" width="40px" height="40px">' : '-';
            }
            $return['total'] = $total;  //总数据
            $return['rows'] = $result;
            return json($return);
        }
        $this->assign("langs", getLangs());
        return $this->fetch();
    }
    
    public function add() {
        $id = $this->request->param("id/d");
        if (request()->isPost()) {
            $param = input('post.');
            $model = new Model;
            if ($id > 0) {
                $result = $model->validate('BankValidate')->isUpdate()->save($param);
            } else {
                $result = $model->validate('BankValidate')->allowField(true)->save($param);
            }
            if (false === $result) {
                $this->error($model->getError());
            } else {
                insert_operation_log('提现银行:' . ($id > 0 ? '修改' : '添加'), $param);
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
        $this->assign("langs", getLangs());
        $this->assign("info", $info);
        return $this->fetch();
    }

    // 删除用户
    public function del() {
        $id = input('param.id');
        $info = Model::get($id);
        if ($info) {
            insert_operation_log('提现银行:删除', $info->toArray());
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
                'auth' => 'bank/add',
                'href' => url('bank/add', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'bank/del',
                'href' => "javascript:del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

    
    public function test() {
        $banks = config('bank');
        $bank_pattern = config('bank_pattern');
        $data = [];
        $model = new Model;
        foreach ($banks as $lang => $arr) {
            $i = 0;
            $bank_pattern2 = $bank_pattern[$lang];
            foreach ($arr as $name => $code) {
                $logo = '';
                if (file_exists(ROOT_PATH . DS . "public" . DS . $name)) {
                    $logo = str_replace("/upload", "", $name);
                    $name = $code;
                }
                $row = [
                    'lang' => $lang,
                    'name' => $name,
                    'bank_code' => $code,
                    'logo' => $logo,
                    'sort' => $i,
                    'status' => 1,
                    'bank_number_rule' => isset($bank_pattern2[$code]) ? $bank_pattern2[$code] : ''
                ];
                $exists = $model->where([
                    'lang' => $lang,
                    'bank_code' => $code,
                ])->find();
                if ($exists) {
                    continue;
                }
                $i++;
                $data[] = $row;
            }
        }
        $model = new Model;
        $res = $model->saveAll($data);
        dump($res);
        die;
    }
}
