/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-04-26 10:11:44
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for baidu_avatar
-- ----------------------------
DROP TABLE IF EXISTS `baidu_avatar`;
CREATE TABLE `baidu_avatar` (
  `ba_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ba_imgurl` varchar(512) NOT NULL DEFAULT '',
  `ba_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'status: 1 for valid, 0 for invalid',
  `ba_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ba_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ba_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
