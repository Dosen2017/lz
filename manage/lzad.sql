/*
 Navicat Premium Data Transfer

 Source Server         : 乐众内网
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : rm-wz9367w216k67j0km9o.mysql.rds.aliyuncs.com:3306
 Source Schema         : lzad

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 20/06/2020 15:34:41
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ad_gdt_click
-- ----------------------------
DROP TABLE IF EXISTS `ad_gdt_click`;
CREATE TABLE `ad_gdt_click`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标识ID',
  `muid` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '设备ID',
  `click_time` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '点击时间',
  `click_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '点击ID',
  `appid` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '应用ID',
  `advertiser_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '广告账号ID',
  `app_type` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'app类型',
  `android_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '安卓IDMD5',
  `mac` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'MAC',
  `ip` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'IP',
  `user_agent` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'USER_AGENT',
  `adgroup_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '广告ID',
  `device_os_type` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '设备系统',
  `request_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '请求ID',
  `oaid` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '安卓设备ID(补充)',
  `callback_params` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '参数列表',
  `add_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `i_click_id`(`click_id`) USING BTREE,
  INDEX `i_adgroup_id`(`adgroup_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '广点通点击事件日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ad_gdt_click
-- ----------------------------

-- ----------------------------
-- Table structure for ad_gdt_click_error
-- ----------------------------
DROP TABLE IF EXISTS `ad_gdt_click_error`;
CREATE TABLE `ad_gdt_click_error`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标识ID',
  `muid` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '设备ID',
  `click_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '点击ID',
  `appid` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '应用ID',
  `adgroup_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '广告ID',
  `request_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '请求ID',
  `oaid` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '安卓设备ID(补充)',
  `callback_params` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '参数列表',
  `add_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `i_click_id`(`click_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '广点通点击事件错误日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ad_gdt_click_error
-- ----------------------------

-- ----------------------------
-- Table structure for ad_gdt_token
-- ----------------------------
DROP TABLE IF EXISTS `ad_gdt_token`;
CREATE TABLE `ad_gdt_token`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标识ID',
  `client_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '应用ID',
  `client_secret` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '应用 SECRET',
  `access_token` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'TOKEN',
  `refresh_token` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'TOKEN刷新码',
  `access_token_expires_in` int(11) UNSIGNED NULL DEFAULT 0 COMMENT 'TOKEN有效时长',
  `access_token_expires_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT 'TOKEN有效截止时间',
  `refresh_token_expires_in` int(11) UNSIGNED NULL DEFAULT 0 COMMENT 'TOKEN刷新码有效时长',
  `refresh_token_expires_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT 'TOKEN刷新码截止时间',
  `add_time` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '添加时间',
  `update_time` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uni_client_id`(`client_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '广点通TOKEN' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ad_gdt_token
-- ----------------------------

-- ----------------------------
-- Table structure for ad_gdt_trans
-- ----------------------------
DROP TABLE IF EXISTS `ad_gdt_trans`;
CREATE TABLE `ad_gdt_trans`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标识ID',
  `uni_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'MD5唯一值',
  `client_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '应用ID',
  `appid` int(11) UNSIGNED NULL DEFAULT 0 COMMENT 'APP_ID',
  `account_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '账号ID',
  `imei` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'IMEI',
  `idfa` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'IDFA',
  `android_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '安卓ID',
  `mac` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'MAC',
  `oaid` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '安卓ID(补充)',
  `os` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '系统',
  `action_type` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '上报类型',
  `action_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '上报时间',
  `status` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '转化状态',
  `ret_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '返回内容',
  `add_time` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `i_uni_id`(`uni_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '广点通上报流水' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ad_gdt_trans
-- ----------------------------

-- ----------------------------
-- Table structure for ad_tout_click
-- ----------------------------
DROP TABLE IF EXISTS `ad_tout_click`;
CREATE TABLE `ad_tout_click`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标识ID',
  `ads_n` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '广告ID',
  `csite` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '广告投放位置',
  `request_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '请求下发ID',
  `device_n` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设备ID',
  `ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'IP',
  `mac` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'MAC',
  `android_n` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '安卓ID',
  `os` enum('0','1','3') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '3' COMMENT '系统:0=安卓,1=IOS,3=其它',
  `click_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '点击时间',
  `callback` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '回调参数',
  `callback_url` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '回调链接',
  `add_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `i_device_n`(`device_n`) USING BTREE,
  INDEX `i_request_id`(`request_id`) USING BTREE,
  INDEX `i_click_time`(`click_time`) USING BTREE,
  INDEX `i_mac`(`mac`) USING BTREE,
  INDEX `i_android_n`(`android_n`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '头条点击事件日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ad_tout_click
-- ----------------------------

-- ----------------------------
-- Table structure for ad_tout_trans
-- ----------------------------
DROP TABLE IF EXISTS `ad_tout_trans`;
CREATE TABLE `ad_tout_trans`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标识ID',
  `uni_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'MD5唯一值',
  `device_n` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设备ID',
  `android_n` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '安卓ID',
  `ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'IP',
  `mac` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'MAC',
  `os` enum('0','1','3') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '3' COMMENT '系统:0=安卓,1=IOS,3=其它',
  `conv_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '转化时间',
  `event_type` enum('1','2') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '1' COMMENT '时间类型:1=注册,2=付费',
  `status` enum('0','1','2') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '1' COMMENT '上报状态:0=失败,1=成功,2=匹配失败',
  `ret_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '失败信息',
  `add_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `i_uni_id`(`uni_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '头条上报流水' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ad_tout_trans
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
