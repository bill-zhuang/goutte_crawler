/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-23 13:41:04
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ttpet_main_category
-- ----------------------------
DROP TABLE IF EXISTS `ttpet_main_category`;
CREATE TABLE `ttpet_main_category` (
  `tmc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tmc_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tmc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ttpet_pet
-- ----------------------------
DROP TABLE IF EXISTS `ttpet_pet`;
CREATE TABLE `ttpet_pet` (
  `tp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tp_main_category` varchar(255) DEFAULT '',
  `tp_sub_category` varchar(255) DEFAULT '',
  `tp_name` varchar(255) DEFAULT '',
  `tp_url` varchar(255) DEFAULT '',
  `tp_status` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`tp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ttpet_pet_info
-- ----------------------------
DROP TABLE IF EXISTS `ttpet_pet_info`;
CREATE TABLE `ttpet_pet_info` (
  `tpi_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tpi_main_category` varchar(255) DEFAULT '',
  `tpi_sub_category` varchar(255) DEFAULT '',
  `tpi_name` varchar(255) DEFAULT '',
  `tpi_url` varchar(255) DEFAULT '',
  `tpi_intro` text,
  `tpi_status` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`tpi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ttpet_sub_category
-- ----------------------------
DROP TABLE IF EXISTS `ttpet_sub_category`;
CREATE TABLE `ttpet_sub_category` (
  `tsc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tsc_name` varchar(255) NOT NULL DEFAULT '',
  `tsc_main_category` varchar(255) NOT NULL DEFAULT '',
  `tsc_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tsc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
