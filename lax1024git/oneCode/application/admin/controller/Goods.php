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

use app\common\model\GoodsModel AS Model;
use app\common\model\GoodsAttrModel;
use app\common\model\CateModel;
use app\services\CommonService;
use app\common\model\GoodsCommentModel;

class Goods extends Base {

    public function index() {
        $is_discount = ['0' => '否', '1' => '是'];
        if (request()->isAjax()) {
            $param = $this->request->param();
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (isset($param['cateId']) && $param['cateId'] != '') {
                $where['cate_id'] = $param['cateId'];
            }
            if (isset($param['is_discount']) && $param['is_discount'] != '-1') {
                $where['is_discount'] = $param['is_discount'];
            }
            if (isset($param['name']) && $param['name'] != '') {
                $where['goods_name'] = ['LIKE', "%{$param['name']}%"];
            }
//            print_r($where);exit;
            $model = new Model();
            $result = $model->where($where)->limit($offset, $limit)->order('sort DESC')->select();
            $total = $model->where($where)->limit($offset, $limit)->count();
            $status = config('status');
            $cates = CateModel::column('name', 'id');
            foreach ($result as $key => $vo) {
                $result[$key]['operate'] = showOperate($this->makeButton($vo['goods_id']));
                $result[$key]['status']      = $status[$vo['status']];
                $result[$key]['is_discount']      = $is_discount[$vo['is_discount']];
                $result[$key]['cate_name']      = isset($cates[$vo['cate_id']]) ? $cates[$vo['cate_id']] : '-';
                $result[$key]['pic'] = $vo['pic'] ? '<img src="' . toMedia($vo['pic']) . '" width="40px" height="40px">' : '-';
            }
            $return['total'] = $total;  //总数据
            $return['rows'] = $result;
            return json($return);
        }
        $this->assign('is_discount', $is_discount);
        $this->assign('cates', CateModel::all());
        return $this->fetch();
    }

    public function add() {
        $goods_id = $this->request->param("goods_id/d");
        if (request()->isPost()) {
            $param = $this->request->param();
            $attr = $param['attr'];
            unset($param['attr']);
            $model = new Model;
            if ($goods_id > 0) {
                $result = $model->isUpdate()->save($param);
            } else {
                $result = $model->allowField(true)->save($param);
            }
            if (false === $result) {
                $this->error($model->getError());
            } else {
//                insert_operation_log('商品:' . ($goods_id > 0 ? '修改' : '添加'), $param);
            }
            $attr_datas = [];
            if (isArrayAndNotEmpty($attr)) {
                foreach ($attr['name'] as $key => $val) {
                    $row = array_filter([
                        'id' => $attr['id'][$key],
                        'name' => $attr['name'][$key],
                        'shop_price' => $attr['shop_price'][$key],
                        'retail_price' => $attr['retail_price'][$key],
                        'return_bonus' => $attr['return_bonus'][$key],
                    ]);
                    if (count($row) < 4) {
                        break;
                    }
                    if ($row['id'] > 0) {
                        GoodsAttrModel::update($row);
                    }else{
                        $attr_datas[] = $row;
                    }
                }
            }
            if (isArrayAndNotEmpty($attr_datas)) {
                $model->attr()->saveAll($attr_datas);
            }
            $url = url('index');
            $this->success(lang('提交成功'), $url);
        }
        $info = Model::with('attr')->where('goods_id', $goods_id)->find();
        if (empty($info)) {
            $info = [
                'is_discount' => 0,
                'sort' => 0,
                'cate_id' => 0,
                'goods_id' => 0,
                'order_number' => 2,
                'status' => 1,
                'goods_name' => '',
                'shop_price' => '',
                'retail_price' => '',
                'return_bonus' => '',
                'pic' => '',
                'main_pic' => '',
                'content' => '',
                'attr' => [],
                'main_pic_arr' => []
            ];
        }else{
            $info['main_pic_arr'] = explode(",", $info['main_pic']);
        }
        $config = getConfig();
        $pd_part = CommonService::getGoodsPricePartConfig();
        $this->assign('cates', CateModel::all());
        $this->assign("status", config('status'));
        $this->assign("config", $config);
        $this->assign("pd_part", json_encode($pd_part));
        $this->assign("info", $info);
        return $this->fetch();
    }

    public function del() {
        $id = $this->request->param('id/d');
        $info = Model::get($id);
        if ($info) {
            $info->delete();
        }
        $this->success(lang('删除成功'), url('index'));
    }
    
    public function delattr() {
        $id = $this->request->param('id/d');
        $info = GoodsAttrModel::get($id);
        if ($info) {
            $info->delete();
        }
        $this->success(lang('删除成功'), url('index'));
    }
    
    /**
     * 随机生成商品评论
     */
    public function rand() {
        $number = $this->request->param('number/d', 1);
        $ids = $this->request->param('id/a');
        if (empty($ids)) {
            $this->error(lang('请选择操作的商品'));
        }
        foreach ($ids as $goods_id) {
            for($i = 0; $i < $number; $i++) {
                CommonService::generateGoodsComment($goods_id);
            }
        }
        $this->success(lang('操作成功'));
    }

    public function comment() {
        $goods_id = $this->request->param('goods_id/d');
        if (request()->isAjax()) {
            $param = $this->request->param();
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (isset($param['text']) && $param['text'] != '') {
                $where['text'] = ['LIKE', "%{$param['text']}%"];
            }
            $model = new GoodsCommentModel();
            $result = $model->where($where)->limit($offset, $limit)->order('id DESC')->select();
            $total = $model->where($where)->limit($offset, $limit)->count();
            foreach ($result as $key => $vo) {
                $result[$key]['operate'] = showOperate([
                    '删除' => [
                        'auth' => 'goods/delcomment',
                        'href' => url("goods/delcomment", ['id' => $vo['id']]),
                        'btnStyle' => 'danger',
                        'icon' => 'fa fa-trash-o'
                    ]
                ]);
            }
            $return['total'] = $total;  //总数据
            $return['rows'] = $result;
            return json($return);
        }
        $this->assign([
            'title' => lang('商品评论'),
            'goods_id' => $goods_id
        ]);
        return $this->fetch();
    }
    
    public function delcomment() {
        $id = $this->request->param('id/d');
        $info = GoodsCommentModel::get($id);
        if ($info) {
            $info->delete();
        }
        $this->success(lang('删除成功'));
    }
    
    // 变更商品状态
    public function checkStatus($id, $status, $type = 0) {
        $goods = new Model();
        if ($type == 1) {
            $ids = implode(',', $id);
            $ret = $goods->updateStatus(['status' => $status], ['goods_id' => ['in', $ids]]);
        } else {
            $ret = $goods->updateStatus(['status' => $status], ['goods_id' => $id]);
        }
        return json($ret);
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id) {
        return [
            '评论' => [
                'auth' => 'goods/comment',
                'href' => url('goods/comment', ['goods_id' => $id]),
                'btnStyle' => 'info',
                'icon' => 'fa fa-comment'
            ],
            '编辑' => [
                'auth' => 'goods/add',
                'href' => url('goods/add', ['goods_id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'goods/del',
                'href' => "javascript:del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

}
