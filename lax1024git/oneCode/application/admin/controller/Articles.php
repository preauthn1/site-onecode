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

use app\admin\model\ArticleModel AS Model;

class Articles extends Base {

    // 文章列表
    public function index() {
        $article_type = config("my.article_type");
        $article_navs = config("my.article_navs");
        $langs = getLangsColumn();
        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['searchText'])) {
                $where['title'] = ['like', '%' . $param['searchText'] . '%'];
            }
            if (!empty($param['lang'])) {
                $where['lang'] = $param['lang'];
            }
            if (!empty($param['is_nav'])) {
                $where['is_nav'] = $param['is_nav'];
            }
            $article = new Model();
            $selectResult = $article->getArticlesByWhere($where, $offset, $limit);
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['status'] = $vo['status'] == 1 ? '启用' : '停用';
                $selectResult[$key]['is_nav'] = $article_navs[$vo['is_nav']];
                $selectResult[$key]['type'] = $article_type[$vo['type']];
                $selectResult[$key]['lang'] = $langs[$vo['lang']];
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                if ($selectResult[$key]['url'] == '') {
                    $selectResult[$key]['url'] = "/article/index/id/{$vo['id']}";
                }
                $selectResult[$key]['thumb'] = toMedia($vo['thumb']);
            }
            $return['total'] = $article->getAllArticles($where);  // 总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
        $this->assign("langs", $langs);
        $this->assign("article_navs", $article_navs);
        return $this->fetch();
    }

    public function add() {
        $id = $this->request->param("id/d");
        if (request()->isPost()) {
            $param = input('post.');
            $model = new Model;
            if ($id > 0) {
                $result = $model->validate('ArticleValidate')->isUpdate()->save($param);
            } else {
                $result = $model->validate('ArticleValidate')->allowField(true)->save($param);
            }
            if (false === $result) {
                $this->error($model->getError());
            } else {
                insert_operation_log('文章:' . ($id > 0 ? '修改' : '添加'), $param['title']);
            }
            $this->success(lang('提交成功'), url('index'));
        }
        $info = Model::where('id', $id)->find();
        if (empty($info)) {
            $model = new Model();
            $info = array_fill_keys($model->getTableFields(), '');
            $info['status'] = 1;
        }
        $article_type = config("my.article_type");
        $article_navs = config("my.article_navs");
        $urls = config('my.urls');
        $this->assign("info", $info);
        $this->assign("urls", $urls);
        $this->assign("article_type", $article_type);
        $this->assign("article_navs", $article_navs);
        $this->assign("langs", getLangs());
        return $this->fetch();
    }

    public function del() {
        $id = input('param.id');
        $article = new Model();
        $flag = $article->delArticle($id);
        return json(msg($flag['code'], $flag['data'], lang($flag['msg'])));
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id) {
        return [
            '编辑' => [
                'auth' => 'articles/add',
                'href' => url('articles/add', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'articles/del',
                'href' => "javascript:del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

    public function uploadimg() {
        $ue_path = ROOT_PATH . "/public/static/admin/js/plugins/ueditor/php/";
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($ue_path . "config.json")), true);
        $action = $this->request->param("action/s");

        switch ($action) {
            case 'config':
                $result = $CONFIG;
                break;

            /* 上传图片 */
            case 'uploadimage':
            /* 上传涂鸦 */
            case 'uploadscrawl':
            /* 上传视频 */
            case 'uploadvideo':
            /* 上传文件 */
            case 'uploadfile':
                $UploadModel = new \app\common\model\UploadModel();
                $ret = $UploadModel->Upload('upfile');
                $ret['data']['src'] = toMedia($ret['data']['path']);
                $ret['data']['url'] = toMedia($ret['data']['path']);
                if ($ret['code'] == 200) {
                    $result = [
                        "state" => "SUCCESS",
                        "url" => toMedia($ret['data']['path']),
                        "title" => $ret['data']['name'],
                        "original" => $ret['data']['name'],
                        "type" => $ret['data']['ext'],
                        "size" => $ret['data']['size']
                    ];
                } else {
                    $result = ['state' => lang($ret['msg'])];
                }
                break;

            /* 列出图片 */
//            case 'listimage':
            /* 列出文件 */
//            case 'listfile':
            /* 抓取远程文件 */
//            case 'catchimage':
            default:
                $result = ['state' => lang('请求地址出错')];
                break;
        }
        return json($result);
    }

}
