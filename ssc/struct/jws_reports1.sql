CREATE TABLE `jws_reports1` (
  `row_id` int(99) NOT NULL AUTO_INCREMENT,
  `month` text NOT NULL COMMENT 'month_format: mm',
  `brgy` text NOT NULL COMMENT 'use barangay profiles',
  `totalC` text NOT NULL COMMENT 'total concessionaires',
  `activeC` text NOT NULL COMMENT 'active concessionaires',
  `cu_m_used` text NOT NULL COMMENT 'cubic meter used',
  `collectibles` text NOT NULL COMMENT 'monthly total collectibles',
  `collections_onDue` text NOT NULL COMMENT 'monthly total collections',
  `collections_afterDue` text NOT NULL,
  PRIMARY KEY (`row_id`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=latin1
