<?php
namespace app\admin\controller;
use app\admin\model\LevelModel;
use app\admin\model\PrizeLogModel;
use app\admin\model\PrizeModel;
use think\Db;

/**
 * Class Turntable
 * 转盘
 * @package app\admin\controller
 */
class Turntable extends Base
{
    /**
     * 奖品列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function prizeList(){
        if(request()->isAjax()){
            $prize = new PrizeModel();
            $param  = input('param.');
            $limit  = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where  = [];
            if (!empty($param['searchText'])) {
                $where['name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $selectResult = $prize->getPrizeByWhere($where, $offset, $limit);
            $status       = config('status');
            $prize_type      = config('prize_type');
            // 拼装参数
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['status']  = $status[$vo['status']];
                $selectResult[$key]['value']  = $this->prizeTypeValue($vo['type'], $vo['value']);
                $selectResult[$key]['type']  = $prize_type[$vo['type']];
                $selectResult[$key]['img']  = $vo['img'] ? '<img src="' . toMedia($vo['img']) . '" width="40px" height="40px">' : '';
                $selectResult[$key]['num']  = $vo['num'] == '-1' ? '无限量': $vo['num'];
                $selectResult[$key]['probability']  = $vo['probability'] . ' (' . round($vo['probability'] / 10000, 8) . '%)';
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }
            $return['total'] = $prize->getAllPrize($where);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
        return $this->fetch();
    }
    
    public function countlist(){
        $act = $this->request->param('act/s');
        if (request()->isAjax()) {
            $param = $this->request->param();
            if ($act == 'add') {
                $user_id = $param['user_id'];
                $remark = $param['remark'];
                $num = $param['num'];
                $user = \app\admin\model\MemberModel::where('id', $user_id)->find();
                if (!$user) {
                    $user = \app\admin\model\MemberModel::where('mobile', $user_id)->find();
                }
                if (!$user) {
                    return $this->error('用户信息不存在');
                }
                if ($num <= 0) {
                    return $this->error('抽奖次数不能小于1');
                }
                Db::name('turntable_count')->insert([
                    "activity_id" => 0,
                    "user_id" => $user->id,
                    "from_user_id" => 0,
                    "remark" => $remark,
                    "num" => $num,
                    "total" => $num,
                    "create_time" => time(),
                    "update_time" => time(),
                ]);
                insert_operation_log('添加抽奖次数', "用户ID:{$user->id},次数:{$num},{$remark}");
                return $this->success('添加抽奖次数成功', url('countlist'));
            }
            $limit = $param['pageSize'];
            $searchText = $param['title'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if ($searchText) {
                $where['user_id|remark'] = $searchText;
            }
            $model = Db::name('turntable_count');
            $result = $model->where($where)->limit($offset, $limit)->order('id desc')->select();
            $total = $model->where($where)->limit($offset, $limit)->count();
            foreach ($result as $key => $vo) {
                $result[$key]['create_time'] = timeToStr($vo['create_time']);
                $result[$key]['update_time'] = timeToStr($vo['update_time']);
            }
            $return['total'] = $total;  //总数据
            $return['rows'] = $result;
            return json($return);
        }
        if ($act == 'add') {
            return $this->fetch('add');
        }
        return $this->fetch();
    }

    /**
     * 新增奖品
     * @return mixed|\think\response\Json
     */
    public function prizeAdd(){
        if(request()->isPost()){
            $param = input('post.');
            $prize = new PrizeModel();
            $flag = $prize->insertPrize($param);
            return json(msg($flag['code'], $flag['data'], lang($flag['msg'])));
        }
        $this->assign('prize_type', config('prize_type'));
        $this->assign('status', config('status'));
        return $this->fetch();
    }

    /**
     * 编辑奖品
     * @return mixed|\think\response\Json
     */
    public function prizeEdit(){
        $prize = new PrizeModel();
        if(request()->isPost()){
            $param = input('post.');
            $flag = $prize->editPrize($param);
            return json(msg($flag['code'], $flag['data'], lang($flag['msg'])));
        }
        $id = input('param.id');
        $this->assign([
            'prize'  => $prize->findPrize($id),
            'prize_type' => config('prize_type'),
            'status' => config('status'),
        ]);
        return $this->fetch();
    }

