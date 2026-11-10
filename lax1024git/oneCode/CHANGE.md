更新记录
===========
### 2020-09-28

1. 数据表增加lang字段,sql如下
```
ALTER TABLE `qd_articles` 
ADD COLUMN `lang` varchar(30) NULL DEFAULT 'zh-cn' COMMENT '语言';

ALTER TABLE `qd_banner` 
ADD COLUMN `lang` varchar(30) NULL DEFAULT 'zh-cn' COMMENT '语言';

ALTER TABLE `qd_goods` 
ADD COLUMN `lang` varchar(30) NULL DEFAULT 'zh-cn' COMMENT '语言';

ALTER TABLE `qd_notice` 
ADD COLUMN `lang` varchar(30) NULL DEFAULT 'zh-cn' COMMENT '语言';

ALTER TABLE `qd_paylist` 
ADD COLUMN `lang` varchar(30) NULL DEFAULT 'zh-cn' COMMENT '语言';

ALTER TABLE `qd_home_user` 
ADD COLUMN `lang` varchar(30) NULL DEFAULT 'zh-cn' COMMENT '语言';
```

### 2020-09-30
1. 会员增加软删除
2. 后台增加操作日志记录表
数据库更新
```
ALTER TABLE `qd_home_user` 
ADD COLUMN `delete_time` int NULL COMMENT '删除时间' AFTER `usdt`;

CREATE TABLE `qd_operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT '',
  `action` varchar(255) DEFAULT NULL,
  `content` varchar(2000) DEFAULT '',
  `ip` varchar(255) DEFAULT '',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='操作日志记录表';
```


### 2020-10-02
1. 增加会员充值金额升级
2. 修改zshop模板
3. 优化细节
4. 对接闪付

数据库更新
```
ALTER TABLE `qd_home_user_level` 
MODIFY COLUMN `price` int(11) NULL DEFAULT 0 COMMENT '升级充值金额' AFTER `users`,
ADD COLUMN `qd_rate` int(11) NULL DEFAULT 30 COMMENT '万分比' AFTER `income_rate`;
```


### 2020-10-06

数据库更新
```
ALTER TABLE `qd_home_user_level` 
ADD COLUMN `img` varchar(255) NULL DEFAULT '' COMMENT '图片' AFTER `qd_rate`,
ADD COLUMN `color` varchar(255) NULL DEFAULT '' COMMENT '颜色' AFTER `img`;

ALTER TABLE `qd_articles` 
MODIFY COLUMN `content` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '文章内容' AFTER `title`;
```

### 2020-10-17

数据库更新
```
ALTER TABLE `qd_home_user_level` ADD `is_free` INT(2) NOT NULL DEFAULT '0' COMMENT '是否是免费商城' AFTER `color`;
ALTER TABLE `qd_home_user_level` ADD `sort` INT(8) NOT NULL DEFAULT '0' COMMENT '排序' AFTER `is_free`;
```

### 2020-10-15

数据库更新
```
ALTER TABLE `qd_role` CHANGE `rule` `rule` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '权限节点数据';
```

### 2020-10-18

数据库更新
```

CREATE TABLE `qd_activity_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '活动类型：0-未定义类型 1-打卡签到 2-充值指定的金额 3-福利',
  `award_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '奖励类型：0-未定义类型 1-系统货币 2-积分',
  `award_quantity` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '奖励数量',
  `userid` int(11) NOT NULL DEFAULT 0 COMMENT '会员ID',
  `created_at` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_at` int(10) NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `activity_userid`(`userid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '活动记录表' ROW_FORMAT = Dynamic;

ALTER TABLE `qd_activity_log` CHANGE `award_quantity` `award_quantity` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '奖励数量';

```

### 2020-10-22

数据库更新
```

CREATE TABLE `qd_turntable_prize` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '奖品ID',
  `name` varchar(100) DEFAULT NULL COMMENT '奖品名称',
  `num` int(11) DEFAULT '-1' COMMENT '奖品数量-1=无限',
  `type` int(4) DEFAULT '0' COMMENT '奖品类型0=返现红包，1=增加抢单次数，2=实物，3=谢谢参与',
  `value` varchar(50) NOT NULL COMMENT '奖品类型对应数值',
  `status` int(4) DEFAULT '1' COMMENT '奖品状态1=正常，2=禁用',
  `img` varchar(255) NOT NULL COMMENT '奖品图片',
  `sort` int(8) NOT NULL DEFAULT '0' COMMENT '排序',
  `probability` int(8) DEFAULT '1' COMMENT '中奖几率',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='转盘奖品表';

