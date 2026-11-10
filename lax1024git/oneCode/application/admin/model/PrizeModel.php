<?php

namespace app\admin\model;

use think\Model;

class PrizeModel extends Model {
    // 确定链接表名
    protected $name = 'turntable_prize';

    public function getPrizeByWhere($where, $offset, $limit) {
        return $this->where($where)->limit($offset, $limit)->order('id DESC')->select();
    }

    public function getAllPrize($where) {
        return $this->where($where)->count();
    }

    public function insertPrize($param) {
        try {
            $result = $this->validate('PrizeValidate')->save($param);
            if (false === $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            } else {
                insert_operation_log('转盘奖品:增加', $param);
                return msg(1, url('turntable/prizeList'), '添加奖品成功');
            }
        } catch (PDOException $e) {
            return msg(-2, '', $e->getMessage());
        }
    }

    public function editPrize($param) {
        try {
            $result = $this->validate('PrizeValidate')->save($param, ['id' => $param['id']]);
            if (false === $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            } else {
                $redis = new \think\Cache();
                $redis_key = "turntable_prize{$param['id']}";
                $redis->rm($redis_key);
                insert_operation_log('转盘奖品:修改', $param);
                return msg(1, url('turntable/prizeList'), '编辑奖品成功');
            }
        } catch (PDOException $e) {
            return msg(-2, '', $e->getMessage());
        }
    }

    public function findPrize($id) {
        return $this->where('id', $id)->find();
    }

}
