<?php

namespace app\index\controller;

use app\admin\model\PrizeLogModel;
use app\admin\model\PrizeModel;
use app\admin\model\RechargeModel;
use app\common\model\UserModel;
use think\Db;
use think\Exception;
use think\Cache;

class Turntable extends Base {

    public function index() {
        if(isset($this->configInfo['zp_enable']) && $this->configInfo['zp_enable']==0){
            return redirect(url('index/index'));
        }
        $this->setTitle(lang('转盘'), 1, url("user/index"));
        $prize = Db::name('turntable_prize')->where(['status' => 1])->order('sort DESC')->select();
        $_list = [];
        foreach ($prize as $k => $v) {
            $_list[] = [
                'id' => $v['id'],
                'name' => $v['name'],
                'image' => toMedia($v['img']),
                'rank' => $k,
            ];
        }
        //最近10次中奖信息
        $prize_log = new PrizeLogModel();
        $new_10 = $prize_log->getPrizeLogByWhere(['status' => 1, 'type' => ['in', '0,1,2']], 0, 10);
        foreach ($new_10 as $k => $v) {
            $new_10[$k]['mobile'] = substr($v['mobile'], 0, 3) . '****' . substr($v['mobile'], strlen($v['mobile']) - 3);
        }

        $my_list_log = $this->getPrizeLog();
        $this->assign('list', json_encode($_list));
        $this->assign('gift_log', $new_10);
        $this->assign('my_list_log', $my_list_log);
        return $this->fetch();
    }

    /**
     * 抽奖
     * @return \think\response\Json
     */
    public function getGift() {
        $free_count = $this->getFreeCount();
        if (request()->isAjax()) {
            if ($free_count <= 0 && $this->configInfo['zp_expend'] > $this->userInfo['user_money']) {
                return json(msg(-1, '', lang('每次抽奖最少花费%s', [i18n_currency($this->configInfo['zp_expend'])])));
            }
            //免费抽奖次数用完了
            if ($this->configInfo['zp_expend'] <= 0 && $free_count <= 0) {
                return json(msg(-1, '', lang('您的抽奖机会已经用完了')));
            }

            $prize = Db::name('turntable_prize')
                ->where("status = 1 AND (num = -1 OR num >= 1)")
                ->order('probability DESC')->select();
            if (count($prize) == 0) {
                return json(msg(-1, '', lang('暂无奖品')));
            }

            $redis = Cache::store('redis');
            $redis_key = "turntable:{$this->userid}";
            if ($redis->has($redis_key)) {
                return json(msg(-1, '', lang('正在抽奖中')));
            }
            $redis->set($redis_key, 1);
            Db::startTrans();
            try {
                $default_prize = Db::name('turntable_prize')
                    ->where("status = 1 AND type = 3 AND (num = -1 OR num >= 1)")
                    ->orderRaw('rand()')->find();
                //默认为谢谢参与，如果没有设置默认为中奖几率最大的。
                $_gift = empty($default_prize) ? $prize[0] : $default_prize;
                $max = 1000000;
                foreach ($prize as $k => $v) {
                    $_r = random_int(0, $max);
                    if ($v['probability'] >= $_r) {
                        $_gift = $v;
                        break;
                    }
                }
                $this->sendGift($this->userInfo['id'], $_gift, $free_count > 0);
                Db::commit();
                $_ret_data = [
                    'id' => $_gift['id'],
                    'name' => $_gift['name'],
                    'image' => toMedia($_gift['img']),
                    'type' => $_gift['type'],
                ];
                $redis->rm($redis_key);
                return json(msg(200, $_ret_data, lang('抽奖完成')));
            } catch (\Exception $e) {
                $redis->rm($redis_key);
                Db::rollback();
            }
        }
        return json(msg(-1, '', lang('抽奖失败')));
    }

    /**
     * 发放奖品
     * @param $user_id
     * @param $gift
     * @return mixed
     * @throws Exception
     */
    private function sendGift($user_id, $gift, $is_free = false) {
        //更新奖品库存
        if ($gift['num'] > 0) {
            if (Db::name('turntable_prize')->where(['id' => $gift['id']])->setDec('num')) {
                $_p = Db::name('turntable_prize')->where(['id' => $gift['id']])->find();
                if ($_p['num'] < 0) {
                    $prize = new PrizeModel();
                    $_p['num'] = 0;
                    if (!$prize->save($_p)) {
                        throw new Exception(lang('奖品发放失败'));
                    }
                }
            }
        }
        //扣除抽奖花费
        if (!$is_free && $this->configInfo['zp_expend'] > 0) {
            addMoneyLog($user_id, -1 * $this->configInfo['zp_expend'], 12, lang('抽奖支付'));
        }
        if ($gift['type'] == '0') {
            addMoneyLog($user_id, $gift['value'], 13, lang('抽奖红包'));
        }
        if ($is_free) {
            $this->decFreeCount();
        }
        $prize_log = new PrizeLogModel();
        $data['user_id'] = $user_id;
        $data['prize_id'] = $gift['id'];
        $data['money'] = $this->configInfo['zp_expend'];
        $data['name'] = $gift['name'];
        $data['num'] = $gift['num'];
        $data['type'] = $gift['type'];
        $data['value'] = $gift['value'];
        $data['probability'] = $gift['probability'];
        $data['create_time'] = time();
        if (!$prize_log->save($data)) {
            throw new Exception(lang('奖品发放失败'));
        }
        return $data;
    }

    public function myPrizeLog() {
        if (request()->isAjax()) {
            $list_new = $this->getPrizeLog();
            return json(msg(200, $list_new, lang('中奖记录')));
        }
        return json(msg(-1, '', lang('没有中奖记录')));
    }
    
    private function getPrizeLog() {
        $list = Db::name('turntable_prize_log')
            ->where("user_id = {$this->userInfo['id']} AND type != 3")
            ->order('id DESC')->select();
        $list_new = [];
        foreach ($list as $k => $v) {
            $list_new[] = [
                'name' => $v['name'],
                'create_time' => date('Y-m-d H:i:s', $v['create_time'])
            ];
        }
        return $list_new;
    }

    /**
     * 获取免费抽奖次数
     */
    private function getFreeCount() {
        $num = Db::name('turntable_count')
            ->where('user_id', $this->userid)
            ->where('num', ">", 0)
            ->sum('num');
        
        return intval($num);
    }
    /**
     * 减少免费抽奖次数
     */
    private function decFreeCount() {
        $info = Db::name('turntable_count')
            ->where('user_id', $this->userid)
            ->where('num', ">", 0)
            ->order("num ASC")
            ->find();
         Db::name('turntable_count')
            ->where('user_id', $this->userid)
            ->where('id', $info['id'])
            ->where('num', ">", 0)
            ->update(['num' => $info['num']-1]);
    }
}
