/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-01-30 14:09:59
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for course
-- ----------------------------
DROP TABLE IF EXISTS `course`;
CREATE TABLE `course` (
  `co_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `co_name` varchar(256) NOT NULL DEFAULT '',
  `co_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `co_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `co_status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`co_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
