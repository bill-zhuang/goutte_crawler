/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-22 17:48:54
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for pcbaby_care_article_content
-- ----------------------------
DROP TABLE IF EXISTS `pcbaby_care_article_content`;
CREATE TABLE `pcbaby_care_article_content` (
  `pcac_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pca_id` int(10) unsigned NOT NULL COMMENT 'pcbaby_care_article primary key',
  `pcac_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'article title',
  `pcac_content` text NOT NULL COMMENT 'article content(html format)',
  `pcac_tag` varchar(255) NOT NULL DEFAULT '' COMMENT 'article tags(seperate by comma)',
  `pcac_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0:invalid, 1:valid',
  `pcac_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pcac_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pcac_id`),
  KEY `idx_pcaid` (`pca_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
