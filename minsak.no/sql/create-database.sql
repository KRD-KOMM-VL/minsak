/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.
*/



/*
CREATE DATABASE mittforslag;
GRANT ALL ON mittforslag.* TO mittforslag@localhost IDENTIFIED BY 'khS872jFj29f';
USE mittforslag;
*/

SET NAMES utf8;

DROP TABLE IF EXISTS `location`;
CREATE TABLE `location` (
  `id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `parent_id` INT(11) NULL,
  `default_language` VARCHAR(10) DEFAULT 'nb' NOT NULL,
  `slug` VARCHAR(255) UNIQUE NOT NULL,
  `signatures_required` INT(8) DEFAULT 0 NOT NULL,
  `auto_moderate_initiative` CHAR(1) DEFAULT '0' NOT NULL,
  `auto_moderate_signature` CHAR(1) DEFAULT '0' NOT NULL,
  `email_address` VARCHAR(255) NULL,
  `web_address` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  KEY (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_danish_ci;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `password` CHAR(160) NOT NULL,
  `isValidated` CHAR(1) DEFAULT '0' NOT NULL,
  `isSiteAdmin` CHAR(1) NOT NULL,
  `isModerator` CHAR(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_danish_ci;

DROP TABLE IF EXISTS `user_access_key`;
CREATE TABLE `user_access_key` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `created_time` BIGINT NOT NULL,
  `access_key` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`user_id`),
  UNIQUE (`access_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role` (
  `user_id` INT(11) NOT NULL,
  `location_id` INT(11) NOT NULL,
  `initiative_moderator` CHAR(1) DEFAULT '0' NOT NULL,
  `signature_moderator` CHAR(1) DEFAULT '0' NOT NULL,
  PRIMARY KEY (`user_id`, `location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_danish_ci;

DROP TABLE IF EXISTS `initiative`;
CREATE TABLE `initiative` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `location_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `text` TEXT NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `zipcode` VARCHAR(4) NOT NULL,
  `end_date` BIGINT(20) NOT NULL,
  `sender` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `created_time` BIGINT NOT NULL,
  `rules_accepted` CHAR(1) DEFAULT '0' NOT NULL,
  `status` ENUM('draft', 'unmoderated', 'rejected', 'open', 'screening', 'completed', 'withdrawn') NOT NULL,
  `status_time` BIGINT NOT NULL,
  `user_id` INT(11) NOT NULL,
  `image_type` ENUM('missing', 'local', 'flickr') DEFAULT 'missing' NOT NULL,
  `image_file` VARCHAR(255),
  `image_flickr_photo_id` VARCHAR(255),
  `image_credits` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_danish_ci;
CREATE INDEX `idx_location_id` ON `initiative` (`location_id`);

DROP TABLE IF EXISTS `temporary_image`;
CREATE TABLE `temporary_image` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11),
  `image_type` ENUM('missing', 'local', 'flickr') DEFAULT 'missing' NOT NULL,
  `image_file` VARCHAR(255),
  `image_flickr_photo_id` VARCHAR(255),
  `image_flickr_image_credits` VARCHAR(255),
  `uploaded_time` BIGINT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_danish_ci;

DROP TABLE IF EXISTS `signature`;
CREATE TABLE `signature` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `initiative_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `address1` VARCHAR(255) NOT NULL,
  `address2` VARCHAR(255) NULL,
  `area_code` VARCHAR(4) NOT NULL,
  `created_time` BIGINT NOT NULL,
  `moderated` ENUM('new', 'accepted', 'rejected') DEFAULT 'new' NOT NULL,
  `moderated_user_id` INT(11),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_danish_ci;
CREATE INDEX `idx_initiative_id` ON `signature` (`initiative_id`);

DROP TABLE IF EXISTS `initiative_status`;
CREATE TABLE `initiative_status` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `initiative_id` INT(11) NOT NULL,
  `prev_status` ENUM('draft', 'unmoderated', 'open', 'screening', 'completed', 'withdrawn') NOT NULL,
  `current_status` ENUM('draft', 'unmoderated', 'open', 'screening', 'completed', 'withdrawn') NOT NULL,
  `change_time` BIGINT NOT NULL,
  `user_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_danish_ci;
CREATE INDEX `idx_initiative_id` ON `initiative_status` (`initiative_id`);

DROP TABLE IF EXISTS `initiative_comment`;
CREATE TABLE `initiative_comment` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `initiative_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `text` TEXT NOT NULL,
  `created_time` BIGINT NOT NULL,
  `status` ENUM('accepted', 'rejected') DEFAULT 'accepted' NOT NULL, -- see add-status-to-comments.sql
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_danish_ci;
CREATE INDEX `idx_initiative_id` ON `initiative_comment` (`initiative_id`);
