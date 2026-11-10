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

use app\admin\model\RoleModel;
use app\admin\model\UserModel;
use PHPGangsta_GoogleAuthenticator;
use app\agent\model\AgentModel;

class User extends Base {

    //用户列表
    public function index() {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where2 = $where = [];
            if (!empty($param['searchText'])) {
                $where2['user_name'] = $where['user_name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            if(session('id') != 1) {
                $where['user.id'] = ['<>', 1];
                $where2['id'] = ['<>', 1];
            }
            if (session('role_id') != 1) {
                $where['user.role_id'] = ['<>', 1];
                $where2['role_id'] = ['<>', 1];
            }
            $user = new UserModel();
            $selectResult = $user->getUsersByWhere($where, $offset, $limit);

            $status = config('user_status');

            // 拼装参数
            foreach ($selectResult as $key => $vo) {

                $selectResult[$key]['last_login_time'] = $vo['last_login_time'] > 0 ? date('Y-m-d H:i:s', $vo['last_login_time']) : '-';
                $selectResult[$key]['status'] = $status[$vo['status']];
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }

            $return['total'] = $user->getAllUsers($where2);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    // 添加用户
    public function userAdd() {
        if (request()->isPost()) {

            $param = input('post.');

            $param['password'] = md5($param['password'] . config('salt'));
            $param['head'] = '/static/admin/images/profile_small.jpg'; // 默认头像
            
            if(session('id') != 1 && $param['role_id'] == 1) {
                return json(msg(-1, '', '超级管理员角色账号只能由admin用户添加'));
            }
            
            $user = new UserModel();
            $flag = $user->insertUser($param);
            if ($flag['code'] == 1) {
                insert_operation_log('管理员:添加', "账户:{$param['user_name']}");
            }
            return json(msg($flag['code'], $flag['data'], lang($flag['msg'])));
        }

        $role = new RoleModel();
        $this->assign([
            'role' => $role->getRole(),
            'status' => config('user_status')
        ]);
        
        return $this->fetch();
    }

    // 编辑用户
    public function userEdit() {
        $userm = new UserModel();

        $id = input('param.id');
        $user_id = session('id');
        $user = $userm->getOneUser($id);
        if (request()->isPost()) {
            $param = input('post.');

            if (empty($param['password'])) {
                unset($param['password']);
            } else {
                $param['password'] = md5($param['password'] . config('salt'));
            }
            if(($user_id != 1 && $user_id != $param['id']) && $user['role_id'] == 1) {
                return json(msg(-1, '', '超级管理员角色账号只能由admin用户修改或账号本人修改'));
            }
            $flag = $user->editUser($param);
            if ($flag['code'] == 1) {
                insert_operation_log('管理员:修改', "账户:{$param['user_name']}");
            }
            
            return json(msg($flag['code'], $flag['data'], lang($flag['msg'])));
        }
        
        if(($user_id != 1 && $user_id != $param['id']) && $user['role_id'] == 1) {
            return $this->error('超级管理员角色账号只能由admin用户修改或账号本人修改');
        }
        $role = new RoleModel();
        $this->assign([
            'user' => $user,
            'status' => config('user_status'),
            'role' => $role->getRole(),
        ]);
        return $this->fetch();
    }

    // 删除用户
    public function userDel() {
        $id = input('param.id');
        if ($id == 1) {
            return json(msg(-1, '', lang('超级管理员不能删除')));
        }
        $user = UserModel::get($id);
        if (!$user) {
            return json(msg(-1, '', lang('管理员不存在')));
        }
        $user->delete();
        insert_operation_log('管理员:删除', "账户:{$user->user_name}");
        return json(msg(1, '', lang('删除管理员成功')));
    }
    
    public function authy() {
        $userModel = new UserModel();
        $user = $userModel->where("id", session('id'))->find();
        if ($this->request->isAjax() && $this->request->isPost()) {
            if ($user['authkey']) {
                $this->error(lang("您已经绑定了谷歌密钥,不能重复绑定!"));
            }
            $secret = $this->request->param("secret/s");
            $code = $this->request->param("code/s");
            
            $ga = new PHPGangsta_GoogleAuthenticator();
//            $oneCode = $ga->getCode($secret);
            $checkResult = $ga->verifyCode($secret, $code);
            if ($checkResult) {
                $user->authkey = $secret;
                $user->save();
                insert_operation_log('管理员:绑定谷歌验证码', "账户:{$user->user_name}");
                $this->success(lang("绑定成功"));
            }else{
                $this->error(lang("谷歌验证码错误"));
            }
        }
        if (!$user['authkey']) {
            $config = new \app\common\model\ConfigModel();
            $configInfo = $config->getConfig();
            $ga = new PHPGangsta_GoogleAuthenticator();
            $secret = $ga->createSecret();
            $data = $ga->getQRCodeGoogleUrl(session('username'), $secret, $configInfo['title']);
        }else{
            die(lang("您已经绑定了谷歌密钥,不能重复绑定!"));
        }
        
        $this->assign("title", lang("绑定谷歌验证码"));
        $this->assign("secret", $secret);
        $this->assign("google_auth_key", $user['authkey']);
        $this->assign("data", $data);
        return $this->fetch();
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id) {
        $btn = [
            '编辑' => [
                'auth' => 'user/useredit',
                'href' => url('user/userEdit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ]
        ];
        if ($id != 1) {
            $btn['删除'] = [
                'auth' => 'user/userdel',
                'href' => "javascript:userDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ];
        }
        return $btn;
    }

}
