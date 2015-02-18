/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2015-02-18 12:24:21
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for city
-- ----------------------------
DROP TABLE IF EXISTS `city`;
CREATE TABLE `city` (
  `ct_id` int(11) NOT NULL AUTO_INCREMENT,
  `pr_id` int(11) NOT NULL COMMENT 'table province primary key',
  `ct_name` varchar(128) NOT NULL DEFAULT '',
  `ct_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ct_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ct_status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ct_id`),
  KEY `idx_prid` (`pr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