CREATE TABLE `qd_turntable_prize_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '奖品ID',
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `prize_id` int(11) DEFAULT NULL COMMENT '奖品ID',
  `money` decimal(20,4) DEFAULT NULL COMMENT '抽奖金额',
  `name` varchar(100) DEFAULT NULL COMMENT '奖品名称',
  `num` int(11) DEFAULT '-1' COMMENT '奖品数量-1=无限',
  `type` int(4) DEFAULT '0' COMMENT '奖品类型0=返现红包，1=增加抢单次数，2=实物，3=谢谢参与',
  `value` varchar(50) NOT NULL COMMENT '奖品类型对应数值',
  `probability` int(8) DEFAULT '1' COMMENT '中奖几率',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8 COMMENT='转盘抽奖记录表';

INSERT INTO `qd_node`(`id`, `node_name`, `control_name`, `action_name`, `is_menu`, `type_id`, `style`) VALUES (102, '转盘管理', '#', '#', 2, 0, 'fa fa-cny');
INSERT INTO `qd_node`(`id`, `node_name`, `control_name`, `action_name`, `is_menu`, `type_id`, `style`) VALUES (104, '奖品设置', 'turntable', 'prizeList', 2, 102, '');
INSERT INTO `qd_node`(`id`, `node_name`, `control_name`, `action_name`, `is_menu`, `type_id`, `style`) VALUES (105, '中奖记录', 'turntable', 'awardList', 2, 102, '');


```

### 2020-10-27

数据库更新
```
DROP TABLE IF EXISTS `qd_score_log`;
CREATE TABLE IF NOT EXISTS `qd_score_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `total_quantity` decimal(21,4) NOT NULL DEFAULT '0.0000' COMMENT '积分总额',
  `excharge_quantity` decimal(21,4) NOT NULL DEFAULT '0.0000' COMMENT '兑换数量',
  `subtraction_quantity` decimal(24,4) NOT NULL DEFAULT '0.0000' COMMENT '兑换的积分',
  `excharge_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '兑换类型 1-系统货币 2-实物',
  `curreny_excharge_rate` decimal(3,2) NOT NULL DEFAULT '0.00' COMMENT '本次兑换率',
  `excharge_userid` int(11) NOT NULL COMMENT '兑换的用户ID',
  `excharge_time` int(10) NOT NULL DEFAULT '0' COMMENT '兑换时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0-待处理 1-审核通过 2-驳回 3-审核通过，尚有余额',
  `audit_userid` int(11) NOT NULL DEFAULT '0' COMMENT '审核人ID',
  `audit_time` int(10) NOT NULL DEFAULT '0' COMMENT '审核时间',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `score_audit_time` (`audit_time`),
  KEY `excharge_userid` (`excharge_userid`) USING BTREE,
  KEY `excharge_type` (`excharge_type`) USING BTREE,
  KEY `excharge_time` (`excharge_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分记录表';
```




### 2020-10-30
```
ALTER TABLE `qd_lang` 
ADD COLUMN `status` tinyint(1) NULL DEFAULT 1 AFTER `is_default`;

ALTER TABLE `qd_lang` 
ADD COLUMN `flag` varchar(255) NULL DEFAULT '' COMMENT '国旗' AFTER `is_default`;

ALTER TABLE `qd_node` 
ADD COLUMN `sort` int(0) NULL DEFAULT 0 COMMENT '排序' AFTER `style`;

ALTER TABLE `qd_paylist` 
ADD COLUMN `sort` int(1) NULL DEFAULT 0 COMMENT '排序' AFTER `status`;

ALTER TABLE `qd_recharge` 
ADD COLUMN `img` varchar(255) NULL DEFAULT '' COMMENT '付款凭证' AFTER `pay_id`;

ALTER TABLE `qd_articles` 
ADD COLUMN `type` varchar(50) NULL DEFAULT 'article' COMMENT '文章类型' AFTER `lang`,
ADD COLUMN `url` varchar(255) NULL DEFAULT '' COMMENT '链接' AFTER `title`,
ADD COLUMN `is_nav` tinyint(1) NULL DEFAULT 0 COMMENT '是否首页菜单' AFTER `url`,
ADD COLUMN `sort` int(0) NULL DEFAULT 0 COMMENT '排序' AFTER `is_nav`,
ADD COLUMN `thumb` varchar(255) NULL DEFAULT '' COMMENT '缩略图' AFTER `sort`;

