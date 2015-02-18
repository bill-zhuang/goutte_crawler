/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-02-18 12:40:47
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crawl_shiti123
-- ----------------------------
DROP TABLE IF EXISTS `crawl_shiti123`;
CREATE TABLE `crawl_shiti123` (
  `cs_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gr_id` int(10) unsigned NOT NULL,
  `co_id` int(10) unsigned NOT NULL,
  `cs_title` varchar(255) NOT NULL DEFAULT '',
  `cs_download_url` varchar(255) NOT NULL DEFAULT '',
  `cs_content` text NOT NULL,
  `cs_url` varchar(255) NOT NULL DEFAULT '',
  `cs_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `cs_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cs_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
