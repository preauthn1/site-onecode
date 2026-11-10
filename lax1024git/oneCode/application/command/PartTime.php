<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Env;

class PartTime extends Command {

    private $db1 = [
        'type' => 'mysql',
        'hostname' => 'mysql',
        'database' => 'parttime2',
        'username' => 'root',
        'password' => '123456',
        'hostport' => '3306',
        'dsn' => '',
        'params' => [],
        'charset' => 'utf8',
        'prefix' => '',
    ];
    
    private $db2 = [
        'type' => 'mysql',
        'hostname' => 'mysql',
        'database' => 'qd_lywl_cc_demo',
        'username' => 'root',
        'password' => '123456',
        'hostport' => '3306',
        'dsn' => '',
        'params' => [],
        'charset' => 'utf8',
        'prefix' => '',
    ];

    protected function configure() {
        $this->setName('parttime')->setDescription('导进老数据');
    }

    protected function execute(Input $input, Output $output) {
//        $this->insertGoods();
//        $this->insertUsers(0);
//        $this->insertAddress();
//        $this->insertRecharge();
//        $this->insertWithdraw();
//        $this->insertMoneyLog();
        
        $url = "https://qd-goods2.s3-ap-southeast-1.amazonaws.com/";
        $list = Db::table('qd_goods')->select();
        foreach ($list as $key => $value) {
            $info = pathinfo($value['pic']);
            $basename = urlencode($info['basename']);
            if ($info['basename'] == $basename) {
                continue;
            }
            Db::table('qd_goods')->where('goods_id', $value['goods_id'])->update(['pic' => "{$url}{$basename}"]);
            dump("{$url}{$basename}");
//            die;
        }
    }
    private function insertGoods() {
        $list = Db::connect($this->db1)->table('commodity')->select();
        $i = 0;
        foreach ($list as $item) {
//            $info = pathinfo($item['img']);
//            $img = file_get_contents($item['img']);
//            file_put_contents(ROOT_PATH . "public/upload/goods/{$info['basename']}", $img);
//            dump("{$i}=====" . $info['basename']);
//            die;
            $i++;
            $row = [
                'goods_name' => $item['name'],
                'pic' => strval($item['img']),
                'shop_price' => $item['amount'],
                'update_time' => time(),
                'create_time' => time(),
                'status' => 1,
            ];
            $r = Db::connect($this->db2)->table('qd_goods')->insert($row, true);
            dump($r);
        }
    }
    private function insertMoneyLog() {
        $page = 0;
        while (true) {
            $list = $this->getMoneyLog($page);
            
            $page++;
            if (empty($list)) {
                break;
            }
            echo "page:" . $page . "\n";
            foreach ($list as $row) {
                $r = Db::connect($this->db2)->table('qd_money_log')->insert($row, true);
//                dump($r);
            }
            
        }
    }
    
    private function getMoneyLog($page = 0) {
        // 类型 recharge 充值, withdraw 提现, present 赠送, game_buy 投注, game_award 奖励 storage 存储 commodity 抢单
        $type = [
            'recharge'  => '1',
            'withdraw'  => '7',
            'present'   => '11',
            'manual_present' => '0',
            'storage_ra' => '5',
            'storage_wi' => '6',
            'commodity' => '3',
            'commodity_fr' => '3',
            'commodity_sl' => '2',
            'commodity_re' => '4',
            'commodity_th' => '2',
        ];
        $list = Db::connect($this->db1)->table('user_bill')->page($page)->limit(10000)->select();
        $all = [];
        foreach ($list as $item) {
            $row = [
//                'id' => $item['id'],
                'user_id' => $item['user_id'],
                'money' => $item['changeAmount'],
                'user_money' => $item['afterAmount'],
                'status' => 1,
                'type' => isset($type[$item['sub_type']]) ? $type[$item['sub_type']] : 0,
                'create_time' => $item['created_at'],
                'remarks' => $item['remark']
            ];
            
            $all[] = $row;
        }
        
        return $all;
    }

    private function insertAddress() {
        $list = Db::connect($this->db1)->table('users')->select();
        foreach ($list as $item) {
            if (empty($item['ship_name'])) {
                continue;
            }
            $row = [
                'user_id' => $item['id'],
                'consignee' => strval($item['ship_name']),
                'mobile' => strval($item['ship_mobile']),
                'area' => 'area',
                'address' => $item['ship_address'],
                'zipcode' => $item['ship_postcode'],
                'status' => 0,
                'is_default' => 1,
            ];
            $r = Db::connect($this->db2)->table('qd_home_user_address')->insert($row, true);
            dump($r);
        }
    }

