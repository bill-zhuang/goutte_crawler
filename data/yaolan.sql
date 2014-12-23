/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-23 16:11:04
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for yaolan_article
-- ----------------------------
DROP TABLE IF EXISTS `yaolan_article`;
CREATE TABLE `yaolan_article` (
  `ya_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ya_main_category` varchar(255) NOT NULL DEFAULT '',
  `ya_sub_category` varchar(255) NOT NULL DEFAULT '',
  `ya_title` varchar(255) NOT NULL DEFAULT '',
  `ya_content` text NOT NULL,
  `ya_url` varchar(255) NOT NULL DEFAULT '',
  `ya_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`ya_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yaolan_main_category
-- ----------------------------
DROP TABLE IF EXISTS `yaolan_main_category`;
CREATE TABLE `yaolan_main_category` (
  `ymc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ymc_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ymc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for yaolan_sub_category
-- ----------------------------
DROP TABLE IF EXISTS `yaolan_sub_category`;
CREATE TABLE `yaolan_sub_category` (
  `ysc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ysc_main_category` varchar(255) NOT NULL DEFAULT '',
  `ysc_name` varchar(255) NOT NULL DEFAULT '',
  `ysc_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ysc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
