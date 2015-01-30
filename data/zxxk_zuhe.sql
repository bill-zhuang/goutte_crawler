/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-01-30 14:10:27
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for zxxk_zuhe
-- ----------------------------
DROP TABLE IF EXISTS `zxxk_zuhe`;
CREATE TABLE `zxxk_zuhe` (
  `zz_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pr_id` int(255) unsigned NOT NULL DEFAULT '0' COMMENT 'table province',
  `zz_title` varchar(255) NOT NULL DEFAULT '',
  `zz_download_url` varchar(255) NOT NULL DEFAULT '',
  `zz_file_type` varchar(50) NOT NULL DEFAULT '',
  `zz_content` text NOT NULL,
  `zz_url` varchar(255) NOT NULL DEFAULT '',
  `zz_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `zz_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `zz_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`zz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
