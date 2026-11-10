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

use app\admin\model\WithdrawModel;
use think\Db;

class Withdraw extends Base {

    //用户列表
    public function index() {
        $status = config('withdrawals_status');
        $type = config('my.withdraw_type');
        if ($this->request->isAjax() || $this->request->isPost()) {
            $export = $this->request->param("export/d", 0);
            $return = $this->getdata(1, $export === 1);
            if ($export) {
                $header = [
                    "id" => "ID",
                    "recharge" => "充值总额",
                    "mobile" => "手机号",
                    "bank_name" => "银行名称",
                    "bank_number" => "银行账号",
                    "true_name" => "真实姓名",
                    "money" => "提现金额",
                    "taxfee" => "手续费",
                    "status_text" => "状态",
                    "create_time" => "时间",
                    "remark" => "备注",
                ];
                return genXlsx($header, $return, "银行卡提现记录");
            }
            return json($return);
        }
        $vuser = $this->request->param("vuser/d", 0);
        $where['w.remark']=['<>',''];
        if($vuser){
            $where['u.is_xuni']=$vuser;
        }else{
            $where['u.is_xuni']=["<>",1];
        }
        $remark = Db::name('withdrawals')->alias('w')
                ->join('home_user u', 'u.id=w.user_id')
                ->where($where)->distinct(true)->column("w.remark");
        
        $this->assign([
            'remark' => array_filter($remark),
            'status' => $status,
            'type' => $type
        ]);
        $this->assign('pay', config('pay'));
        return $this->fetch();
    }
    //USDT列表
    public function usdt() {
        $status = config('withdrawals_status');
        $type = config('my.withdraw_type');
        if ($this->request->isAjax() || $this->request->isPost()) {
            $export = $this->request->param("export/d", 0);
            $return = $this->getdata(4, $export === 1);
            if ($export) {
                $header = [
                    "id" => "ID",
                    "recharge" => "充值总额",
                    "mobile" => "手机号",
                    "bank_number" => "USDT地址",
                    "true_name" => "真实姓名",
                    "money" => "提现金额",
                    "taxfee" => "手续费",
                    "status_text" => "状态",
                    "create_time" => "时间",
                    "remark" => "备注",
                ];
                return genXlsx($header, $return, "USDT提现记录");
            }
            return json($return);
        }
        $remark = Db::name('withdrawals')->where("remark", "<>", "")->distinct(true)->column("remark");
        $this->assign([
            'remark' => array_filter($remark),
            'status' => $status,
            'type' => $type
        ]);
        return $this->fetch();
    }
    
