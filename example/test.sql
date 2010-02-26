/*
MySQL Data Transfer
Source Host: localhost
Source Database: test
Target Host: localhost
Target Database: test
Date: 2010/2/26 15:35:49
*/

SET FOREIGN_KEY_CHECKS=0;

use test;

-- ----------------------------
-- Table structure for e_fw_category
-- ----------------------------
CREATE TABLE `e_fw_category` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `e_fw_category` VALUES ('1', '你好bc');
INSERT INTO `e_fw_category` VALUES ('4', 'qwe');

create database test2; 

use test2;
-- ----------------------------
-- Table structure for e_fw_blog
-- ----------------------------
CREATE TABLE `e_fw_blog` (
  `id` int(11) NOT NULL auto_increment,
  `category_id` int(11) NOT NULL,
  `category_title` varchar(255) default NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `e_fw_blog` VALUES ('1', '1', '你bc', 'test_update', '123');
INSERT INTO `e_fw_blog` VALUES ('2', '4', '你好ab', 'hello word!', 'link push data');