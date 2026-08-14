CREATE TABLE `accesorios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `precio` decimal(11,2) NOT NULL,
  `activo` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0;


INSERT INTO `information` (`key`, `Title`, `Value`, `textarea`, `HelpMessage`) VALUES
  ('accesorios_escala_descuento', 'Escala de comisiones por vendedor: Accesorios', '{}', 2, 'Escala de comisiones de accesorios.');
