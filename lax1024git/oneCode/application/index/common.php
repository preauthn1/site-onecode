<?php
use think\Db;
use think\Cache;
/**
 * 当天订单收益
 * @param type $userid
 * @return int
 */
function getTodayOrderGray($userid, $is_free = -1) {
    return 0;
}
/**
 * 昨天订单收益
 * @param type $userid
 * @return int
 */
function getYesterdayOrderGray($userid, $is_free = -1) {
    return 0;
}
/**
 * 余额宝预计收益
 * @param type $userid
 * @return int
 */
function getTodayYuebaoGray($userid) {
    return 0;
}


// 团队当天佣金
function getTodayTeamGrayByFromUserId($from_user_id, $userid) {
    return 0;
}

function genShareUrl($url, $type = 'email', $username = '') {
    $format = "%s";
    if ($type == 'email') {
        $format = "mailto:?subject={$username} ".lang('asked you to make money')."&body=".lang('Here is a great opportunity to make money online').": %s";
    }elseif($type == 'sms') {
        $format = "sms:&body=".lang('Here is an opportunity to make money through e-commerce. See details here').":%s";
    }elseif($type == 'whatsapp') {
        $format = "https://wa.me/?text=".lang('Here is a great opportunity to make money online').":%s";
    }
    
    return sprintf($format, $url);
}