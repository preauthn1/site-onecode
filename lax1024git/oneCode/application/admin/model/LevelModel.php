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

namespace app\admin\model;

use think\Model;

class LevelModel extends Model {

    // 确定链接表名
    protected $name = 'home_user_level';

    /**
     * 根据搜索条件获取商家列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getLevelByWhere($where, $offset, $limit) {
        return $this->where($where)->limit($offset, $limit)->order('sort desc')->select();
    }

    /**
     * 根据搜索条件获取所有的商家数量
     * @param $where
     */
    public function getAllLevel($where) {
        return $this->alias('user')->where($where)->count();
    }

    /**
     * 插入管理员信息
     * @param $param
     */
    public function insertLevel($param) {
        try {
            $result = $this->validate('LevelValidate')->save($param);
            if (false === $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            } else {
                insert_operation_log('会员等级:增加', $param);
                return msg(1, url('level/index'), '添加等级成功');
            }
        } catch (PDOException $e) {

            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑管理员信息
     * @param $param
     */
    public function editLevel($param, $id) {
        try {
            $result = $this->where(['level_id' => $id])->update($param);
            if (false === $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            } else {
                insert_operation_log('会员等级:修改', $param);
                return msg(1, url('level/index'), '编辑等级成功');
            }
        } catch (\think\Exception $e) {
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据管理员id获取角色信息
     * @param $id
     */
    public function getOneLevel($id) {
        return $this->where('level_id', $id)->find();
    }

    /**
     * 删除管理员
     * @param $id
     */
    public function delLevel($id) {
        try {

            $this->where('level_id', $id)->delete();
            return msg(1, '', '删除等级成功');
        } catch (PDOException $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 更新管理员状态
     * @param array $param
     */
    public function updateStatus($param = [], $uid) {
        try {
            $this->where('level_id', $uid)->update($param);
            return msg(1, '', 'ok');
        } catch (\Exception $e) {

            return msg(-1, '', $e->getMessage());
        }
    }

    // 获取所有分类
    public function getLevel() {
        return $this->select();
    }

    public function setSettingAttr($val) {
        return empty($val) ? null : serialize($val);
    }

    public function getSettingAttr($val) {
        return empty($val) ? [] : unserialize($val);
    }

}
