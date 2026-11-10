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

use app\admin\model\RechargeModel;
use think\Db;

class Recharge extends Base
{
    //用户列表
    public function index()
    {
        $status = config('recharge_status');
        $type = config('my.pay_type');
        if ($this->request->isAjax() || $this->request->isPost()) {
            $export = $this->request->param("export/d", 0);
            $return = $this->getdata(1, $export === 1);
            if ($export) {
                $header = [
                    "id" => "ID",
                    "car_num" => "收款账号",
                    "true_name" => "真实姓名",
                    "mobile" => "联系电话",
                    "user_note" => "付款类型",
                    "pay_account" => "付款账户",
                    "money" => "金额",
                    "status_text" => "状态",
                    "create_time" => "时间",
                ];
                return genXlsx($header, $return, "银行卡充值记录");
            }
            return json($return);
        }
        $this->assign([
            'status' => $status,
            'type' => $type
        ]);
        return $this->fetch();
    }

    public function usdt() {
        $status = config('recharge_status');
        $type = config('my.pay_type');
        if ($this->request->isAjax() || $this->request->isPost()) {
            $export = $this->request->param("export/d", 0);
            $return = $this->getdata(4, $export === 1);
            if ($export) {
                $header = [
                    "id" => "ID",
                    "car_num" => "收款账号",
                    "mobile" => "联系电话",
                    "user_note" => "付款类型",
                    "pay_account" => "付款账户",
                    "money" => "金额",
                    "usdt_money" => "USDT金额",
                    "status_text" => "状态",
                    "create_time" => "时间",
                ];
                return genXlsx($header, $return, "USDT充值记录");
            }
            return json($return);
        }
        $this->assign([
            'status' => $status,
            'type' => $type
        ]);
        return $this->fetch();
    }

    public function third()
    {
        $status = config('recharge_status');
        $type = config('my.pay_type');
        if ($this->request->isAjax() || $this->request->isPost()) {
            $export = $this->request->param("export/d", 0);
            $return = $this->getdata(3, $export === 1);
            if ($export) {
                $header = [
                    "id" => "ID",
                    "order_id" => "订单号",
                    "channel" => "第三方渠道",
                    "bank_name" => "银行",
                    "mobile" => "联系电话",
                    "money" => "金额",
                    "status_text" => "状态",
                    "create_time" => "时间",
                ];
                return genXlsx($header, $return, "第三方充值记录");
            }
            return json($return);
        }
        $remark = Db::name('recharge')->where("remark", "<>", "")->distinct(true)->column("remark");
        $this->assign([
            'remark' => array_filter($remark),
            'status' => $status,
            'type' => $type
        ]);
        return $this->fetch();
    }

    private function getdata($type, $export = 0) {
        $status_text = config('recharge_status');
        $type_text   = config('my.pay_type');
        $param  = input('param.');
        if (!isset($param['pageNumber'])) {
            $param['pageNumber'] = 1;
        }
        if (!isset($param['pageSize'])) {
            $param['pageSize'] = 10;
        }
        $limit  = $param['pageSize'];
        $offset = ($param['pageNumber'] - 1) * $limit;
        $where  = ['type' => $type];
        $uid = $this->request->param("uid/d", 0);
        if ($uid > 0) {
            $where['user_id'] = $uid;
        }
        if (!empty($param['searchText'])) {
            $where['hu.mobile|hu.true_name|w.order_id'] = ['like', '%' . $param['searchText'] . '%'];
        }
        $where['hu.tree_id'] = ['like', "%/{$this->userid}/%"];
        $where['w.status'] = ['>', -2];
        if($param['searchStatus'] != '') {
            $where['w.status'] = $param['searchStatus'];
        }
        $orderby = 'id DESC';
        if (!empty($param['sortName']) && in_array($param['sortName'], ['money', 'create_time'])) {
            $orderby = "w." . $param['sortName'] . " " . ($param['sortOrder'] == "asc" ? "ASC" : "DESC");
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
        $medol = new RechargeModel();
        if ($export) {
            $selectResult = $medol->getAllByWhere($where, $orderby);
        }else{
            $selectResult = $medol->getLlistsByWhere($where, $offset, $limit, $orderby);
        }
        if ($type == 3) {
            $paylist = \app\admin\model\PaymentModel::where('type', 3)->column('*', 'id');
            $pay = config('pay');
        }
        // 拼装参数
        foreach($selectResult as $key=>$vo){
            if($vo['status'] == 1){
                $buttons = showOperate($this->makeButton($vo['id']));
            } else {
                $buttons = '暂无';
            }
//            $selectResult[$key]['create_time'] = date('Y-m-d H:i:s', $vo['create_time']);
            $selectResult[$key]['type']        = $type_text[$vo['type']];
            $selectResult[$key]['money']        = sprintf4f($vo['money']);
            $selectResult[$key]['status_text']      = $status_text[$vo['status']];
            $selectResult[$key]['operate']     = $buttons;
            $selectResult[$key]['img'] = show_img(toMedia($vo['img']));
            $selectResult[$key]['last_login_ip']     = $vo['last_login_ip'] . "<br>" . ip2location($vo['last_login_ip']);
            
            if ($type == 3) {
                $selectResult[$key]['channel']     = $pay[$paylist[$vo['pay_id']]['account']]['name'];
                $selectResult[$key]['bank_name']     = $paylist[$vo['pay_id']]['bank_name'];
            }
        }
        if ($export) {
            return $selectResult;
        }
        $return['total'] = $medol->getAllLists($where);  //总数据
        $return['rows']  = $selectResult;
        
        $model = new RechargeModel();
        $stat = $model->alias('w')
                ->join('home_user hu', 'w.user_id = hu.id', 'left')
                ->where($where)->group('status')->field('w.status,sum(w.money) AS total')->select();
        if ($stat) {
            foreach ($stat as $k => $v) {
                $stat[$k]['text'] = $status_text[$v['status']];
                $stat[$k]['total'] = sprintf4f($v['total']);
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
    private function makeButton($id)
    {
        return [];
    }
}
