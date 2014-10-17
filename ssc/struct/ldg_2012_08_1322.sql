CREATE TABLE `ldg_2012_08_1322` (
  `id` int(99) NOT NULL AUTO_INCREMENT,
  `reading_date` text,
  `meter_reading` text,
  `cu_used` text COMMENT 'cubic meter used',
  `pen_fee` text COMMENT 'penalty fee',
  `bill_amnt` text COMMENT 'billing amount',
  `loans_MLP` text COMMENT 'loans on material loan program',
  `loans_MF` text COMMENT 'loans on meter fee',
  `AF` text NOT NULL,
  `misc` text NOT NULL,
  `total` text,
  `OR_num` text COMMENT 'OR number',
  `OR_date` text COMMENT 'OR date',
  `remarks` text COMMENT 'other remarks goes here',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1
