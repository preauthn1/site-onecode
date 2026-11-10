<?php

namespace app\common\model;

use think\Model;

class ActivitynewModel extends Model {

    protected $name = 'activitys';
    protected $autoWriteTimestamp = true;
    protected $type = [
        'start_time' => 'timestamp',
        'end_time' => 'timestamp',
    ];

    protected function setUserTypeAttr($value) {
        return isArrayAndNotEmpty($value) ? implode(",", $value) : '';
    }
    
    protected function getUserTypeAttr($value) {
        return is_string($value) ? explode(",", $value) : [];
    }
    protected function setTriggerTypeAttr($value) {
        return isArrayAndNotEmpty($value) ? implode(",", $value) : '';
    }
    
    protected function getTriggerTypeAttr($value) {
        return is_string($value) ? explode(",", $value) : [];
    }

}
