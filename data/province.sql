/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-01-30 14:10:12
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for province
-- ----------------------------
DROP TABLE IF EXISTS `province`;
CREATE TABLE `province` (
  `pr_id` int(11) NOT NULL AUTO_INCREMENT,
  `pr_name` varchar(128) NOT NULL DEFAULT '',
  `pr_pinyin` varchar(256) NOT NULL DEFAULT '',
  `pr_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 for province, 1 for city',
  `pr_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pr_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pr_status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`pr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