ALTER TABLE `qd_articles` 
MODIFY COLUMN `create_time` int(10) NULL DEFAULT NULL AFTER `status`,
ADD COLUMN `update_time` int NULL AFTER `create_time`;

ALTER TABLE `qd_banner` 
ADD COLUMN `sort` int(0) NULL DEFAULT 0 COMMENT '排序' AFTER `create_time`;

CREATE TABLE `qd_home_user_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户id',
  `ip` varchar(255) DEFAULT '' COMMENT 'IP',
  `create_time` int(11) DEFAULT NULL COMMENT '登录时间',
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `create_time` (`create_time`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户登录记录表';

CREATE TABLE `qd_day_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stat_date` date DEFAULT NULL COMMENT '日期',
  `recharge` decimal(20,4) DEFAULT NULL COMMENT '充值总额',
  `withdraw` decimal(20,4) DEFAULT NULL COMMENT '提现总额',
  `recharge_user` int(11) DEFAULT '0' COMMENT '充值人数',
  `withdraw_user` int(11) DEFAULT '0' COMMENT '提现人数',
  `first_recharge_user` int(11) DEFAULT '0' COMMENT '首充人数',
  `online_user` int(11) DEFAULT '0' COMMENT '在线人数',
  `order_user` int(11) DEFAULT '0' COMMENT '抢单人数',
  `wr_diff` int(11) DEFAULT '0' COMMENT '充提差额',
  `order_price` decimal(20,4) DEFAULT NULL COMMENT '抢单商品总额',
  `order_gray` decimal(20,4) DEFAULT NULL COMMENT '佣金总额',
  `rebate` decimal(20,4) DEFAULT NULL COMMENT '返佣总额',
  `manual_money` decimal(20,4) DEFAULT NULL COMMENT '手动赠送总额',
  `user_money` decimal(20,4) DEFAULT NULL COMMENT '用户余额',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COMMENT='日统计报表';

ALTER TABLE `qd_home_user` 
MODIFY COLUMN `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '密码' AFTER `mobile`,
ADD COLUMN `bank_code` varchar(100) NULL DEFAULT '' AFTER `level_id`;

ALTER TABLE `qd_goods` 
MODIFY COLUMN `goods_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品名称' AFTER `level_id`;

ALTER TABLE `qd_order` 
ADD COLUMN `is_free` tinyint(1) NULL DEFAULT 0 COMMENT '是否是免费商城' AFTER `is_demo`;

```

### 2020-11-05
```
ALTER TABLE `qd_paylist` 
ADD COLUMN `min_money` int(0) NULL DEFAULT 0 COMMENT '最小金额' AFTER `sort`,
ADD COLUMN `max_money` int(0) NULL DEFAULT 0 COMMENT '最大金额' AFTER `min_money`;

ALTER TABLE `qd_recharge` 
ADD COLUMN `extra` varchar(255) NULL DEFAULT '' COMMENT '扩展数据字段' AFTER `trade_no`;
```

### 2020-12-04
```
ALTER TABLE `qd_home_user` 
ADD COLUMN `points` decimal(20, 4) NULL COMMENT '用户积分' AFTER `total_money`;

CREATE TABLE `qd_activitys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '' COMMENT '活动名称',
  `thumb` varchar(255) DEFAULT '' COMMENT '活动图片',
  `start_time` int(11) DEFAULT NULL COMMENT '开始时间',
  `end_time` int(11) DEFAULT NULL COMMENT '结束时间',
  `content` mediumtext COMMENT '活动介绍',
  `type` tinyint(1) DEFAULT '0' COMMENT '0线下活动，1充值活动，2邀请活动',
  `reward_type` tinyint(1) DEFAULT '1' COMMENT '1货币，2积分',
  `reward` varchar(50) DEFAULT '0' COMMENT '奖励，有百分号时按百分比发放奖励，充值活动有效',
  `times` int(11) DEFAULT '0' COMMENT '可领奖次数',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `extra` text COMMENT '扩展信息',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='活动表';

CREATE TABLE `qd_activity_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) DEFAULT '0' COMMENT '活动id',
  `user_id` int(11) DEFAULT '0' COMMENT '用户id',
  `from_user_id` int(11) DEFAULT '0' COMMENT '来自用户id',
  `reward` decimal(20,4) unsigned DEFAULT NULL COMMENT '奖励',
  `reward_type` tinyint(1) DEFAULT '1' COMMENT '1货币，2积分',
  `status` tinyint(1) DEFAULT '1' COMMENT '0待领取，1已领取',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_from_userid` (`from_user_id`),
  KEY `idx_activity_id` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='活动记录表';
```