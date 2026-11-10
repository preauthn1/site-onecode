<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/4
 * Time: 10:59
 */

namespace app\index\controller;

use app\common\model\ArticleModel;
use app\admin\model\NoticeModel;
use app\common\model\ActivitynewModel;
use think\Db;

class Article extends Base
{

    // 信息
    public function index($id){
        $model = new ArticleModel;
        $info = $model->getOneArticle($id);
        if(!$info){
            $this->error(lang('暂无数据'));
        }
        $this->setTitle(lang($info['title']));
        $this->assign([
            'info' => $info
        ]);
        if ($info['url'] != '') {
            if (!strexists($info['url'], "http://") && !strexists($info['url'], "https://")) {
                $info['url'] = url($info['url']);
            }
            return $this->redirect($info['url']);
        }
        return $this->fetch();
    }

    // 信息
    public function notice($id){
        $model = new NoticeModel;
        $info = $model->getOneNotice($id);
        if(!$info){
            $this->error(lang('暂无数据'));
        }
        $this->setTitle(lang($info['title']));
        $this->assign([
            'info' => $info
        ]);
        return $this->fetch('index');
    }

    public function activity($id) {
        $model = new ActivitynewModel;
        $info = $model->where('id', $id)->find();
        if(!$info){
            $this->error(lang('暂无数据'));
        }
        $extra = array_unique(array_filter(explode("\n", html_entity_decode($info->extra))));
        if (isArrayAndNotEmpty($extra)) {
            foreach ($extra as $item) {
                list($exp, $url) = explode("|", $item);
                if ($exp != 'url') {
                    continue;
                }
                if (!strexists($url, "http://") && !strexists($url, "https://")) {
                    $url = url($url);
                }
                return $this->redirect($url);
            }
        }
        $this->setTitle(lang($info['title']));
        $this->assign([
            'info' => $info
        ]);
        return $this->fetch('index');
    }
}