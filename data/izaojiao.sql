/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-23 15:40:21
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for izaojiao_institution
-- ----------------------------
DROP TABLE IF EXISTS `izaojiao_institution`;
CREATE TABLE `izaojiao_institution` (
  `ii_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ii_branch` varchar(255) NOT NULL DEFAULT '',
  `ii_name` varchar(255) NOT NULL DEFAULT '',
  `ii_url` varchar(255) NOT NULL DEFAULT '',
  `ii_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`ii_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for izaojiao_institution_info
-- ----------------------------
DROP TABLE IF EXISTS `izaojiao_institution_info`;
CREATE TABLE `izaojiao_institution_info` (
  `iii_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `iii_branch` varchar(255) NOT NULL DEFAULT '',
  `iii_name` varchar(255) NOT NULL DEFAULT '',
  `iii_logo` varchar(255) NOT NULL DEFAULT '',
  `iii_type` varchar(255) NOT NULL DEFAULT '',
  `iii_age` varchar(255) NOT NULL DEFAULT '',
  `iii_area` varchar(255) NOT NULL DEFAULT '',
  `iii_address` varchar(255) NOT NULL DEFAULT '',
  `iii_phone` varchar(255) NOT NULL DEFAULT '',
  `iii_intro` text NOT NULL,
  `iii_website` varchar(255) NOT NULL DEFAULT '',
  `iii_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`iii_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for izaojiao_province
-- ----------------------------
DROP TABLE IF EXISTS `izaojiao_branch`;
CREATE TABLE `izaojiao_branch` (
  `ib_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ib_name` varchar(255) NOT NULL DEFAULT '',
  `ib_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
