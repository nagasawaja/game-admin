-- MySQL dump 10.13  Distrib 5.7.21, for Linux (x86_64)
--
-- Host: 192.168.42.172    Database: wxzs
-- ------------------------------------------------------
-- Server version	5.6.34-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL COMMENT '登录账号',
  `password` varchar(32) NOT NULL COMMENT '原始密码的md5值',
  `role_id` smallint(5) NOT NULL DEFAULT '0' COMMENT 'admin_role.id',
  `encrypt` varchar(6) NOT NULL COMMENT '密码salt',
  `login_ip` varchar(15) NOT NULL DEFAULT '',
  `login_time` int(10) NOT NULL DEFAULT '0',
  `realname` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_role`
--

DROP TABLE IF EXISTS `admin_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_role` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `rolename` varchar(50) NOT NULL COMMENT '角色名称',
  `description` text NOT NULL COMMENT '描述',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态，1=启用，2=禁用',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rolename` (`rolename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_role_priv`
--

DROP TABLE IF EXISTS `admin_role_priv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_role_priv` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` tinyint(3) unsigned NOT NULL COMMENT '管理员角色id',
  `uri` varchar(64) CHARACTER SET ascii NOT NULL COMMENT '请求路径',
  `status` tinyint(4) NOT NULL COMMENT '假删除标识，1=正常，2=删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_id_uri` (`role_id`,`uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员角色权限表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `coin_products`
--

DROP TABLE IF EXISTS `coin_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coin_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL COMMENT '名称',
  `description` varchar(100) NOT NULL COMMENT '描述',
  `coins` int(11) unsigned NOT NULL COMMENT '币数',
  `extra_coins` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '额外赠送币数',
  `price` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '人民币(分)',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='充值套餐';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recharge_orders`
--

DROP TABLE IF EXISTS `recharge_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recharge_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trade_no` char(20) CHARACTER SET ascii NOT NULL COMMENT '系统订单号',
  `user_id` int(11) NOT NULL COMMENT 'users.id',
  `product_id` int(11) NOT NULL COMMENT 'coin_products.id',
  `total_fee` int(11) unsigned NOT NULL COMMENT '消费金额：人民币(分)',
  `total_coins` int(11) unsigned NOT NULL COMMENT '获得虚拟币数',
  `status` tinyint(1) NOT NULL DEFAULT '2' COMMENT '订单状态，1=已支付，2=待支付，3=已取消',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `pay_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '支付类型，1=app苹果支付，2=app支付宝，3=支付宝扫码，6=app微信，7=微信扫码，8=微信公众号，9=转账',
  PRIMARY KEY (`id`),
  UNIQUE KEY `trade_no` (`trade_no`),
  KEY `user_id` (`user_id`),
  KEY `create_time` (`create_time`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='充值订单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(32) CHARACTER SET utf8mb4 NOT NULL COMMENT '服务名称',
  `description` text COMMENT '描述',
  `category` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '功能类别：1=同步群发消息,2=群发朋友圈,3=一键转发朋友圈',
  `icon` varchar(255) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1=开发中,2=已上线,3=下线',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='功能服务表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `services_product`
--

DROP TABLE IF EXISTS `services_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(10) unsigned NOT NULL COMMENT 'service.id',
  `coins` int(10) unsigned NOT NULL COMMENT '花费的金币',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '1：启用，2：停用',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '服务产品类型，1：收费，2：免费试用',
  `avail_time` int(10) unsigned NOT NULL COMMENT '开通服务后的有效时间(分钟)',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='服务套餐表'
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `services_trial_setting`
--

DROP TABLE IF EXISTS `services_trial_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services_trial_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL COMMENT 'services.id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否开通试用：1=启用,2=禁用',
  `avail_time` int(11) unsigned NOT NULL DEFAULT '10' COMMENT '服务试用期(分钟)',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='功能服务试用设置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sync_moments_setting`
--

DROP TABLE IF EXISTS `sync_moments_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_moments_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wxid` varchar(32) NOT NULL COMMENT '微信id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态，1=开启，2=关闭',
  `sync_location` tinyint(3) NOT NULL DEFAULT '2' COMMENT '同步位置信息，1=同步，2=不同步',
  `target_wxid` varchar(1024) NOT NULL COMMENT '发送目标微信id，多个则用逗号分隔',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `wxid` (`wxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='群发朋友圈设置(发朋友圈时同时发到其他号)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sync_othermoments_setting`
--

DROP TABLE IF EXISTS `sync_othermoments_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_othermoments_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wxid` varchar(32) NOT NULL COMMENT '微信id',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1=开启，2=关闭',
  `sync_location` tinyint(4) NOT NULL DEFAULT '2' COMMENT '同步位置信息，1=同步，2=不同步',
  `target_wxid` varchar(1024) NOT NULL COMMENT '发送目标微信id，多个则用逗号分隔',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `wxid` (`wxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='一键转发朋友圈(通过收藏功能重发别人的朋友圈)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sync_send_setting`
--

DROP TABLE IF EXISTS `sync_send_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_send_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wxid` varchar(32) NOT NULL COMMENT '微信id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态，1=开启，2=关闭',
  `target_wxid` varchar(1024) NOT NULL COMMENT '发送目标微信id，多个则用逗号分隔',
  `create_time` int(11) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `wxid` (`wxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='多号同步群发消息设置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sync_send_target`
--

DROP TABLE IF EXISTS `sync_send_target`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_send_target` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_id` int(11) unsigned NOT NULL COMMENT 'sync_send_setting.id',
  `wxid` varchar(128) NOT NULL COMMENT '所属微信id，多个则用逗号分隔',
  `target_id` VARCHAR(32) NOT NULL COMMENT '发送目标，group_id或wxid',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '发送目标类型，1=group_id，2=wxid',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态，1=有效，2=无效，3=已经删除',
  `create_time` int(11) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `setting_id` (`setting_id`),
  KEY `target_id` (`target_id`),
  KEY `wxid` (`wxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='多号同步群发消息发送目标';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `test`
--

DROP TABLE IF EXISTS `test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` char(11) NOT NULL,
  `coins` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '虚拟币',
  `register_time` int(11) unsigned NOT NULL,
  `password` varchar(32) NOT NULL COMMENT '原始密码的md5值',
  `encrypt` varchar(6) NOT NULL COMMENT '密码salt',
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_coin_bill`
--

DROP TABLE IF EXISTS `users_coin_bill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_coin_bill` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户id',
  `coins` int(11) unsigned NOT NULL COMMENT '增减金币数',
  `type` tinyint(3) unsigned NOT NULL COMMENT '交易类型，1=充值，2=购买服务消费',
  `symbol` char(1) CHARACTER SET ascii NOT NULL COMMENT '符号，+或-',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `summary` varchar(100) NOT NULL COMMENT '交易摘要',
  `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
  `balance` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '增减后的金币余额',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='虚拟币账单记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_wx_account`
--

DROP TABLE IF EXISTS `users_wx_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_wx_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'users.id',
  `wxid` varchar(32) NOT NULL COMMENT '微信id',
  `wxidfa` varchar(64) CHARACTER SET ascii NOT NULL COMMENT '微信idfa(绑定的设备标识)',
  `wxcoreid` int(11) NOT NULL COMMENT '登录的微信核心模块',
  `remark` varchar(64) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '用户微信备注',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  `login_time` int(10) unsigned DEFAULT '0' COMMENT '最后登录时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态情况 0 正常， 非0 异常/失败',
  `state` tinyint(3) unsigned DEFAULT '9' COMMENT '0(INIT) 初始化客户端, 1(AUTO_AUTH) 自动登录, 2(SEND_QRCODE) 扫码登录, 3(CHECK_QRCODE)检查登录状态, 4(MANUAL_AUTH) 手动登录, 5(MANUAL_SWITCH) 手动登录类型选择, 6(CYCLE) 业务循环工作中，\n	//7（PUSH_LOGIN） 推送ipad登录， 8（CHECK_PUSH_LOGIN） 检测ipad登录状态， 9（EXIT） 退出登录',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idfa` (`wxidfa`),
  KEY `uid` (`user_id`),
  KEY `wxid` (`wxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户关联的微信号';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wx_account`
--

DROP TABLE IF EXISTS `wx_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wx_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wxid` varchar(32) NOT NULL COMMENT '微信id',
  `wxaccount` varchar(32) NOT NULL COMMENT '微信号',
  `nickname` varchar(32) CHARACTER SET utf8mb4 NOT NULL COMMENT '昵称',
  `avatar` varchar(1024) NOT NULL DEFAULT '' COMMENT '头像地址;同步时文件名格式为:md5(原地址)+md5(文件内容)',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `type` tinyint(4) DEFAULT '0' COMMENT '联系人类型，0未知联系人类型，1普通好友，2黑名单（不保存在本表，在wx_contacts.status体现），3公众号，4特殊号，5群聊，6自己（不保存）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `wxid` (`wxid`),
  KEY `wxaccount` (`wxaccount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信号';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wx_contacts`
--

DROP TABLE IF EXISTS `wx_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wx_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_wxid` varchar(32) NOT NULL COMMENT '拥有者微信id',
  `wxid` varchar(32) NOT NULL COMMENT '通讯录微信id',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  `alias` varchar(32) CHARACTER SET utf8mb4 NOT NULL COMMENT '别名',
  `status` tinyint(3) unsigned DEFAULT '1' COMMENT '0异常，1正常，2已经拉黑，3删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unqwxid` (`owner_wxid`,`wxid`),
  KEY `owner_wxid` (`owner_wxid`),
  KEY `wxid` (`wxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信通讯录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wx_core`
--

DROP TABLE IF EXISTS `wx_core`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wx_core` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '主机名',
  `ip` int(10) unsigned NOT NULL COMMENT '主机ip，查询时可用函数inet_ntoa/inet_aton转换',
  `port` mediumint(9) NOT NULL COMMENT '主机端口',
  `account_num` int(10) unsigned NOT NULL COMMENT '已经登录微信账号数量',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态，1=启用，2=禁用',
  `update_time` int(11) NOT NULL COMMENT '修改时间',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `addr` (`ip`,`port`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信核心功能模块主机表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wx_group`
--

DROP TABLE IF EXISTS `wx_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wx_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` varchar(32) NOT NULL COMMENT '微信群id',
  `group_name` varchar(32) CHARACTER SET utf8mb4 NOT NULL COMMENT '微信群名称',
  `group_icon` varchar(1024) NOT NULL DEFAULT '' COMMENT '群图标地址;同步时文件名格式为:md5(原地址)+md5(文件内容)',
  `ownerid` varchar(32) CHARACTER SET ascii DEFAULT '' COMMENT '群主id',
  `maxnum` mediumint(8) unsigned DEFAULT '0' COMMENT '最大人数',
  `members` mediumint(8) unsigned DEFAULT '0' COMMENT '当前群成员数',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信群';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wx_group_members`
--

DROP TABLE IF EXISTS `wx_group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wx_group_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` varchar(32) NOT NULL COMMENT '微信群id',
  `wxid` varchar(32) NOT NULL COMMENT '微信id',
  `nickname` varchar(32) CHARACTER SET utf8mb4 NOT NULL COMMENT '群用户昵称',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  `flag` tinyint(3) unsigned DEFAULT '0',
  `status` tinyint(3) unsigned DEFAULT '1' COMMENT '0异常，1正常，3删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpwxid` (`group_id`,`wxid`),
  KEY `group_id` (`group_id`),
  KEY `wxid` (`wxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信群成员';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wx_services`
--

DROP TABLE IF EXISTS `wx_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wx_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL COMMENT 'services.id',
  `wxid` varchar(32) NOT NULL COMMENT '微信id',
  `start_time` int(11) NOT NULL COMMENT '服务开通时间',
  `end_time` int(11) NOT NULL COMMENT '服务到期时间',
  `buy_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '开通类型：1=购买,2=免费试用',
  `create_time` int(11) NOT NULL COMMENT '服务第一次开通时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `svwxid` (`service_id`,`wxid`),
  KEY `wxid` (`wxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信号开通了的功能服务';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wx_services_buylog`
--

DROP TABLE IF EXISTS `wx_services_buylog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wx_services_buylog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_product_id` int(11) unsigned NOT NULL COMMENT 'service_product.id',
  `user_id` int(11) unsigned NOT NULL COMMENT 'users.id',
  `wxid` varchar(32) NOT NULL COMMENT '微信id',
  `start_time` int(11) unsigned NOT NULL COMMENT '服务开通时间',
  `end_time` int(11) unsigned NOT NULL COMMENT '服务到期时间',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `wxid` (`wxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='功能服务开通日志';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'wxzs'
--

--
-- Dumping routines for database 'wxzs'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-03-09 14:40:51

-- 2018-03-13 add
CREATE TABLE `wxdev_loginlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'users.id',
  `wxid` varchar(32) NOT NULL COMMENT '微信id',
  `wxidfa` varchar(64) CHARACTER SET ascii NOT NULL COMMENT '微信idfa(绑定的设备标识)',
  `wxcoreid` int(11) NOT NULL COMMENT '登录的微信核心模块',
  `add_time` int(11) NOT NULL COMMENT '账号添加时间',
  `login_time` int(10) unsigned DEFAULT '0' COMMENT '最后登录时间',
  `create_time` int(11) NOT NULL COMMENT '日志创建时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态情况 0 正常， 非0 异常/失败',
  `state` tinyint(3) unsigned DEFAULT '9' COMMENT '0(INIT) 初始化客户端, 1(AUTO_AUTH) 自动登录, 2(SEND_QRCODE) 扫码登录, 3(CHECK_QRCODE)检查登录状态, 4(MANUAL_AUTH) 手动登录, 5(MANUAL_SWITCH) 手动登录类型选择, 6(CYCLE) 业务循环工作中，\n	//7（PUSH_LOGIN） 推送ipad登录， 8（CHECK_PUSH_LOGIN） 检测ipad登录状态， 9（EXIT） 退出登录',
  PRIMARY KEY (`id`),
  KEY `wxid` (`wxid`),
  KEY `user_id` (`user_id`),
  KEY `idfa` (`wxidfa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信号登录设备日志';