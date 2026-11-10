/*
 Navicat Premium Data Transfer

 Source Server         : local-mysql3306
 Source Server Type    : MySQL
 Source Server Version : 50736
 Source Host           : 127.0.0.1:3306
 Source Schema         : pingdan

 Target Server Type    : MySQL
 Target Server Version : 50736
 File Encoding         : 65001

 Date: 19/11/2021 15:06:49
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for qd_activity
-- ----------------------------
DROP TABLE IF EXISTS `qd_activity`;
CREATE TABLE `qd_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '' COMMENT '活动名称',
  `thumb` varchar(255) DEFAULT '' COMMENT '活动图片',
  `content` mediumtext COMMENT '活动介绍',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='活动表';

-- ----------------------------
-- Table structure for qd_activity_record
-- ----------------------------
DROP TABLE IF EXISTS `qd_activity_record`;
CREATE TABLE `qd_activity_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_xuni` tinyint(1) DEFAULT '0' COMMENT '虚拟用户',
  `activity_id` int(11) DEFAULT '0' COMMENT '活动id',
  `user_id` int(11) DEFAULT '0' COMMENT '用户id',
  `status` tinyint(1) DEFAULT '0' COMMENT '0待审核，1活动中，2已完成',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_userid` (`user_id`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='活动记录表';

-- ----------------------------
-- Table structure for qd_activitys
-- ----------------------------
DROP TABLE IF EXISTS `qd_activitys`;
CREATE TABLE `qd_activitys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '' COMMENT '活动名称',
  `thumb` varchar(255) DEFAULT '' COMMENT '活动图片',
  `start_time` int(11) DEFAULT NULL COMMENT '开始时间',
  `end_time` int(11) DEFAULT NULL COMMENT '结束时间',
  `content` mediumtext COMMENT '活动介绍',
  `user_type` varchar(200) DEFAULT '' COMMENT '0所有用户，1充值用户，2二次充值用户，3新用户，4老用户，5有直属下级的用户',
  `trigger_type` varchar(200) DEFAULT '' COMMENT '触发类型',
  `type` tinyint(1) DEFAULT '0' COMMENT '0线下活动，1充值活动，2邀请活动，3邀请充值活动，4签到活动',
  `reward_type` tinyint(1) DEFAULT '1' COMMENT '1货币，2积分',
  `reward` varchar(50) DEFAULT '0' COMMENT '奖励，有百分号时按百分比发放奖励，充值活动有效',
  `times` int(11) DEFAULT '0' COMMENT '可领奖次数',
  `day_times` int(11) DEFAULT NULL COMMENT '每天参与次数',
  `day_times_type` tinyint(4) DEFAULT '0' COMMENT '0不区分触发类型，1区分触发类型',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `extra` text COMMENT '扩展信息',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='活动表';

-- ----------------------------
-- Table structure for qd_activitys_record
-- ----------------------------
DROP TABLE IF EXISTS `qd_activitys_record`;
CREATE TABLE `qd_activitys_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_xuni` tinyint(1) DEFAULT '0' COMMENT '虚拟用户',
  `activity_id` int(11) DEFAULT '0' COMMENT '活动id',
  `user_id` int(11) DEFAULT '0' COMMENT '用户id',
  `from_user_id` int(11) DEFAULT '0' COMMENT '来自用户id',
  `befor_num` decimal(20,4) DEFAULT '0.0000' COMMENT '奖励之前金额',
  `reward` decimal(20,4) unsigned DEFAULT NULL COMMENT '奖励',
  `reward_type` tinyint(1) DEFAULT '1' COMMENT '1货币，2积分',
  `trigger_type` int(11) DEFAULT '0' COMMENT '触发类型',
  `status` tinyint(1) DEFAULT '1' COMMENT '0待领取，1已领取',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_userid` (`user_id`) USING BTREE,
  KEY `idx_from_userid` (`from_user_id`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='活动记录表';

-- ----------------------------
-- Table structure for qd_agent
-- ----------------------------
DROP TABLE IF EXISTS `qd_agent`;
CREATE TABLE `qd_agent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_uid` int(11) NOT NULL DEFAULT '0' COMMENT '代理用户ID',
  `bind_front_uid` int(11) NOT NULL DEFAULT '0' COMMENT '绑定的前台会员ID',
  `is_disable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否禁用 0-正常 1-禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `agentuid` (`agent_uid`) USING BTREE,
  KEY `bind_front_uid` (`bind_front_uid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='代理信息表';

-- ----------------------------
-- Table structure for qd_articles
-- ----------------------------
DROP TABLE IF EXISTS `qd_articles`;
CREATE TABLE `qd_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `lang` varchar(30) DEFAULT 'zh-cn' COMMENT '语言',
  `type` varchar(50) DEFAULT 'article' COMMENT '文章类型',
  `title` varchar(155) NOT NULL DEFAULT '' COMMENT '文章标题',
  `url` varchar(255) DEFAULT '' COMMENT '链接',
  `is_nav` tinyint(1) DEFAULT '0' COMMENT '是否首页菜单',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `thumb` varchar(255) DEFAULT '' COMMENT '缩略图',
  `content` text COMMENT '文章内容',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(10) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for qd_banks
-- ----------------------------
DROP TABLE IF EXISTS `qd_banks`;
CREATE TABLE `qd_banks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(255) DEFAULT 'zh-cn' COMMENT '语言',
  `name` varchar(255) DEFAULT '' COMMENT '银行名称',
  `logo` varchar(255) DEFAULT '' COMMENT 'logo',
  `bank_code` varchar(255) DEFAULT '' COMMENT '银行编码',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  `bank_number_rule` varchar(50) DEFAULT '' COMMENT '银行卡规则',
  `extra` varchar(255) DEFAULT NULL COMMENT '额外参数',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='用户提现支持的银行表';

-- ----------------------------
-- Table structure for qd_banner
-- ----------------------------
DROP TABLE IF EXISTS `qd_banner`;
CREATE TABLE `qd_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `lang` varchar(30) DEFAULT 'zh-cn' COMMENT '语言',
  `name` varchar(155) NOT NULL DEFAULT '' COMMENT 'banner标题',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转链接',
  `pic` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '图片',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(10) DEFAULT NULL COMMENT '发布时间',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='文章';

-- ----------------------------
-- Table structure for qd_collect
-- ----------------------------
DROP TABLE IF EXISTS `qd_collect`;
CREATE TABLE `qd_collect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for qd_comment_text
-- ----------------------------
DROP TABLE IF EXISTS `qd_comment_text`;
CREATE TABLE `qd_comment_text` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(255) DEFAULT '',
  `create_time` timestamp NULL DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='评论文字表';

-- ----------------------------
-- Table structure for qd_day_report
-- ----------------------------
DROP TABLE IF EXISTS `qd_day_report`;
CREATE TABLE `qd_day_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `stat_date` date DEFAULT NULL COMMENT '日期',
  `recharge` decimal(20,4) DEFAULT NULL COMMENT '充值总额',
  `withdraw` decimal(20,4) DEFAULT NULL COMMENT '提现总额',
  `recharge_user` int(11) DEFAULT '0' COMMENT '充值人数',
  `withdraw_user` int(11) DEFAULT '0' COMMENT '提现人数',
  `first_recharge_user` int(11) DEFAULT '0' COMMENT '首充人数',
  `online_user` int(11) DEFAULT '0' COMMENT '在线人数',
  `order_user` int(11) DEFAULT '0' COMMENT '抢单人数',
  `wr_diff` decimal(20,4) DEFAULT '0.0000' COMMENT '充提差额',
  `order_price` decimal(20,4) DEFAULT NULL COMMENT '抢单商品总额',
  `order_gray` decimal(20,4) DEFAULT NULL COMMENT '佣金总额',
  `rebate` decimal(20,4) DEFAULT NULL COMMENT '返佣总额',
  `manual_money` decimal(20,4) DEFAULT NULL COMMENT '手动赠送总额',
  `user_money` decimal(20,4) DEFAULT NULL COMMENT '用户余额',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='日统计报表';

-- ----------------------------
-- Table structure for qd_domain
-- ----------------------------
DROP TABLE IF EXISTS `qd_domain`;
CREATE TABLE `qd_domain` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) DEFAULT '' COMMENT '域名',
  `title` varchar(255) DEFAULT '' COMMENT '网站标题',
  `logo` varchar(255) DEFAULT '' COMMENT 'logo',
  `lang` varchar(30) DEFAULT '' COMMENT '语言',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态',
  `is_bind` tinyint(1) DEFAULT '0' COMMENT '是否已经绑定',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='域名管理';

-- ----------------------------
-- Table structure for qd_faker_datas
-- ----------------------------
DROP TABLE IF EXISTS `qd_faker_datas`;
CREATE TABLE `qd_faker_datas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `fristcz` int(11) DEFAULT NULL COMMENT '首充人数',
  `nextcount` int(11) DEFAULT NULL COMMENT '直推人数',
  `teamcount` int(11) DEFAULT NULL COMMENT '团队人数',
  `teamgray` varchar(255) DEFAULT '0' COMMENT '当天团队收益',
  `total_teamgray` varchar(255) DEFAULT '' COMMENT '团队总收益',
  `newcount` int(11) DEFAULT NULL COMMENT '新增人数',
  `nowcount` int(11) DEFAULT NULL COMMENT '活跃人数',
  `one_lv_users` text COMMENT '一级会员,账号,余额,电话,注册',
  `two_lv_users` text COMMENT '2级会员;账号,余额,电话,注册',
  `three_lv_users` text COMMENT '3级会员;账号,余额,电话,注册',
  `money_log` text COMMENT '账户明细，操作金额，类型，时间',
  `recharge_log` text COMMENT '充值明细，充值金额，时间',
  `cash_log` text COMMENT '提现记录，提现金额，卡号，时间',
  `user_money` varchar(255) DEFAULT '0.00' COMMENT '用户余额',
  `today_self_gray` varchar(255) DEFAULT '0' COMMENT '今日抢单佣金',
  `today_qiangdan_num` int(11) DEFAULT '0' COMMENT '今日抢单数量',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for qd_goods
-- ----------------------------
DROP TABLE IF EXISTS `qd_goods`;
CREATE TABLE `qd_goods` (
  `goods_id` int(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品id',
  `lang` varchar(30) DEFAULT 'zh-cn' COMMENT '语言',
  `level_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联会员等级',
  `cate_id` int(11) DEFAULT '0' COMMENT '分类ID',
  `goods_name` varchar(255) NOT NULL DEFAULT '' COMMENT '商品名称',
  `pic` varchar(255) NOT NULL DEFAULT '' COMMENT '商品上传原始图',
  `main_pic` varchar(1000) DEFAULT '' COMMENT '商品主图',
  `shop_price` decimal(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '实际价格',
  `retail_price` decimal(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '拼单价格',
  `return_bonus` decimal(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '佣金',
  `content` text COMMENT '手机端商品详情',
  `order_number` tinyint(4) NOT NULL DEFAULT '2' COMMENT '最低拼单人数限制',
  `sort` int(10) unsigned NOT NULL DEFAULT '50' COMMENT '商品排序',
  `status` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`goods_id`) USING BTREE,
  KEY `last_update` (`update_time`) USING BTREE,
  KEY `sort_order` (`sort`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='商品数据表';

-- ----------------------------
-- Table structure for qd_goods_attr
-- ----------------------------
DROP TABLE IF EXISTS `qd_goods_attr`;
CREATE TABLE `qd_goods_attr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) DEFAULT '' COMMENT '属性名称',
  `shop_price` decimal(20,2) DEFAULT '0.00' COMMENT '实际价格',
  `retail_price` decimal(20,2) DEFAULT '0.00' COMMENT '拼单价格',
  `return_bonus` decimal(20,0) DEFAULT '0' COMMENT '佣金',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for qd_goods_cate
-- ----------------------------
DROP TABLE IF EXISTS `qd_goods_cate`;
CREATE TABLE `qd_goods_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '' COMMENT '商品分类名称',
  `pic` varchar(255) DEFAULT '' COMMENT '分类图片',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='商品分类表';

-- ----------------------------
-- Table structure for qd_goods_comment
-- ----------------------------
DROP TABLE IF EXISTS `qd_goods_comment`;
CREATE TABLE `qd_goods_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `avatar` int(11) DEFAULT '0' COMMENT '用户头像',
  `nick_name` varchar(100) DEFAULT '' COMMENT '用户昵称',
  `goods_id` int(11) DEFAULT '0' COMMENT '商品ID',
  `order_id` int(11) DEFAULT '0' COMMENT '订单ID',
  `text` varchar(255) DEFAULT '' COMMENT '评论',
  `rate` decimal(10,2) DEFAULT '5.00' COMMENT '评分',
  `create_time` timestamp NULL DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='商品评论表';

-- ----------------------------
-- Table structure for qd_grab_order
-- ----------------------------
DROP TABLE IF EXISTS `qd_grab_order`;
CREATE TABLE `qd_grab_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `goods_id` int(11) DEFAULT '0' COMMENT '商品ID',
  `attr_id` int(11) DEFAULT '0' COMMENT '商品属性ID',
  `status` tinyint(1) DEFAULT '0' COMMENT '0 待处理， 1已处理',
  `create_time` timestamp NULL DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='系统分配抢单表';

-- ----------------------------
-- Table structure for qd_home_user
-- ----------------------------
DROP TABLE IF EXISTS `qd_home_user`;
CREATE TABLE `qd_home_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(30) COLLATE utf8_bin DEFAULT 'zh-cn' COMMENT '语言',
  `user_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '用户名',
  `nick_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '昵称',
  `true_name` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '真实姓名',
  `alipay` varchar(255) COLLATE utf8_bin DEFAULT '' COMMENT '支付宝',
  `mobile` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '手机',
  `password` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '密码',
  `paypwd` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '提现密码',
  `avatar` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '头像',
  `user_money` decimal(20,4) DEFAULT NULL,
  `tyj_money` decimal(20,4) DEFAULT NULL,
  `frozen_money` decimal(20,4) DEFAULT NULL,
  `total_money` decimal(20,4) DEFAULT NULL,
  `points` decimal(20,4) DEFAULT '100.0000' COMMENT '用户积分',
  `level_id` int(11) NOT NULL DEFAULT '0' COMMENT '等级id',
  `bank_code` varchar(100) COLLATE utf8_bin DEFAULT '',
  `bank_name` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '银行',
  `bank_number` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '银行账号',
  `bank_kh` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '开户行',
  `bank_address` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '支行地址',
  `qqnum` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'qq',
  `login_times` int(11) NOT NULL DEFAULT '0' COMMENT '登陆次数',
  `last_login_ip` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `last_login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `invite_code` varchar(30) CHARACTER SET utf8 NOT NULL DEFAULT '0' COMMENT '邀请码',
  `status` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `leader1` int(10) NOT NULL DEFAULT '0',
  `leader2` int(10) NOT NULL DEFAULT '0',
  `leader3` int(10) NOT NULL DEFAULT '0',
  `leader4` int(10) NOT NULL DEFAULT '0',
  `leader5` int(10) NOT NULL DEFAULT '0',
  `leader6` int(10) NOT NULL DEFAULT '0',
  `leader7` int(10) NOT NULL DEFAULT '0',
  `is_xuni` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '虚拟用户',
  `is_rec` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐',
  `is_demo` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否是DEMO',
  `ip` varchar(100) COLLATE utf8_bin DEFAULT '' COMMENT '注册IP',
  `login_key` varchar(255) COLLATE utf8_bin DEFAULT '' COMMENT '登录唯一key',
  `tree_id` varchar(1000) COLLATE utf8_bin DEFAULT '' COMMENT '上下级树',
  `usdt_type` varchar(30) COLLATE utf8_bin DEFAULT '' COMMENT 'USDT地址类型',
  `usdt` varchar(255) COLLATE utf8_bin DEFAULT '' COMMENT 'USDT地址',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `cp_id` int(11) DEFAULT '0' COMMENT 'CP ID',
  `pd_num` int(10) unsigned DEFAULT '0' COMMENT '拼单次数限制',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_mobile` (`mobile`) USING BTREE COMMENT '登录账号唯一',
  KEY `idx_is_xuni` (`is_xuni`) USING BTREE,
  KEY `idx_tree_id` (`tree_id`) USING BTREE,
  KEY `idx_leader1` (`leader1`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for qd_home_user_address
-- ----------------------------
DROP TABLE IF EXISTS `qd_home_user_address`;
CREATE TABLE `qd_home_user_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `consignee` varchar(60) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '收货人',
  `mobile` varchar(60) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '手机',
  `area` varchar(11) COLLATE utf8_bin NOT NULL DEFAULT '0' COMMENT '地区',
  `address` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '密码',
  `zipcode` varchar(60) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '邮政编码',
  `is_default` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否是默认地址',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '头像',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for qd_home_user_level
-- ----------------------------
DROP TABLE IF EXISTS `qd_home_user_level`;
CREATE TABLE `qd_home_user_level` (
  `level_id` smallint(4) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `level_name` varchar(30) NOT NULL DEFAULT '' COMMENT '头衔名称',
  `level_txt` varchar(20) NOT NULL DEFAULT '' COMMENT '通道名称',
  `users` int(11) DEFAULT '0' COMMENT '升级会员数',
  `price` int(11) DEFAULT '0' COMMENT '升级充值金额',
  `dannum` int(10) NOT NULL DEFAULT '0' COMMENT '最高抢单数量',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '审核通过，1待审核，2未通过',
  `income_rate` int(11) DEFAULT '0' COMMENT '千分比',
  `qd_rate` int(11) DEFAULT '3' COMMENT '千分比',
  `img` varchar(255) DEFAULT '' COMMENT '图片',
  `color` varchar(255) DEFAULT '' COMMENT '颜色',
  `is_free` int(2) NOT NULL DEFAULT '0' COMMENT '是否是免费商城',
  `sort` int(8) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_buy` tinyint(1) DEFAULT '0' COMMENT '是否需要购买该等级',
  `money` decimal(20,4) unsigned DEFAULT '0.0000' COMMENT '购买价格',
  `min_amount` int(10) unsigned DEFAULT '0' COMMENT '抢单最低价格',
  `max_amount` int(10) unsigned DEFAULT '0' COMMENT '抢单最高价格',
  `setting` text COMMENT '等级设置',
  PRIMARY KEY (`level_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for qd_home_user_login
-- ----------------------------
DROP TABLE IF EXISTS `qd_home_user_login`;
CREATE TABLE `qd_home_user_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户id',
  `ip` varchar(255) DEFAULT '' COMMENT 'IP',
  `create_time` int(11) DEFAULT NULL COMMENT '登录时间',
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `create_time` (`create_time`) USING BTREE,
  KEY `ip` (`ip`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='用户登录记录表';

-- ----------------------------
-- Table structure for qd_lang
-- ----------------------------
DROP TABLE IF EXISTS `qd_lang`;
CREATE TABLE `qd_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '',
  `lang` varchar(255) DEFAULT '',
  `remark` varchar(255) DEFAULT '',
  `is_default` tinyint(1) DEFAULT '0' COMMENT '默认地区',
  `is_default_lang` tinyint(1) DEFAULT '0' COMMENT '默认语言',
  `flag` varchar(255) DEFAULT '' COMMENT '国旗',
  `status` tinyint(1) DEFAULT '1',
  `create_time` timestamp NULL DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for qd_lang_item_detail
-- ----------------------------
DROP TABLE IF EXISTS `qd_lang_item_detail`;
CREATE TABLE `qd_lang_item_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `text` varchar(2000) DEFAULT '',
  `create_time` timestamp NULL DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for qd_lang_items
-- ----------------------------
DROP TABLE IF EXISTS `qd_lang_items`;
CREATE TABLE `qd_lang_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(1000) DEFAULT '',
  `create_time` timestamp NULL DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for qd_message
-- ----------------------------
DROP TABLE IF EXISTS `qd_message`;
CREATE TABLE `qd_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `content` varchar(1000) DEFAULT '' COMMENT '消息内容',
  `keyword` varchar(255) DEFAULT '',
  `create_time` datetime DEFAULT NULL COMMENT '发送消息时间',
  `ip` varchar(30) DEFAULT '' COMMENT '用户IP',
  `master_id` int(11) DEFAULT '0' COMMENT '管理员ID，0时为玩家发送的消息，大于0时为客服回复玩家的消息',
  `reply_time` datetime DEFAULT NULL COMMENT '回复时间',
  `read` tinyint(1) DEFAULT '0' COMMENT '是否已读0未读，1已读',
  `source_url` varchar(255) NOT NULL DEFAULT '' COMMENT '来源链接',
  `browser` varchar(255) DEFAULT '' COMMENT '浏览器信息',
  `type` tinyint(1) DEFAULT '0' COMMENT '消息类型',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='客服消息表';

-- ----------------------------
-- Table structure for qd_messagefast
-- ----------------------------
DROP TABLE IF EXISTS `qd_messagefast`;
CREATE TABLE `qd_messagefast` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chat_id` int(10) NOT NULL DEFAULT '0' COMMENT '消息id',
  `content` text NOT NULL COMMENT '回复内容',
  `title` varchar(255) DEFAULT NULL,
  `auto_reply` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否自动回复 0 否 1 是',
  `keyword` varchar(225) DEFAULT '' COMMENT '关键词',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用 0 否 1是',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数',
  `master_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布人id',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for qd_messagefilter
-- ----------------------------
DROP TABLE IF EXISTS `qd_messagefilter`;
CREATE TABLE `qd_messagefilter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(30) NOT NULL DEFAULT '' COMMENT '关键词',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for qd_money_log
-- ----------------------------
DROP TABLE IF EXISTS `qd_money_log`;
CREATE TABLE `qd_money_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `is_xuni` tinyint(1) DEFAULT '0' COMMENT '是否虚拟用户',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `order_id` int(10) NOT NULL DEFAULT '0' COMMENT '来自订单ID',
  `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `from_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来自会员ID',
  `remarks` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `money` decimal(20,4) DEFAULT NULL COMMENT '佣金',
  `user_money` decimal(20,4) DEFAULT NULL COMMENT '用户金额',
  `type` tinyint(5) NOT NULL DEFAULT '0' COMMENT '类型',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `create_time_idx` (`create_time`) USING BTREE,
  KEY `idx_money` (`money`) USING BTREE,
  KEY `idx_type` (`type`) USING BTREE,
  KEY `idx_user_id_type_create_time` (`user_id`,`type`,`create_time`) USING BTREE,
  KEY `idx_user_id_type_money` (`user_id`,`type`,`money`) USING BTREE,
  KEY `is_xuni` (`is_xuni`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='佣金记录表';

-- ----------------------------
-- Table structure for qd_node
-- ----------------------------
DROP TABLE IF EXISTS `qd_node`;
CREATE TABLE `qd_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_name` varchar(155) NOT NULL DEFAULT '' COMMENT '节点名称',
  `control_name` varchar(155) NOT NULL DEFAULT '' COMMENT '控制器名',
  `action_name` varchar(155) NOT NULL DEFAULT '' COMMENT '方法名',
  `is_menu` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否是菜单项 1不是 2是',
  `type_id` int(11) NOT NULL DEFAULT '0' COMMENT '父级节点id',
  `style` varchar(155) DEFAULT '' COMMENT '菜单样式',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for qd_notice
-- ----------------------------
DROP TABLE IF EXISTS `qd_notice`;
CREATE TABLE `qd_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `lang` varchar(30) DEFAULT 'zh-cn' COMMENT '语言',
  `content` text COMMENT '文章内容',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(10) DEFAULT '0' COMMENT '头像',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for qd_operation_log
-- ----------------------------
DROP TABLE IF EXISTS `qd_operation_log`;
CREATE TABLE `qd_operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT '',
  `action` varchar(255) DEFAULT NULL,
  `content` varchar(2000) DEFAULT '',
  `ip` varchar(255) DEFAULT '',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='操作日志记录表';

-- ----------------------------
-- Table structure for qd_order
-- ----------------------------
DROP TABLE IF EXISTS `qd_order`;
CREATE TABLE `qd_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_xuni` tinyint(1) NOT NULL DEFAULT '0' COMMENT '虚拟用户',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `level_id` int(11) NOT NULL DEFAULT '0' COMMENT '等级ID',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '拼单父级订单ID',
  `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `attr_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品属性ID',
  `shop_price` decimal(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '商品实际价格',
  `retail_price` decimal(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '拼单价格',
  `return_bonus` decimal(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '佣金',
  `order_sn` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '订单编号',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '拼单状态 1 进行中 2 已完成 3 已失效',
  `paid_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '付款状态 1 待支付 2 已完成 3 已失效',
  `repurchase_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '回购状态，1待回购，2回购申请，3回购成功',
  `is_demo` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否是DEMO',
  `specify_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '指定用户拼单',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `idx_status_time` (`paid_status`,`create_time`) USING BTREE,
  KEY `is_xuni` (`is_xuni`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for qd_order_log
-- ----------------------------
DROP TABLE IF EXISTS `qd_order_log`;
CREATE TABLE `qd_order_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `order_id` int(10) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `remark` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for qd_paylist
-- ----------------------------
DROP TABLE IF EXISTS `qd_paylist`;
CREATE TABLE `qd_paylist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(30) DEFAULT 'zh-cn' COMMENT '语言',
  `type` tinyint(1) DEFAULT '0' COMMENT '0支付宝，1银行卡',
  `account` varchar(255) DEFAULT NULL COMMENT '收款账号',
  `name` varchar(255) DEFAULT NULL COMMENT '姓名',
  `bank_name` varchar(255) DEFAULT NULL COMMENT '银行名称',
  `bank_address` varchar(255) DEFAULT NULL COMMENT '支行地址',
  `logo` varchar(255) DEFAULT '' COMMENT 'logo',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态',
  `sort` int(1) DEFAULT '0' COMMENT '排序',
  `min_money` int(11) DEFAULT '0' COMMENT '最小金额',
  `max_money` int(11) DEFAULT '0' COMMENT '最大金额',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='收款账号表';

-- ----------------------------
-- Table structure for qd_recharge
-- ----------------------------
DROP TABLE IF EXISTS `qd_recharge`;
CREATE TABLE `qd_recharge` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) DEFAULT '' COMMENT '订单号',
  `is_xuni` tinyint(1) DEFAULT '0' COMMENT '是否虚拟用户',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `money` decimal(20,4) NOT NULL DEFAULT '0.0000' COMMENT '充值金额;法币',
  `exchange_rate` decimal(20,8) NOT NULL DEFAULT '1.00000000' COMMENT 'USDT兑法币汇率',
  `car_num` varchar(100) NOT NULL DEFAULT '' COMMENT '充值卡号',
  `type` tinyint(5) NOT NULL DEFAULT '0' COMMENT '类型',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1 待支付 2 通过 3 失效',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `pay_account` varchar(250) DEFAULT '' COMMENT '付款账户',
  `user_note` varchar(250) DEFAULT '' COMMENT '用户备注',
  `pay_time` datetime DEFAULT NULL COMMENT '付款时间',
  `trade_no` varchar(50) DEFAULT NULL COMMENT '第三方订单号',
  `extra` varchar(255) DEFAULT '' COMMENT '扩展数据字段',
  `pay_id` int(11) DEFAULT '0' COMMENT '收款ID',
  `img` varchar(255) DEFAULT '' COMMENT '付款凭证',
  `ip` varchar(255) DEFAULT '' COMMENT '操作IP',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='充值记录表';

-- ----------------------------
-- Table structure for qd_role
-- ----------------------------
DROP TABLE IF EXISTS `qd_role`;
CREATE TABLE `qd_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `role_name` varchar(155) NOT NULL DEFAULT '' COMMENT '角色名称',
  `rule` varchar(1000) DEFAULT NULL COMMENT '权限节点数据',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for qd_score_log
-- ----------------------------
DROP TABLE IF EXISTS `qd_score_log`;
CREATE TABLE `qd_score_log` (
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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `score_audit_time` (`audit_time`) USING BTREE,
  KEY `excharge_userid` (`excharge_userid`) USING BTREE,
  KEY `excharge_type` (`excharge_type`) USING BTREE,
  KEY `excharge_time` (`excharge_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分记录表';

-- ----------------------------
-- Table structure for qd_setting
-- ----------------------------
DROP TABLE IF EXISTS `qd_setting`;
CREATE TABLE `qd_setting` (
  `name` varchar(50) NOT NULL COMMENT '名称',
  `field` varchar(50) NOT NULL COMMENT '字段名',
  `value` text COMMENT '值',
  `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
  `group_id` int(11) DEFAULT '0' COMMENT '分组ID',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `type` varchar(50) DEFAULT '' COMMENT '字段类型',
  PRIMARY KEY (`name`,`field`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统配置表';

-- ----------------------------
-- Table structure for qd_turntable_count
-- ----------------------------
DROP TABLE IF EXISTS `qd_turntable_count`;
CREATE TABLE `qd_turntable_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `from_user_id` int(11) DEFAULT '0' COMMENT '来自用户ID',
  `activity_id` int(11) DEFAULT '0' COMMENT '活动ID',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `num` int(11) DEFAULT '1' COMMENT '可用抽奖次数',
  `total` int(11) DEFAULT '1' COMMENT '总次数',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for qd_turntable_prize
-- ----------------------------
DROP TABLE IF EXISTS `qd_turntable_prize`;
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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='转盘奖品表';

-- ----------------------------
-- Table structure for qd_turntable_prize_log
-- ----------------------------
DROP TABLE IF EXISTS `qd_turntable_prize_log`;
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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='转盘抽奖记录表';

-- ----------------------------
-- Table structure for qd_upload
-- ----------------------------
DROP TABLE IF EXISTS `qd_upload`;
CREATE TABLE `qd_upload` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'UID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '文件路径',
  `ext` char(4) NOT NULL DEFAULT '' COMMENT '文件类型',
  `size` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `md5` char(32) DEFAULT '' COMMENT '文件md5',
  `sha1` char(40) DEFAULT '' COMMENT '文件sha1编码',
  `download` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上传时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='文件上传表';

-- ----------------------------
-- Table structure for qd_user
-- ----------------------------
DROP TABLE IF EXISTS `qd_user`;
CREATE TABLE `qd_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '密码',
  `head` varchar(255) COLLATE utf8_bin DEFAULT '' COMMENT '头像',
  `login_times` int(11) NOT NULL DEFAULT '0' COMMENT '登陆次数',
  `last_login_ip` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `last_login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `real_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '真实姓名',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `role_id` int(11) NOT NULL DEFAULT '1' COMMENT '用户角色id',
  `authkey` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `message_type` tinyint(1) DEFAULT '-1' COMMENT '客服类型',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for qd_user_report
-- ----------------------------
DROP TABLE IF EXISTS `qd_user_report`;
CREATE TABLE `qd_user_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `is_xuni` tinyint(1) DEFAULT '0' COMMENT '是否虚拟',
  `stat_date` date DEFAULT NULL COMMENT '统计日期',
  `type` int(11) DEFAULT NULL COMMENT '账变类型',
  `total` decimal(20,4) DEFAULT '0.0000' COMMENT '总额',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_user_id` (`user_id`) USING BTREE,
  KEY `idx_is_xuni` (`is_xuni`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for qd_withdraw_limit
-- ----------------------------
DROP TABLE IF EXISTS `qd_withdraw_limit`;
CREATE TABLE `qd_withdraw_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态',
  `remark` varchar(255) DEFAULT '' COMMENT '说明',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='用户提现限制表';

-- ----------------------------
-- Table structure for qd_withdrawals
-- ----------------------------
DROP TABLE IF EXISTS `qd_withdrawals`;
CREATE TABLE `qd_withdrawals` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '提现申请表',
  `order_id` varchar(50) DEFAULT '' COMMENT '订单号',
  `is_xuni` tinyint(1) DEFAULT '0' COMMENT '是否虚拟用户',
  `user_id` int(11) DEFAULT '0' COMMENT '用户id',
  `money` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '提现金额;单位法币',
  `exchange_rate` decimal(20,8) NOT NULL DEFAULT '1.00000000' COMMENT 'USDT兑法币汇率',
  `create_time` int(11) DEFAULT '0' COMMENT '申请时间',
  `check_time` int(11) DEFAULT '0' COMMENT '审核时间',
  `taxfee` decimal(10,2) DEFAULT NULL COMMENT '税收手续费',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '' COMMENT '提现备注',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态：',
  `true_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '真实姓名',
  `bank_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '银行',
  `bank_number` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '银行账号',
  `bank_kh` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '开户行',
  `bank_address` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '支行地址',
  `type` tinyint(1) DEFAULT '1' COMMENT '0支付宝，1银行卡',
  `trade_no` varchar(50) DEFAULT NULL COMMENT '第三方订单号',
  `pay_time` datetime DEFAULT NULL COMMENT '付款时间',
  `channel` varchar(50) DEFAULT NULL COMMENT '代付渠道',
  `ip` varchar(255) NOT NULL DEFAULT '' COMMENT '操作IP',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='提现申请表';

-- ----------------------------
-- Table structure for qd_yuebao
-- ----------------------------
DROP TABLE IF EXISTS `qd_yuebao`;
CREATE TABLE `qd_yuebao` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `is_xuni` tinyint(1) DEFAULT '0' COMMENT '是否虚拟用户',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `money` decimal(20,4) DEFAULT NULL COMMENT '佣金',
  `rate` decimal(20,4) DEFAULT NULL COMMENT '利率',
  `days` int(10) NOT NULL DEFAULT '0' COMMENT '天数',
  `principal` decimal(20,4) NOT NULL COMMENT '本金',
  `gray` decimal(20,4) unsigned DEFAULT NULL COMMENT '利息',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1 已转入 2 已转出 3 已到期',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `out_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '取出时间',
  `is_demo` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否是DEMO',
  `redemption_fee` decimal(20,4) DEFAULT '0.0000' COMMENT '赎回手续费',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='佣金记录表';

-- ----------------------------
-- Table structure for qd_yuebao_log
-- ----------------------------
DROP TABLE IF EXISTS `qd_yuebao_log`;
CREATE TABLE `qd_yuebao_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rec_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '余额宝ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `gray` decimal(20,4) DEFAULT NULL COMMENT '收益',
  `get_time` int(10) NOT NULL DEFAULT '0' COMMENT '收益日期',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='佣金记录表';

-- ----------------------------
-- Table structure for qd_yuebao_rate
-- ----------------------------
DROP TABLE IF EXISTS `qd_yuebao_rate`;
CREATE TABLE `qd_yuebao_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `rate` decimal(20,4) unsigned DEFAULT NULL COMMENT '利率',
  `days` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '天数',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `min_amount` int(11) DEFAULT '0' COMMENT '最低购买金额',
  `max_amount` int(11) DEFAULT '0' COMMENT '最大购买金额',
  `redemption_fee` decimal(20,4) DEFAULT '0.0000' COMMENT '赎回手续费',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

SET FOREIGN_KEY_CHECKS = 1;
