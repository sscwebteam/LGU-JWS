CREATE TABLE `or_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `or_number` text,
  `or_date` text,
  `issued_to_accnt` text,
  `issued_amnt` text,
  `encodedBy` text NOT NULL,
  `remarks` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13530 DEFAULT CHARSET=latin1
