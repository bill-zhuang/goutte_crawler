/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-23 15:57:14
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for mama_article
-- ----------------------------
DROP TABLE IF EXISTS `mama_article`;
CREATE TABLE `mama_article` (
  `ma_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ma_title` varchar(255) NOT NULL,
  `ma_content` text NOT NULL,
  `ma_url` varchar(255) NOT NULL,
  `ma_main_category` varchar(255) NOT NULL,
  `ma_sub_category` varchar(255) NOT NULL,
  `ma_status` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`ma_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mama_main_category
-- ----------------------------
DROP TABLE IF EXISTS `mama_main_category`;
CREATE TABLE `mama_main_category` (
  `mmc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mmc_name` varchar(255) NOT NULL,
  PRIMARY KEY (`mmc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mama_sub_category
-- ----------------------------
DROP TABLE IF EXISTS `mama_sub_category`;
CREATE TABLE `mama_sub_category` (
  `msc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `msc_name` varchar(255) NOT NULL,
  `msc_main_category` varchar(255) NOT NULL,
  `msc_url` varchar(255) NOT NULL,
  PRIMARY KEY (`msc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
