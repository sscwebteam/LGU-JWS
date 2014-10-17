CREATE TABLE `collectorsreport_2014_01_03_cashier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `OR_date` text NOT NULL,
  `OR_num` text NOT NULL,
  `Payee` text NOT NULL,
  `address_brgy` text NOT NULL,
  `app_fee_partial` text NOT NULL,
  `app_fee_full` text NOT NULL,
  `meter_fee` text NOT NULL,
  `MLP` text NOT NULL,
  `water_bill` text NOT NULL,
  `penalty_fee` text NOT NULL,
  `misc_fee` text NOT NULL,
  `total` text NOT NULL,
  `remarks` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=239 DEFAULT CHARSET=latin1
