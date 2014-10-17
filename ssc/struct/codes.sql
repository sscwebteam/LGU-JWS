CREATE TABLE `codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing data',
  `code` text COMMENT 'assigned codes for specific data from category',
  `descr` text COMMENT 'description of the codes or equivalent string value',
  `category` text COMMENT 'category of the data according to its nature',
  `rem` text COMMENT 'other remarks can be shown here',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=latin1
