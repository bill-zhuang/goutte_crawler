/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-04-14 19:37:11
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crawl_zuoyebao
-- ----------------------------
DROP TABLE IF EXISTS `crawl_zuoyebao`;
CREATE TABLE `crawl_zuoyebao` (
  `cz_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cg_id` int(10) unsigned NOT NULL,
  `cc_id` int(10) unsigned NOT NULL,
  `cz_name` text NOT NULL,
  `cz_choice` text NOT NULL,
  `cz_answer` varchar(8) NOT NULL DEFAULT '',
  `cz_tag` varchar(512) NOT NULL,
  `cz_url` varchar(255) NOT NULL DEFAULT '',
  `cz_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `cz_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cz_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
