/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-22 18:32:05
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for boqqi_city
-- ----------------------------
DROP TABLE IF EXISTS `boqqi_city`;
CREATE TABLE `boqqi_city` (
  `bc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bc_name` varchar(255) NOT NULL DEFAULT '',
  `bc_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`bc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for boqqi_district
-- ----------------------------
DROP TABLE IF EXISTS `boqqi_district`;
CREATE TABLE `boqqi_district` (
  `bd_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bd_name` varchar(255) NOT NULL DEFAULT '',
  `bd_city` varchar(255) NOT NULL DEFAULT '',
  `bd_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`bd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for boqqi_institution
-- ----------------------------
DROP TABLE IF EXISTS `boqqi_institution`;
CREATE TABLE `boqqi_institution` (
  `bi_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bi_city` varchar(255) NOT NULL DEFAULT '',
  `bi_district` varchar(255) NOT NULL DEFAULT '',
  `bi_name` varchar(255) NOT NULL DEFAULT '',
  `bi_url` varchar(255) NOT NULL DEFAULT '',
  `bi_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`bi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for boqqi_institution_info
-- ----------------------------
DROP TABLE IF EXISTS `boqqi_institution_info`;
CREATE TABLE `boqqi_institution_info` (
  `bii_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bii_city` varchar(255) NOT NULL DEFAULT '',
  `bii_district` varchar(255) NOT NULL DEFAULT '',
  `bii_name` varchar(255) NOT NULL DEFAULT '',
  `bii_url` varchar(255) NOT NULL DEFAULT '',
  `bii_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `bii_address` varchar(255) NOT NULL DEFAULT '',
  `bii_phone` varchar(255) NOT NULL DEFAULT '',
  `bii_opentime` varchar(255) NOT NULL DEFAULT '',
  `bii_tags` varchar(255) NOT NULL DEFAULT '',
  `bii_intro` text NOT NULL,
  `bii_logo` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`bii_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
