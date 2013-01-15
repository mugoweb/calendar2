--
-- Table structure for table `calendar2event`
--

CREATE TABLE IF NOT EXISTS `calendar2event` (
  `id` int(11) NOT NULL auto_increment,
  `node_id` int(11) NOT NULL,
  `calendar_node_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(100) collate utf8_unicode_ci NOT NULL,
  `content` text collate utf8_unicode_ci NOT NULL,
  `start` int(11) NOT NULL,
  `end` int(11) NOT NULL,
  `all_day` int(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `object_id` (`node_id`,`category_id`,`start`,`end`,`all_day`),
  KEY `calendar_node_id` (`calendar_node_id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar2event_category`
--

CREATE TABLE IF NOT EXISTS `calendar2event_category` (
  `id` int(11) NOT NULL auto_increment,
  `event_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `event_id` (`event_id`,`category_id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci

