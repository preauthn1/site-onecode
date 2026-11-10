<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    'mobileconfig/[:inviteCode]'   => ['index/login/mobileconfig', ['method' => 'GET']],
    'download/[:inviteCode]'   => ['index/login/download', ['method' => 'GET']],
    'notify/pay/:pay_id'   => ['index/notify/pay', ['method' => 'GET|POST'], ['pay_id' => '\d+']],
    'notify/transfer/:channel'   => ['index/notify/transfer', ['method' => 'GET|POST']],
    'payed/[:type]/[:message]'   => ['index/login/payed', ['method' => 'GET|POST']],
    'goods/list/html'   => ['index/goods/getListHtml', ['method' => 'GET']],
    'search'   => ['index/goods/search', ['method' => 'GET']],
];
