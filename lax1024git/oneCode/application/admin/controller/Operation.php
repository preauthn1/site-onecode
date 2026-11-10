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

use app\admin\model\OperationModel;

class Operation extends Base {
    public function index() {
        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $searchText = $param['searchText'];
            $where = [];
            if ($searchText) {
                $where['username|action|content'] = ['LIKE', "%{$searchText}%"];
            }
            $selectResult = OperationModel::where($where)->limit($offset, $limit)->order('id DESC')->select();
            // 拼装参数
            foreach ($selectResult as $key => $vo) {
            }
            $return['total'] = OperationModel::where($where)->count();  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
        return $this->fetch();
    }
}
