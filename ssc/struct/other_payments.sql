CREATE TABLE `other_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acct_no_name` text NOT NULL,
  `OR_num` text NOT NULL,
  `OR_date` text NOT NULL,
  `amount` text NOT NULL,
  `specific_payments` text NOT NULL,
  `remarks` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1
