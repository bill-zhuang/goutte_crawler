/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-03-28 15:52:15
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crawl_fhedu
-- ----------------------------
DROP TABLE IF EXISTS `crawl_fhedu`;
CREATE TABLE `crawl_fhedu` (
  `cf_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cg_id` int(10) unsigned NOT NULL,
  `cc_id` int(10) unsigned NOT NULL,
  `cf_title` varchar(255) NOT NULL DEFAULT '',
  `cf_download_url` varchar(255) NOT NULL DEFAULT '',
  `cf_content` text NOT NULL,
  `cf_url` varchar(255) NOT NULL DEFAULT '',
  `cf_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `cf_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cf_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
