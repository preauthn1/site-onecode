<?php

namespace app\admin\model;

use think\Model;

class OperationModel extends Model {

    protected $name = 'operation_log';
    protected $autoWriteTimestamp = 'datetime';
    protected $insert = ['ip'];

    protected function setIpAttr() {
        return getip();
    }

}
