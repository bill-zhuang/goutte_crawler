/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-01-30 14:31:02
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for g12e
-- ----------------------------
DROP TABLE IF EXISTS `g12e`;
CREATE TABLE `g12e` (
  `g1_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gr_id` int(10) unsigned NOT NULL,
  `co_id` int(10) unsigned NOT NULL,
  `pr_id` int(255) unsigned NOT NULL DEFAULT '0',
  `g1_title` varchar(255) NOT NULL DEFAULT '',
  `g1_download_url` varchar(255) NOT NULL DEFAULT '',
  `g1_content` text NOT NULL,
  `g1_url` varchar(255) NOT NULL DEFAULT '',
  `g1_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `g1_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `g1_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`g1_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
