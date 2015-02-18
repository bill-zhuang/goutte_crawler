/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-02-18 12:24:01
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crawl_grade
-- ----------------------------
DROP TABLE IF EXISTS `crawl_grade`;
CREATE TABLE `crawl_grade` (
  `cg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cg_name` varchar(256) NOT NULL DEFAULT '',
  `cg_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cg_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cg_status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
