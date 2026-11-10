<?php

namespace app\agent\controller;

use app\common\model\UserModel;
use think\Controller;
use org\Verify;
use think\Db;

class Login extends Controller {

    // 登录页面
    public function index() {
        $this->assign('langs', getLangs(1));
        return $this->fetch('/login');
    }

    // 登录操作
    public function doLogin() {
        $userName = input("param.user_name");
        $password = input("param.password");
        $code = input("param.code");
//        $authorize = input("param.authorize");

        $result = $this->validate(compact('userName', 'password', "code"), 'AdminValidate');
        if (true !== $result) {
            return json(msg(-1, '', lang($result)));
        }
//        $authorize1 = Db::name('setting')->where('name','authorize')->value('value');
//        if ($authorize != $authorize1){
//            return json(msg(-1, '', "授权码有误"));
//        }

        $verify = new Verify();
        if (!$verify->check($code, 'agent')) {
            return json(msg(-2, '', lang('验证码错误')));
        }

        $userModel = new UserModel();
        $hasUser = $userModel->checkUser($userName, $password);

        if (empty($hasUser)) {
            $hasUser = $userModel->checkUser2($userName, $password);
        }
        if (empty($hasUser)) {
            return json(msg(-3, '', '账号或密码错误'));
        }
        // 验证账号状态
        if (2 == $hasUser['status']) {
            return msg(-5, '', lang('该账号被禁用'));
        }
        if (isset($hasUser['delete_time']) && $hasUser['delete_time']) {
            return msg(-5, '', lang('该账号被禁用'));
        }

        session('username', $hasUser['mobile'], 'agent');
        session('userid', $hasUser['id'], 'agent');
        return json(msg(1, url('index/index'), '登录成功'));
    }

    // 验证码
    public function checkVerify() {
        $verify = new Verify();
        $verify->imageH = 32;
        $verify->imageW = 100;
        $verify->length = 4;
        $verify->useNoise = false;
        $verify->fontSize = 14;
        return $verify->entry('agent');
    }

    // 退出操作
    public function loginOut() {
        session('username', null, 'agent');
        session('id', null, 'agent');
        $this->redirect(url('index'));
    }

    public function check_lang() {
        $lang = $this->request->param('lang/s');
        $allow_lang = \app\common\model\LangModel::column('lang');
        if (in_array($lang, $allow_lang)) {
            cookie('think_var', $lang);
        }
        return $this->success('ok');
    }

}
