CREATE TABLE `lst_emp` (
  `row_id` int(11) NOT NULL AUTO_INCREMENT,
  `accnt_no` text NOT NULL COMMENT 'account number',
  `ledger_id` text NOT NULL COMMENT 'id on legder on non-paid',
  `remarks` text NOT NULL,
  PRIMARY KEY (`row_id`)
) ENGINE=MyISAM AUTO_INCREMENT=76 DEFAULT CHARSET=latin1
