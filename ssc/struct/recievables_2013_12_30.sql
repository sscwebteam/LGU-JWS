CREATE TABLE `recievables_2013_12_30` (
  `row_id` int(11) NOT NULL AUTO_INCREMENT,
  `accntno` text NOT NULL COMMENT 'account number',
  `name` text NOT NULL COMMENT 'applilcant name',
  `reading_date` text NOT NULL,
  `other_payments` text NOT NULL COMMENT 'loans on mlp,mf,af,misc',
  `bill_amnt` text NOT NULL COMMENT 'bill amount',
  `due_date` text NOT NULL COMMENT 'penalty date',
  `penalty_amnt` text NOT NULL COMMENT 'penalty amount',
  `total_receivables` text NOT NULL COMMENT 'total receivables amount',
  PRIMARY KEY (`row_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1655 DEFAULT CHARSET=latin1
