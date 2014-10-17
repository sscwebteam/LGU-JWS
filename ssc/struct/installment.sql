CREATE TABLE `installment` (
  `acc_no` text,
  `installment_value` text,
  `billed_date` text,
  `bill_count` text,
  `addons_type` text NOT NULL,
  `status` text NOT NULL,
  `remarks` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1