    private function getdata($type, $export = false) {
        $status = config('withdrawals_status');
        $param = input('param.');
        $param['pageSize'] = !isset($param['pageSize']) ? 10 : intval($param['pageSize']);
        $param['pageNumber'] = !isset($param['pageNumber']) ? 1 : intval($param['pageNumber']);
        $limit = $param['pageSize'];
        $offset = ($param['pageNumber'] - 1) * $limit;
        $where = ['type' => $type];
        $uid = $this->request->param("uid/d", 0);
        if ($uid > 0) {
            $where['user_id'] = $uid;
        }
        $is_xuni = $param['vuser'];
        if (in_array($is_xuni, [0,1])) {
            $where['hu.is_xuni'] = $is_xuni;
        }elseif($is_xuni == 2) { //冻结用户
            $where['hu.status'] = 2;
        }elseif($is_xuni == 3) { //未冻结用户
            $where['hu.status'] = 1;
        }
        if (!empty($param['searchText'])) {
            $where['hu.mobile|hu.true_name|w.order_id'] = ['like', '%' . $param['searchText'] . '%'];
        }
        $where['hu.tree_id'] = ['like', "%/{$this->userid}/%"];
        $where['w.status'] = ['>', -2];
        if ($param['searchStatus'] != '') {
            $where['w.status'] = $param['searchStatus'];
        }
        if (isset($param['channel']) && $param['channel'] != '') {
            $where['w.channel'] = $param['channel'];
        }
        if (!empty($param['startTime']) && !empty($param['endTime'])) {
            $start_time = strtotime($param['startTime'] . " 00:00:00");
            $end_time   = strtotime($param['endTime'] . " 23:59:59");
            $where['w.create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        if (!empty($param['startTime']) && empty($param['endTime'])) {
            $start_time = strtotime($param['startTime'] . " 00:00:00");
             $where['w.create_time'] = ['>=', $start_time];
        }
        if (!empty($param['endTime']) && empty($param['startTime'])) {
            $end_time   = strtotime($param['endTime'] . " 23:59:59");
             $where['w.create_time'] = ['<=', $end_time];
        }
        $orderby = 'status ASC, id DESC';
        if (!empty($param['sortName']) && in_array($param['sortName'], ['money', 'create_time', 'check_time'])) {
            $orderby = "w." . $param['sortName'] . " " . ($param['sortOrder'] == "asc" ? "ASC" : "DESC");
        }
        
        //重复IP用户
        $repeat_flag = $this->request->param('repeatIP/d', 0);
        if($repeat_flag == '1'){
            $where['hu.last_login_ip'] = ['in', function($query){
                $query->name('home_user')
                    ->whereNotIn('last_login_ip', ['', '0.0.0.0'])
                    ->group('last_login_ip')
                    ->having('count(1) > 1')
                    ->field('last_login_ip');
            }];
            $orderby = 'last_login_ip DESC,id DESC';
        }

        $medol = new WithdrawModel();
        if ($export) {
            $selectResult = $medol->getAllByWhere($where, $orderby);
        }else{
            $selectResult = $medol->getLlistsByWhere($where, $offset, $limit, $orderby);
        }
        $pay = config('pay');
        $recharge = Db::name('recharge')->where("status", 2)->group("user_id")->column("sum(money)", "user_id");
        // 拼装参数
        foreach ($selectResult as $key => $vo) {
            if ($vo['status'] == 1) {
                $buttons = showOperate($this->makeButton($vo['id']));
            } else if ($vo['status'] == 3) {
                $buttons = showOperate([
                    '删除' => [
                        'auth' => 'withdraw/withdrawdel',
                        'href' => "javascript:withdrawDel(" . $vo['id'] . ")",
                        'btnStyle' => 'danger',
                        'icon' => 'fa fa-trash-o'
                    ]
                ]);
            } else {
                $buttons = '-';
            }
            if ($vo['type'] == 4) {
                $selectResult[$key]['usdt_money'] = fiatToUsdt($vo['money'], $vo['exchange_rate']);
            }
//                $selectResult[$key]['true_name'] = $vo['true_name'] . '(ID:' . $vo['user_id'] . ')';
            $selectResult[$key]['check_time'] = $vo['check_time'] > 0 ? date('Y-m-d H:i:s', $vo['check_time']) : '-';
            $selectResult[$key]['status_text'] = $status[$vo['status']];
            $selectResult[$key]['operate'] = $buttons;
            $selectResult[$key]['last_login_ip']     = $vo['last_login_ip'] . "<br>" . ip2location($vo['last_login_ip']);
            if ($vo['ip']) {
                $selectResult[$key]['ip']     = $vo['ip'] . "<br>" . ip2location($vo['ip']);
            }
            $selectResult[$key]['user_info'] = "手机号:{$vo['mobile']}<br>银行名称:{$vo['bank_name']}<br>银行账户:{$vo['bank_number']}<br>真实姓名:{$vo['true_name']}";
            if (isset($vo['bank_kh']) && !empty($vo['bank_kh'])) {
                $selectResult[$key]['user_info'] .= "<br>IFSC:{$vo['bank_kh']}";
            }
            if (isset($vo['trade_no']) && !empty($vo['trade_no'])) {
                $selectResult[$key]['order_id'] .= "<hr>{$vo['trade_no']}";
            }
            $selectResult[$key]['user_info2'] = "手机号:{$vo['mobile']}<br>支付宝:{$vo['bank_number']}<br>真实姓名:{$vo['true_name']}";
            $selectResult[$key]['user_info3'] = "手机号:{$vo['mobile']}<br>USDT地址:{$vo['bank_number']}<br>地址类型:{$vo['bank_name']}<br>";
            $selectResult[$key]['user_info3'] .= show_img("https://api.qrserver.com/v1/create-qr-code/?data={$vo['bank_number']}&size=200x200&ecc=M");
            $selectResult[$key]['recharge'] = isset($recharge[$vo['user_id']]) ? intval($recharge[$vo['user_id']]) : 0;
            $selectResult[$key]['channel']     = isset($pay[$vo['channel']]) ? $pay[$vo['channel']]['name'] : $vo['channel'];
        }
        
        if ($export) {
            return $selectResult;
        }
        $return['total'] = $medol->getAllLists($where);  //总数据
        $return['rows'] = $selectResult;
        
        $model = new WithdrawModel();
        $stat = $model->alias('w')
                ->join('home_user hu', 'w.user_id = hu.id', 'left')
                ->where($where)->group('status')->field('w.status,sum(w.money) AS total')->select();
        if ($stat) {
            foreach ($stat as $k => $v) {
                $stat[$k]['text'] = $status[$v['status']];
                $stat[$k]['total'] = i18n_sprintf($v['total']);
            }
        }
        $return['stat']  = $stat;
        return $return;
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id) {
        return [];
    }

}
