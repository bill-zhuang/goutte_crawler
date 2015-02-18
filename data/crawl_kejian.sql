/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-02-18 12:41:02
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crawl_kejian
-- ----------------------------
DROP TABLE IF EXISTS `crawl_kejian`;
CREATE TABLE `crawl_kejian` (
  `ck_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cg_id` int(10) unsigned NOT NULL,
  `cc_id` int(10) unsigned NOT NULL,
  `ck_title` varchar(255) NOT NULL DEFAULT '',
  `ck_download_url` varchar(255) NOT NULL DEFAULT '',
  `ck_content` text NOT NULL,
  `ck_url` varchar(255) NOT NULL DEFAULT '',
  `ck_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `ck_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ck_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ck_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
