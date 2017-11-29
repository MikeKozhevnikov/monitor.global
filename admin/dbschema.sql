/*

=============================================
 Monitor Global
---------------------------------------------
 2007-2009, Mike Kozhevnikov
=============================================
 File: admin/dbschema.sql
=============================================
 Назначение: SQL Schema
=============================================

*/

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) NOT NULL auto_increment,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `offurl` varchar(255) NOT NULL default '',
  `cat_image` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 /*!40101 DEFAULT CHARSET=utf8 */;


DROP TABLE IF EXISTS `portals`;
CREATE TABLE `portals` (
  `id` int(11) NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `name` text NOT NULL,
  `description` text NOT NULL,
  `url` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2166 /*!40101 DEFAULT CHARSET=utf8 */;

DROP TABLE IF EXISTS `review`;
CREATE TABLE `review` (
  `id` int(11) NOT NULL auto_increment,
  `catid` int(11) default NULL,
  `name` text /*!40101 character set cp1251 */,
  `post` text /*!40101 character set cp1251 */,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 /*!40101 DEFAULT CHARSET=utf8 */;

DROP TABLE IF EXISTS `servers`;
CREATE TABLE `servers` (
  `id` int(11) NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `name` text NOT NULL,
  `onlineday` int(11) default NULL,
  `description` text NOT NULL,
  `url` varchar(255) default '',
  `enable` int(1) default '1',
  `dataurl` varchar(255) NOT NULL default '',
  `tagstart` text,
  `tagstop` text,
  `coding` text,
  `deletetags` int(1) default '0',
  `deletespaceandrows` int(1) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2170 /*!40101 DEFAULT CHARSET=utf8 */;


DROP TABLE IF EXISTS `statslog`;
CREATE TABLE `statslog` (
  `id` int(11) NOT NULL auto_increment,
  `serverid` int(11) NOT NULL default '0',
  `date` date NOT NULL,
  `max` int(11) default NULL,
  `average` int(11) default NULL,
  `time0` int(11) default NULL,
  `time1` int(11) default NULL,
  `time2` int(11) default NULL,
  `time3` int(11) default NULL,
  `time4` int(11) default NULL,
  `time5` int(11) default NULL,
  `time6` int(11) default NULL,
  `time7` int(11) default NULL,
  `time8` int(11) default NULL,
  `time9` int(11) default NULL,
  `time10` int(11) default NULL,
  `time11` int(11) default NULL,
  `time12` int(11) default NULL,
  `time13` int(11) default NULL,
  `time14` int(11) default NULL,
  `time15` int(11) default NULL,
  `time16` int(11) default NULL,
  `time17` int(11) default NULL,
  `time18` int(11) default NULL,
  `time19` int(11) default NULL,
  `time20` int(11) default NULL,
  `time21` int(11) default NULL,
  `time22` int(11) default NULL,
  `time23` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 /*!40101 DEFAULT CHARSET=utf8 */;