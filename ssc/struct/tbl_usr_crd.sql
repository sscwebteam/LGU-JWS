CREATE TABLE `tbl_usr_crd` (
  `row_id` text NOT NULL,
  `un` text NOT NULL,
  `pwd` text NOT NULL,
  `last_login_ip` text NOT NULL,
  `last_login_time` text NOT NULL,
  `fullname` text NOT NULL,
  `ofc_name` text NOT NULL,
  `failed_logins` text NOT NULL,
  `is_disable` text NOT NULL,
  `profile_id` text NOT NULL,
  `status` text NOT NULL COMMENT '1-logged'
) ENGINE=MyISAM DEFAULT CHARSET=latin1
