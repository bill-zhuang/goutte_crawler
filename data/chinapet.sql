/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-23 14:26:28
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for chinapet_main_category
-- ----------------------------
DROP TABLE IF EXISTS `chinapet_main_category`;
CREATE TABLE `chinapet_main_category` (
  `cmc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cmc_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`cmc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for chinapet_pet
-- ----------------------------
DROP TABLE IF EXISTS `chinapet_post`;
CREATE TABLE `chinapet_post` (
  `cp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cp_main_category` varchar(255) DEFAULT '',
  `cp_sub_category` varchar(255) DEFAULT '',
  `cp_name` varchar(255) DEFAULT '',
  `cp_url` varchar(255) DEFAULT '',
  `cp_status` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`cp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for chinapet_pet_info
-- ----------------------------
DROP TABLE IF EXISTS `chinapet_post_info`;
CREATE TABLE `chinapet_post_info` (
  `cpi_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cpi_main_category` varchar(255) DEFAULT '',
  `cpi_sub_category` varchar(255) DEFAULT '',
  `cpi_name` varchar(255) DEFAULT '',
  `cpi_url` varchar(255) DEFAULT '',
  `cpi_content` text,
  `cpi_image` varchar(255) DEFAULT '',
  `cpi_status` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`cpi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for chinapet_sub_category
-- ----------------------------
DROP TABLE IF EXISTS `chinapet_sub_category`;
CREATE TABLE `chinapet_sub_category` (
  `csc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `csc_main_category` varchar(255) DEFAULT '',
  `csc_name` varchar(255) DEFAULT '',
  `csc_url` varchar(255) DEFAULT '',
  PRIMARY KEY (`csc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
