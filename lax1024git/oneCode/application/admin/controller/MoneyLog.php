<?php

namespace app\admin\controller;

use app\admin\model\MoneyModel AS Model;
use app\common\model\UserModel;
use app\admin\model\UserModel AS AdminModel;
use think\Db;
class MoneyLog extends Base {

    public function index() {
        $money_type = config("my.money_type");
        if (request()->isAjax()) {
            $param = $this->request->param();
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            $where_str = [];
            if (!empty($param['username'])) {
                $user_id = UserModel::where('mobile', $param['username'])->value('id');
                $where['user_id'] = $user_id ?? $param['username'];
                $where_str[] = "user_id = '" . ($user_id ?? $param['username']) . "'";
            }
            if (isset($param['userId']) && $param['userId'] !== '') {
                $where['user_id'] = $param['userId'];
                $where_str[] = "user_id = '{$param['userId']}'";
            }
            if ($param['type'] !== '') {
                $where['type'] = $param['type'];
                $where_str[] = "type = '{$param['type']}'";
            }
            if (empty($param['startTime'])) {
                $param['startTime'] = getDateStr();
            }
            if (empty($param['endTime'])) {
                $param['endTime'] = getDateStr();
            }
            if ($param['from_user_id'] !== '') {
                $where['from_user_id'] = $param['from_user_id'];
                $where_str[] = "from_user_id = '{$param['from_user_id']}'";
            }
            if ($param['order_id'] !== '') {
                $where['order_id'] = $param['order_id'];
                $where_str[] = "order_id = '{$param['order_id']}'";
            }
            if (!empty($param['startTime']) && !empty($param['endTime'])) {
                $start_time = strtotime($param['startTime'] . " 00:00:00");
                $end_time   = strtotime($param['endTime'] . " 23:59:59");
                $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
                $where_str[] = "create_time BETWEEN '{$start_time}' AND '{$end_time}'";
            }
            if (!empty($param['startTime']) && empty($param['endTime'])) {
                $start_time = strtotime($param['startTime'] . " 00:00:00");
                $where['create_time'] = ['>=', $start_time];
                $where_str[] = "create_time >= '{$start_time}'";
            }
            if (!empty($param['endTime']) && empty($param['startTime'])) {
                $end_time   = strtotime($param['endTime'] . " 23:59:59");
                $where['create_time'] = ['<=', $end_time];
                $where_str[] = "create_time <= '{$start_time}'";
            }
            $tables = [];
            for ($i = strtotime($param['startTime']); $i <= strtotime($param['endTime']); $i+=86400) {
                $full_table_name = "qd_money_log_" . timeToStr($i, "Ymd");
                if (existTable($full_table_name)) {
                    $tables[] = $full_table_name;
                }
            }
            $field = "id,is_xuni,user_id,admin_id,order_id,goods_id,from_user_id,remarks,money,user_money,type,status,create_time";
            $sqls = [];
            $count_sqls = [];
            $stat_sqls = [];
            for($i=0; $i < count($tables); $i++) {
                $sqls[] = "SELECT {$field} FROM {$tables[$i]} WHERE " . implode(" AND ", $where_str);
                $count_sqls[] = "SELECT count(id) AS total FROM {$tables[$i]} WHERE " . implode(" AND ", $where_str);
                $stat_sqls[] = "SELECT type, sum(money) AS total FROM {$tables[$i]} WHERE " . implode(" AND ", $where_str) . " GROUP BY type";
            }
            
            $db_result = Db::table('qd_money_log');
            if (isArrayAndNotEmpty($sqls)) {
                $db_result = $db_result->union($sqls, true);
            }
            $result = $db_result->field($field)->where($where)
                    ->limit($offset, $limit)->order('create_time desc')->select();
            
            if (isArrayAndNotEmpty($count_sqls)) {
                $count_sql = Db::field("count(id) AS total")
                    ->table('qd_money_log')
                    ->union($count_sqls, true)
                    ->where($where)
                    ->buildSql();
                $total = Db::table($count_sql . " a")->sum("a.total");
            }else{
                $total = Db::table("qd_money_log")->where($where)->count();
            }
            foreach ($result as $key => &$vo) {
                $vo['type'] = $money_type[$vo['type']];
                if ($vo['admin_id'] > 0) {
                    $vo['admin_name'] = AdminModel::where('id', $vo['admin_id'])->value('user_name');
                }else{
                    $vo['admin_name'] = '-';
                }
                $vo['create_time'] = date('Y-m-d H:i:s', $vo['create_time']);
            }
            unset($vo);
            $return['total'] = $total;  //总数据
            $return['rows'] = $result;
            
            if (isArrayAndNotEmpty($stat_sqls)) {
                $stat_sql = Db::field('type, sum(money) AS total')
                    ->table('qd_money_log')
                    ->union($stat_sqls, true)
                    ->where($where)
                    ->group('type')
                    ->buildSql();
                $stat = Db::table($stat_sql . " a")->group('a.type')->field('a.type, sum(a.total) AS total')->select();
            }else{
                $stat = Db::table('qd_money_log')->where($where)->group('type')->field('type, sum(money) AS total')->select();
            }

            if ($stat) {
                foreach ($stat as $k => $v) {
                    $stat[$k]['text'] = $money_type[$v['type']];
                    $stat[$k]['total'] = sprintf4f($v['total']);
                }
            }
            $return['stat'] = $stat;
            return json($return);
        }
        $this->assign('money_type', $money_type);
        return $this->fetch();
    }

}
