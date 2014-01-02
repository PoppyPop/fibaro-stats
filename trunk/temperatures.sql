CREATE TABLE `temperatures` (
  `cle` bigint(10) NOT NULL AUTO_INCREMENT,
  `datecapture` datetime NOT NULL,
  `piece` int(5) DEFAULT NULL,
  `id` int(5) DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `temp` float(5,2) DEFAULT NULL,
  PRIMARY KEY (`cle`),
  UNIQUE KEY `IDX_TEMP_ENR` (`datecapture`,`id`),
  KEY `IDX_TEMP_DATECAPTURE` (`datecapture`),
  KEY `IDX_TEMP_ID` (`id`),
  KEY `IDX_TEMP_PIECE` (`piece`,`id`,`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=401563 DEFAULT CHARSET=utf8;