/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-02-18 12:24:15
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crawl_aoshu
-- ----------------------------
DROP TABLE IF EXISTS `crawl_aoshu`;
CREATE TABLE `crawl_aoshu` (
  `ca_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cg_id` int(10) unsigned NOT NULL,
  `cc_id` int(10) unsigned NOT NULL,
  `ct_id` int(10) unsigned NOT NULL,
  `ca_title` varchar(255) NOT NULL DEFAULT '',
  `ca_download_url` varchar(255) NOT NULL DEFAULT '',
  `ca_content` text NOT NULL,
  `ca_file_type` varchar(64) NOT NULL DEFAULT '',
  `ca_url` varchar(255) NOT NULL DEFAULT '',
  `ca_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `ca_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ca_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ca_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
