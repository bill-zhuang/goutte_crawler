/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-06-10 14:35:52
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for crawl_test51
-- ----------------------------
DROP TABLE IF EXISTS `crawl_test51`;
CREATE TABLE `crawl_test51` (
  `ctid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cgid` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `wenku_url` varchar(255) NOT NULL DEFAULT '',
  `file_type` varchar(64) NOT NULL DEFAULT '',
  `coin` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'download coin',
  `url` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ctid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
