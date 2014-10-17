CREATE TABLE `dates_sched` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bryg_codes` text,
  `date_meter_reading` text COMMENT 'reading month(ex: Jan)',
  `date_payment` text COMMENT 'next month after reading month(ex Feb)',
  `date_due_grace_period` text COMMENT 'the same month of payment',
  `date_ext_penalty` text COMMENT 'the same month of payment',
  `date_disconn` text COMMENT 'the same month of payment',
  `rem` text COMMENT 'other remarks can be shown here',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COMMENT='schedule of reading, payment, and disconnection dates'
