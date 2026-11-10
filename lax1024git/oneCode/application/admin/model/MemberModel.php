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
use traits\model\SoftDelete;

class MemberModel extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    // 确定链接表名
    protected $name = 'home_user';

    /**
     * 根据搜索条件获取用户列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getUsersByWhere($where, $offset, $limit, $orderby = 'id DESC')
    {
        $lists = $this->withTrashed()
                ->where($where)->limit($offset, $limit)->order($orderby)->select();
        foreach ($lists as $k => $val) {
            $lists[$k] = $val;
        }
        return $lists;
    }
    /**
     * 根据搜索条件获取用户列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getAllUsersByWhere($where, $orderby = 'id DESC')
    {
        $lists = $this->withTrashed()->where($where)->order($orderby)->select();
        return $lists;
    }

    /**
     * 根据搜索条件获取所有的用户数量
     * @param $where
     */
    public function getAllUsers($where)
    {
        return $this->alias('user')->where($where)->count();
    }

    /**
     * 插入管理员信息
     * @param $param
     */
    public function insertUser($param)
    {
        try{
            $param['user_money'] = 0;
            $param['tyj_money'] = 0;
            $param['frozen_money'] = 0;
            $param['total_money'] = 0;
            $result =  $this->validate('MemberValidate')->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{
                return msg(1, url('member/index'), '添加用户成功');
            }
        }catch(PDOException $e){

            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑管理员信息
     * @param $param
     */
    public function editUser($param)
    {
        try{
            $result =  $this->validate('MemberValidate')->save($param, ['id' => $param['id']]);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{
                return msg(1, url('member/index'), '编辑用户成功');
            }
        }catch(PDOException $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据管理员id获取角色信息
     * @param $id
     */
    public function getOneUser($id, $field = "*")
    {
        $info = $this->withTrashed()->where('id', $id)->field($field)->find();
        return !empty($info) && isset($info[$field]) ? $info[$field] : $info;
    }

    /**
     * 删除管理员
     * @param $id
     */
    public function delUser($id)
    {
        try{
            $this->where('id', $id)->delete();
            return msg(1, '', '删除用户成功');
        }catch( PDOException $e){
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 根据用户名获取管理员信息
     * @param $name
     */
    public function findUserByName($name)
    {
        return $this->where('nick_name', $name)->find();
    }

    /**
     * 更新管理员状态
     * @param array $param
     */
    public function updateStatus($param = [], $uid)
    {
        try{
            $this->where('id', $uid)->update($param);
            return msg(1, '', 'ok');
        }catch (\Exception $e){

            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 根据手机检测用户数据
     * @param $mobile
     */
    public function checkUser($mobile)
    {
        return $this->alias('u')
                ->field('u.id, u.nick_name, u.avatar, u.paypwd, u.status, u.last_login_time')
                ->where('u.mobile', $mobile)
                ->find();
    }
    
    public function parent()
    {
        return $this->hasOne('MemberModel', 'id', 'leader1');
    }
    
    public function cp()
    {
        return $this->hasOne('MemberModel', 'id', 'cp_id');
    }
}
