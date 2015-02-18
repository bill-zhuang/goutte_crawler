/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-02-18 12:40:54
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crawl_edudown
-- ----------------------------
DROP TABLE IF EXISTS `crawl_edudown`;
CREATE TABLE `crawl_edudown` (
  `ce_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cg_id` int(10) unsigned NOT NULL,
  `cc_id` int(10) unsigned NOT NULL,
  `ce_title` varchar(255) NOT NULL DEFAULT '',
  `ce_download_url` varchar(255) NOT NULL DEFAULT '',
  `ce_content` text NOT NULL,
  `ce_url` varchar(255) NOT NULL DEFAULT '',
  `ce_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `ce_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ce_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ce_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
