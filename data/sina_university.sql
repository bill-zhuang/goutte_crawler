/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-23 17:58:00
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sina_university
-- ----------------------------
DROP TABLE IF EXISTS `sina_university`;
CREATE TABLE `sina_university` (
  `su_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `su_name` varchar(255) DEFAULT '',
  `su_type` varchar(255) DEFAULT '',
  `sp_id` int(10) unsigned DEFAULT NULL,
  `su_status` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`su_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sina_province
-- ----------------------------
DROP TABLE IF EXISTS `sina_province`;
CREATE TABLE `sina_province` (
  `sp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sp_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`sp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
