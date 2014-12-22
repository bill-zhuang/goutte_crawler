/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-22 18:19:56
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for aigou_hospital
-- ----------------------------
DROP TABLE IF EXISTS `aigou_hospital`;
CREATE TABLE `aigou_hospital` (
  `ah_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ah_province` varchar(255) NOT NULL DEFAULT '',
  `ah_name` varchar(255) NOT NULL DEFAULT '',
  `ah_url` varchar(255) NOT NULL DEFAULT '',
  `ah_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`ah_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for aigou_hospital_info
-- ----------------------------
DROP TABLE IF EXISTS `aigou_hospital_info`;
CREATE TABLE `aigou_hospital_info` (
  `ahi_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ahi_name` varchar(255) NOT NULL DEFAULT '',
  `ahi_url` varchar(255) NOT NULL DEFAULT '',
  `ahi_province` varchar(255) NOT NULL DEFAULT '',
  `ahi_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `ahi_address` varchar(255) NOT NULL DEFAULT '',
  `ahi_phone` varchar(255) NOT NULL DEFAULT '',
  `ahi_intro` text NOT NULL,
  PRIMARY KEY (`ahi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for aigou_province
-- ----------------------------
DROP TABLE IF EXISTS `aigou_province`;
CREATE TABLE `aigou_province` (
  `ap_id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `ap_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ap_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
