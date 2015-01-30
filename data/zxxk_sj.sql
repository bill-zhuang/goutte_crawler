/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-01-30 14:10:19
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for zxxk_sj
-- ----------------------------
DROP TABLE IF EXISTS `zxxk_sj`;
CREATE TABLE `zxxk_sj` (
  `zs_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gr_id` int(10) unsigned NOT NULL COMMENT 'table grade',
  `co_id` int(10) unsigned NOT NULL COMMENT 'table course',
  `pr_id` int(255) unsigned NOT NULL DEFAULT '0' COMMENT 'table province',
  `zs_title` varchar(255) NOT NULL DEFAULT '',
  `zs_download_url` varchar(255) NOT NULL DEFAULT '',
  `zs_file_type` varchar(50) NOT NULL DEFAULT '',
  `zs_content` text NOT NULL,
  `zs_url` varchar(255) NOT NULL DEFAULT '',
  `zs_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `zs_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `zs_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`zs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
