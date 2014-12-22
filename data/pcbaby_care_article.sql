/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-22 17:48:47
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for pcbaby_care_article
-- ----------------------------
DROP TABLE IF EXISTS `pcbaby_care_article`;
CREATE TABLE `pcbaby_care_article` (
  `pca_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pca_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'article title',
  `pca_type` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '1:0-1, 2:1-3, 3:3-6(article type)',
  `pca_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'article url',
  `pca_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0:invalid, 1: valid',
  `pca_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pca_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pca_id`),
  KEY `idx_type_title` (`pca_type`,`pca_title`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
