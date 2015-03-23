-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 23, 2015 at 09:56 PM
-- Server version: 5.5.38-1~dotdeb.0
-- PHP Version: 5.4.38

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


-- --------------------------------------------------------

--
-- Table structure for table `cms_container`
--

CREATE TABLE IF NOT EXISTS `cms_container` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `classes` varchar(255) DEFAULT NULL,
  `template` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_file`
--

CREATE TABLE IF NOT EXISTS `cms_file` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_form`
--

CREATE TABLE IF NOT EXISTS `cms_form` (
  `id` int(11) NOT NULL,
  `config` text CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_global_object`
--

CREATE TABLE IF NOT EXISTS `cms_global_object` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `object_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_image`
--

CREATE TABLE IF NOT EXISTS `cms_image` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `url_target` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `image_highres` varchar(255) DEFAULT NULL,
  `align` varchar(255) DEFAULT NULL,
  `text` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_link`
--

CREATE TABLE IF NOT EXISTS `cms_link` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_multimedia`
--

CREATE TABLE IF NOT EXISTS `cms_multimedia` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `embedded_code` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_news`
--

CREATE TABLE IF NOT EXISTS `cms_news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT '1',
  `text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_object`
--

CREATE TABLE IF NOT EXISTS `cms_object` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `changed_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `changed_by` varchar(255) NOT NULL,
  `sort_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_page`
--

CREATE TABLE IF NOT EXISTS `cms_page` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `title_sef` varchar(255) NOT NULL,
  `title_alt` varchar(255) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `display_in_navi` tinyint(1) DEFAULT '1',
  `seo_keywords` text,
  `seo_description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_text`
--

CREATE TABLE IF NOT EXISTS `cms_text` (
  `id` int(11) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_user`
--

CREATE TABLE IF NOT EXISTS `cms_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_user_node`
--

CREATE TABLE IF NOT EXISTS `cms_user_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `node` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `node` (`node`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cms_container`
--
ALTER TABLE `cms_container`
  ADD CONSTRAINT `cms_container_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cms_object` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cms_file`
--
ALTER TABLE `cms_file`
  ADD CONSTRAINT `cms_file_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cms_object` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cms_form`
--
ALTER TABLE `cms_form`
  ADD CONSTRAINT `cms_form_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cms_object` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cms_global_object`
--
ALTER TABLE `cms_global_object`
  ADD CONSTRAINT `cms_global_object_ibfk_1` FOREIGN KEY (`object_id`) REFERENCES `cms_object` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cms_image`
--
ALTER TABLE `cms_image`
  ADD CONSTRAINT `cms_image_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cms_object` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cms_link`
--
ALTER TABLE `cms_link`
  ADD CONSTRAINT `cms_link_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cms_object` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cms_multimedia`
--
ALTER TABLE `cms_multimedia`
  ADD CONSTRAINT `cms_multimedia_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cms_object` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cms_news`
--
ALTER TABLE `cms_news`
  ADD CONSTRAINT `cms_news_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cms_object` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cms_object`
--
ALTER TABLE `cms_object`
  ADD CONSTRAINT `cms_object_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `cms_object` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cms_page`
--
ALTER TABLE `cms_page`
  ADD CONSTRAINT `cms_page_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cms_object` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cms_text`
--
ALTER TABLE `cms_text`
  ADD CONSTRAINT `cms_text_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cms_object` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cms_user_node`
--
ALTER TABLE `cms_user_node`
  ADD CONSTRAINT `cms_user_node_ibfk_1` FOREIGN KEY (`user`) REFERENCES `cms_user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cms_user_node_ibfk_2` FOREIGN KEY (`node`) REFERENCES `cms_object` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
