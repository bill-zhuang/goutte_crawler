/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50522
Source Host           : localhost:3306
Source Database       : crawl

Target Server Type    : MYSQL
Target Server Version : 50522
File Encoding         : 65001

Date: 2014-12-22 18:19:44
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

-- ----------------------------
-- Table structure for pcbaby_disease_article_content
-- ----------------------------
DROP TABLE IF EXISTS `pcbaby_disease_article_content`;
CREATE TABLE `pcbaby_disease_article_content` (
  `pdac_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pda_id` int(10) unsigned NOT NULL COMMENT 'pcbaby_desease_article primary key',
  `pdac_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'article title',
  `pdac_content` text NOT NULL COMMENT 'article content(html format)',
  `pdac_tag` varchar(255) NOT NULL DEFAULT '' COMMENT 'article tags(seperate by comma)',
  `pdac_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0:invalid, 1:valid',
  `pdac_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pdac_update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pdac_id`),
  KEY `idx_pdaid` (`pda_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
