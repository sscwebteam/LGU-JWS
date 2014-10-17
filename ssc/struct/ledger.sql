CREATE TABLE `ledger` (
  `id` int(99) NOT NULL AUTO_INCREMENT,
  `reading_date` text NOT NULL,
  `meter_reading` text NOT NULL,
  `cu_used` text NOT NULL COMMENT 'cubic meter used',
  `pen_fee` text NOT NULL COMMENT 'penalty fee',
  `bill_amnt` text NOT NULL COMMENT 'billing amount',
  `loans_MLP` text NOT NULL COMMENT 'loans on material loan program',
  `loans_MF` text NOT NULL COMMENT 'loans on meter fee',
  `total` text NOT NULL,
  `OR_num` text NOT NULL COMMENT 'OR number',
  `OR_date` text NOT NULL COMMENT 'OR date',
  `accnt_no` text NOT NULL COMMENT 'account number',
  `cli_name` text NOT NULL COMMENT 'client''s name',
  `add_st` text NOT NULL COMMENT 'street adress',
  `add_brgy` text NOT NULL COMMENT 'barangay address',
  `remarks` text NOT NULL COMMENT 'other remarks goes here',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
