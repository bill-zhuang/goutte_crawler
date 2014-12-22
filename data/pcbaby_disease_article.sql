/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-22 17:49:01
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for pcbaby_disease_article
-- ----------------------------
DROP TABLE IF EXISTS `pcbaby_disease_article`;
CREATE TABLE `pcbaby_disease_article` (
  `pda_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pda_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'article title',
  `pda_type` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '1:0-1, 2:1-3, 3:3-6(article type)',
  `pda_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'article url',
  `pda_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0:invalid, 1: valid',
  `pda_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pda_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pda_id`),
  KEY `idx_type_title` (`pda_type`,`pda_title`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
