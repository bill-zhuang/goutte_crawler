/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-23 17:12:12
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for guahao_city
-- ----------------------------
DROP TABLE IF EXISTS `guahao_city`;
CREATE TABLE `guahao_city` (
  `gc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gc_name` varchar(255) NOT NULL DEFAULT '',
  `gc_city_id` int(10) unsigned NOT NULL,
  `gc_province_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`gc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for guahao_hospital
-- ----------------------------
DROP TABLE IF EXISTS `guahao_hospital`;
CREATE TABLE `guahao_hospital` (
  `gh_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gh_province_id` int(10) unsigned NOT NULL,
  `gh_city_id` int(10) unsigned NOT NULL,
  `gh_name` varchar(255) NOT NULL DEFAULT '',
  `gh_url` varchar(255) NOT NULL DEFAULT '',
  `gh_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`gh_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for guahao_hospital_info
-- ----------------------------
DROP TABLE IF EXISTS `guahao_hospital_info`;
CREATE TABLE `guahao_hospital_info` (
  `ghi_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ghi_province_id` int(10) unsigned NOT NULL,
  `ghi_city_id` int(10) unsigned NOT NULL,
  `ghi_name` varchar(255) NOT NULL DEFAULT '',
  `ghi_url` varchar(255) NOT NULL DEFAULT '',
  `ghi_address` varchar(255) NOT NULL DEFAULT '',
  `ghi_phone` varchar(255) NOT NULL DEFAULT '',
  `ghi_intro` text NOT NULL,
  `ghi_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`ghi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for guahao_province
-- ----------------------------
DROP TABLE IF EXISTS `guahao_province`;
CREATE TABLE `guahao_province` (
  `gp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gp_name` varchar(255) NOT NULL DEFAULT '',
  `gp_province_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`gp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
