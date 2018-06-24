#
# putting in new tables from cohomeals.
#
# will have to export the data in the tables on the live site
#

CREATE TABLE IF NOT EXISTS `coho_meals_meal` (
  `cal_id` int(11) NOT NULL DEFAULT '0',
  `cal_club_id` int(11) DEFAULT NULL,
  `cal_date` int(11) NOT NULL DEFAULT '0',
  `cal_time` int(11) DEFAULT NULL,
  `cal_suit` varchar(7) NOT NULL DEFAULT '',
  `cal_walkins` char(1) DEFAULT 'C',
  `cal_signup_deadline` int(11) NOT NULL DEFAULT '2',
  `cal_base_price` decimal(7,2) NOT NULL DEFAULT '400.00',
  `cal_max_diners` int(11) NOT NULL DEFAULT '0',
  `cal_menu` text,
  `cal_notes` text,
  `cal_cancelled` char(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cal_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `coho_meals_meal_participant` (
  `cal_id` int(11) NOT NULL DEFAULT '0',
  `cal_login` varchar(25) NOT NULL DEFAULT '',
  `cal_type` char(1) NOT NULL DEFAULT '',
  `cal_notes` varchar(80) NOT NULL DEFAULT '',
  `cal_walkin` char(1) NOT NULL DEFAULT '0',
  `cal_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