    private function insertWithdraw() {
        //状态  0 等待确认 / 1 已确认 / 2 已拒绝
        $list = Db::connect($this->db1)->table('user_withdraw')->select();
        $status = [
            '0' => 1,
            '1' => 2,
            '2' => 3,
        ];
        foreach ($list as $item) {
            $bank = Db::connect($this->db1)->table('bank_account')->where('id', $item['bank_account_id'])->find();
            $real_name = Db::connect($this->db1)->table('users')->where('id', $item['user_id'])->value('real_name');
            $row = [
                'id' => $item['id'],
                'user_id' => $item['user_id'],
                'money' => $item['amount'],
                'true_name' => strval($real_name),
                'taxfee' => 0,
                'type' => 1,
                'status' => isset($status[$item['status']]) ? $status[$item['status']] : 1,
                'remark' => $item['reason'] ? $item['reason'] : $item['remark'],
                'create_time' => $item['created'],
                'check_time' => $item['updated'],
                'order_id' => genOrderSN($item['id'], 'WOPT'),
            ];
            if ($bank) {
                $row['bank_name'] = $bank['bank_code'];
                $row['true_name'] = $bank['bank_account_name'];
                $row['bank_number'] = $bank['bank_account_num'];
                $row['bank_kh'] = $bank['bank_branch_name'];
            }
            
            $r = Db::connect($this->db2)->table('qd_withdrawals')->insert($row, true);
            dump($r);
        }
    }
    
    private function insertRecharge() {
        //状态  0 等待确认 / 1 已确认 / 2 已拒绝 / 3 已经收到确认，等后台自动处理通过
        $list = Db::connect($this->db1)->table('user_deposit')->select();
        $all = [];
        $status = [
            '0' => 1,
            '1' => 2,
            '2' => 3,
            '3' => 1
        ];
        foreach ($list as $item) {
            $content = json_decode($item['content'], true);
            $row = [
                'id' => $item['id'],
                'user_id' => $item['user_id'],
                'money' => $item['amount'],
                'type' => 1,
                'status' => isset($status[$item['status']]) ? $status[$item['status']] : 1,
                'remark' => $item['remark'],
                'create_time' => $item['created'],
                'update_time' => $item['updated'],
                'order_id' => genOrderSN($item['id'], 'OPT'),
            ];
            if (isset($content['存款账号'])) {
                $row['pay_account'] = $content['存款账号'];
            }
            if (isset($content['附言'])) {
                $row['user_note'] = $content['附言'];
            }
            $r = Db::connect($this->db2)->table('qd_recharge')->insert($row, true);
            dump($r);
        }
    }

    private function insertUsers($referrer_id = 0) {
        $all = $this->getUsers($referrer_id);
        if (!empty($all)) {
            foreach ($all as $row) {
                $r = Db::connect($this->db2)->table('qd_home_user')->insert($row, true);
                dump($r);
            }
        }
    }

    private function getUsers($referrer_id = 0) {
        $list = Db::connect($this->db1)->table('users')->where('referrer_id', $referrer_id)->select();
        if (empty($list)) {
            return [];
        }
        $all = [];
        foreach ($list as $item) {
            $bank = Db::connect($this->db1)->table('bank_account')->where('user_id', $item['id'])->order('id DESC')->where('deleted_at', 0)->find();
            $account = Db::connect($this->db1)->table('user_account')->where('id', $item['id'])->find();
//            dump($bank);
            $new = [
                'id' => $item['id'],
                'user_name' => $item['username'],
                'nick_name' => $item['nickname'],
                'true_name' => strval($item['real_name']),
                'mobile' => $item['mobile'] ?? $item['username'],
                'password' => $item['password'],
                'paypwd' => $item['withdraw_password'],
                'invite_code' => $item['username'],
                'leader1' => $item['referrer_id'],
                'status' => $item['status'] == 1 ? 1 : 2,
                'is_xuni' => $item['test'],
                'lang' => $item['lang'],
                'create_time' => time(),
                'update_time' => time(),
                'lang' => $item['lang'],
                'user_money' => 0,
                'tyj_money' => 0,
                'frozen_money' => 0,
                'total_money' => 0,
                'avatar' => mt_rand(1, 100),
            ];
            if ($bank) {
                $new['bank_code'] = $bank['bank_code'];
                $new['bank_name'] = $bank['bank_name'];
                $new['true_name'] = $bank['bank_account_name'];
                $new['bank_number'] = $bank['bank_account_num'];
                $new['bank_kh'] = $bank['bank_branch_name'];
                $new['create_time'] = $bank['created_at'];
                $new['update_time'] = $bank['updated_at'];
            }
            if ($account) {
                $new['user_money'] = $account['usable_amount'];
                $new['frozen_money'] = $account['freeze_amount'];
                $new['total_money'] = $account['amount'];
            }
            $all[] = $new;
            $this->insertUsers($item['id']);
        }
        
        return $all;
    }
}
