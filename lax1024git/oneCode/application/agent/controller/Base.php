<?php

namespace app\agent\controller;

use think\Controller;
use app\common\model\UserModel;

class Base extends Controller {
    public $userid = null;
    public $userInfo = null;
    public function _initialize() {
        if (empty(session('?username', '', 'agent')) || empty(session('?userid', '', 'agent'))) {
            $loginUrl = url('login/index');
            if (request()->isAjax()) {
                echo json_encode(msg(111, $loginUrl, '登录超时'));
                die;
            }
            
            $this->redirect($loginUrl);
        }
        $this->userid = session('userid', '', 'agent');
        $model = new UserModel;
        $this->userInfo = $model->getOneUser($this->userid);
        if (empty($this->userInfo)) {
            session('username', null, 'agent');
            session('userid', null, 'agent');
            $loginUrl = url('login/index');
            if (request()->isAjax()) {
                echo json_encode(msg(111, $loginUrl, '登录超时'));
                die;
            }
            $this->redirect($loginUrl);
        }
        $this->assign([
            'userInfo' => $this->userInfo,
        ]);
    }
}