    /**
     * 中奖记录
     * @return mixed|\think\response\Json
     */
    public function awardList(){

        $prize_type      = config('prize_type');
        if(request()->isAjax() || $this->request->isPost()){
            $prizeLog = new PrizeLogModel();
            $param  = input('param.');
            $export = $this->request->param("export/d", 0);

            $limit  = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where  = [];
            if (!empty($param['searchText'])) {
                $where['p.name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            if (!empty($param['mobile'])) {
                $where['hu.mobile'] = ['like', '%' . $param['mobile'] . '%'];
            }
            if(isset($param['userType']) && $param['userType'] >= 0){
                $where['hu.is_xuni'] = $param['userType'];
            }
            if(isset($param['type'])  && $param['type'] >= 0){
                $where['type'] = $param['type'];
            }
            if (!empty($param['startTime']) && !empty($param['endTime'])) {
                $start_time = strtotime($param['startTime'] . " 00:00:00");
                $end_time   = strtotime($param['endTime'] . " 23:59:59");
                $where['p.create_time'] = ['BETWEEN', [$start_time, $end_time]];
            }
            if($export){
                $offset = 0;
                $limit = 1000000; //最多一百万条，再多xlx也打不开
            }
            $selectResult = $prizeLog->getPrizeLogByWhere($where, $offset, $limit);
            // 拼装参数
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['value']  = $this->prizeTypeValue($vo['type'], $vo['value']);
                $selectResult[$key]['money']  = i18n_currency($vo['money']);
                $selectResult[$key]['type']  = $prize_type[$vo['type']];
                $selectResult[$key]['user_type']  = $vo['is_xuni'] ? '是': '不是';
                $selectResult[$key]['num']  = $vo['num'] == '-1' ? '无限量': $vo['num'];
                $selectResult[$key]['probability']  = $vo['probability'] . ' (' . round($vo['probability'] / 10000, 8) . '%)';
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }
            $return['total'] = $prizeLog->getAllPrizeLog($where);  //总数据
            $return['rows'] = $selectResult;
            if($export){
                $header = [
                    "id" => "记录ID",
                    "nick_name" => "昵称",
                    "mobile" => "手机号",
                    "user_type" => "虚拟账号",
                    "money" => "抽奖支付金额",
                    "name" => "奖品名称",
                    "num" => "奖品库存",
                    "type" => "奖品类型",
                    "value" => "奖品类型对应数值",
                    "probability" => "中奖几率",
                    "create_time" => "抽奖时间",
                ];
                return genCsv($header, $return['rows'], "中奖记录");
            }
            $return['total_data']['total_money'] = $prizeLog->whereSql($where)->sum('money');  //总数据
            if(isset($param['type']) && ($param['type'] == 0 || $param['type'] == -1) ){
                $return['total_data']['total_red'] = $prizeLog->whereSql(array_merge($where,['type' => 0]))->sum('value');  //红包
            }
            if(isset($param['type']) && ($param['type'] == 1 || $param['type'] == -1) ) {
                $return['total_data']['total_grab'] = $prizeLog->whereSql(array_merge($where, ['type' => 1]))->sum('value');  //抢单
            }
            if(isset($param['type']) && ($param['type'] == 2 || $param['type'] == -1) ) {
                $return['total_data']['total_entity'] = $prizeLog->whereSql(array_merge($where, ['type' => 2]))->count();  //实物
            }
            if(isset($param['type']) && ($param['type'] == 3 || $param['type'] == -1) ) {
                $return['total_data']['total_thank'] = $prizeLog->whereSql(array_merge($where, ['type' => 3]))->count();  //谢谢参与
            }
            return json($return);
        }
        $this->assign('prize_type', $prize_type);
        return $this->fetch();
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
            '编辑' => [
                'auth' => 'turntable/prizeEdit',
                'href' => url('turntable/prizeEdit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            /*'删除' => [
                'auth' => 'level/leveldel',
                'href' => "javascript:levelDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]*/
        ];
    }

    private function prizeTypeValue($type, $value = ''){
        if($type == 0){
            return '返现:' . $value;
        }else if($type == 1){
            return '增加抢单次数:' . $value;
        }
        if(empty($value)){
            return '无';
        }
        return $value;
    }
}
