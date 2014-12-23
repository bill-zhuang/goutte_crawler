/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-23 15:21:34
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for babytree_city
-- ----------------------------
DROP TABLE IF EXISTS `babytree_city`;
CREATE TABLE `babytree_city` (
  `bc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bc_province_id` int(10) unsigned NOT NULL,
  `bc_city_id` int(10) unsigned NOT NULL,
  `bc_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`bc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for babytree_hospital
-- ----------------------------
DROP TABLE IF EXISTS `babytree_hospital`;
CREATE TABLE `babytree_hospital` (
  `bh_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bh_city_id` int(10) unsigned NOT NULL,
  `bh_province_id` int(10) unsigned NOT NULL,
  `bh_name` varchar(255) NOT NULL DEFAULT '',
  `bh_url` varchar(255) NOT NULL DEFAULT '',
  `bh_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`bh_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for babytree_hospital_info
-- ----------------------------
DROP TABLE IF EXISTS `babytree_hospital_info`;
CREATE TABLE `babytree_hospital_info` (
  `bhi_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bhi_city_id` int(10) unsigned NOT NULL,
  `bhi_name` varchar(255) NOT NULL DEFAULT '',
  `bhi_url` varchar(255) NOT NULL DEFAULT '',
  `bhi_address` varchar(255) NOT NULL DEFAULT '',
  `bhi_phone` varchar(255) NOT NULL DEFAULT '',
  `bhi_intro` text NOT NULL,
  `bhi_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`bhi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for babytree_province
-- ----------------------------
DROP TABLE IF EXISTS `babytree_province`;
CREATE TABLE `babytree_province` (
  `bp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bp_province_id` int(10) unsigned NOT NULL,
  `bp_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`bp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
