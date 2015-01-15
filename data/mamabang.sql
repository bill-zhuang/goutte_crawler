/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-23 18:08:52
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for mamabang_main_category
-- ----------------------------
DROP TABLE IF EXISTS `mamabang_main_category`;
CREATE TABLE `mamabang_main_category` (
  `mmc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mmc_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`mmc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mamabang_topic
-- ----------------------------
DROP TABLE IF EXISTS `mamabang_topic`;
CREATE TABLE `mamabang_topic` (
  `mt_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mt_name` varchar(255) NOT NULL DEFAULT '',
  `mt_main_category` varchar(255) NOT NULL DEFAULT '',
  `mt_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`mt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mamabang_topic_content
-- ----------------------------
DROP TABLE IF EXISTS `mamabang_topic_content`;
CREATE TABLE `mamabang_topic_content` (
  `mtc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mtc_topic` varchar(255) NOT NULL DEFAULT '',
  `mtc_main_category` varchar(255) NOT NULL DEFAULT '',
  `mtc_url` varchar(255) NOT NULL DEFAULT '',
  `mtc_content` text NOT NULL,
  `mtc_floor` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `mtc_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`mtc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
