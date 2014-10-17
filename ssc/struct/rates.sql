CREATE TABLE `rates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `baseline` text COMMENT 'first 10 cubic meter',
  `min_usage` text COMMENT 'minimum usage',
  `max_usage` text COMMENT 'maximum usage',
  `conn_type` text COMMENT 'connection type(1-residential;2-commercial)',
  `rates` text COMMENT 'rates',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COMMENT='rates of water consumption'
