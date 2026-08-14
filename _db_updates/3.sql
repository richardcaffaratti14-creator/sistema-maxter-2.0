
INSERT INTO `information` (`key`, `Title`, `Value`, `textarea`, `HelpMessage`)
VALUES ('photobook_image_price', 'Fotolibro: Precio imágenes', '50', '0', 'Precio de las imágenes del fotolibro.');

INSERT INTO `information` (`key`, `Title`, `Value`, `textarea`, `HelpMessage`)
VALUES ('photobook_escala_descuento', 'Fotolibro: Escala descuento',  '{\"dd\":[\"0\"],\"dh\":[\"999999\"],\"dm\":[\"100\"]}', '2', 'Escala de descuentos de los fotolibros.');

DROP TABLE IF EXISTS `fotolibros`;
CREATE TABLE `fotolibros` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `telefono` varchar(255) NOT NULL,
  `pedido` text NOT NULL,
  `total` float NOT NULL,
  `sena` float NOT NULL,
  `idVendedor` int(11) NOT NULL,
  `estado` int(11) DEFAULT '0',
  `evento` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idVendedor` (`idVendedor`),
  CONSTRAINT `fotolibros_ibfk_1` FOREIGN KEY (`idVendedor`) REFERENCES `vendedores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `pedidos`
ADD `idFotolibro` int(10) unsigned NULL AFTER `idPresupuesto`,
ADD FOREIGN KEY (`idFotolibro`) REFERENCES `fotolibros` (`id`);


