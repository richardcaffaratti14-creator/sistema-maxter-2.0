# SQL Manager for MySQL 5.4.1.42661
# ---------------------------------------
# Host     : localhost
# Port     : 3306
# Database : maxter_koisk


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES latin1 */;

SET FOREIGN_KEY_CHECKS=0;

#
# Structure for the `formato_coreo` table : 
#

CREATE TABLE `formato_coreo` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `Nombre` VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
  `Precio` DECIMAL(11,2) NOT NULL,
  `Sufijo` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY USING BTREE (`id`) COMMENT ''
)ENGINE=InnoDB
AUTO_INCREMENT=4 AVG_ROW_LENGTH=5461 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
COMMENT=''
;

#
# Structure for the `formato_imagen` table : 
#

CREATE TABLE `formato_imagen` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) COLLATE utf8_general_ci NOT NULL,
  `precio` FLOAT NOT NULL,
  `ancho` INTEGER(11) NOT NULL,
  `alto` INTEGER(11) NOT NULL,
  `carpeta` VARCHAR(30) COLLATE utf8_general_ci NOT NULL,
  `orden` INTEGER(11) NOT NULL,
  PRIMARY KEY USING BTREE (`id`) COMMENT ''
)ENGINE=MyISAM
AUTO_INCREMENT=4 AVG_ROW_LENGTH=57 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
COMMENT=''
;

#
# Structure for the `formato_video` table : 
#

CREATE TABLE `formato_video` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) COLLATE utf8_general_ci NOT NULL,
  `precio` FLOAT NOT NULL,
  `carpeta` VARCHAR(30) COLLATE utf8_general_ci NOT NULL,
  `orden` INTEGER(11) NOT NULL,
  `Sufijo` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY USING BTREE (`id`) COMMENT ''
)ENGINE=MyISAM
AUTO_INCREMENT=3 AVG_ROW_LENGTH=42 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
COMMENT=''
;

#
# Structure for the `information` table : 
#

CREATE TABLE `information` (
  `key` VARCHAR(50) COLLATE latin1_general_ci NOT NULL,
  `Title` VARCHAR(255) COLLATE latin1_general_ci NOT NULL,
  `Value` TEXT COLLATE latin1_general_ci NOT NULL,
  `textarea` TINYINT(4) DEFAULT 0,
  `HelpMessage` TEXT COLLATE latin1_general_ci,
  PRIMARY KEY USING BTREE (`key`) COMMENT ''
)ENGINE=InnoDB
AVG_ROW_LENGTH=1092 CHARACTER SET 'latin1' COLLATE 'latin1_general_ci'
COMMENT=''
;

#
# Structure for the `pedidos` table : 
#

CREATE TABLE `pedidos` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) COLLATE utf8_general_ci NOT NULL,
  `apellido` VARCHAR(255) COLLATE utf8_general_ci NOT NULL,
  `telefono` VARCHAR(80) COLLATE utf8_general_ci NOT NULL,
  `total` FLOAT NOT NULL,
  `descripcion` LONGTEXT COLLATE utf8_general_ci NOT NULL,
  `estado` INTEGER(11) NOT NULL,
  `Fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Evento` VARCHAR(255) COLLATE utf8_general_ci NOT NULL,
  `Descuento` FLOAT NOT NULL DEFAULT 0,
  `pedido` LONGTEXT COLLATE utf8_general_ci,
  `extra` LONGTEXT COLLATE utf8_general_ci,
  `idVendedor` INTEGER(11) DEFAULT NULL,
  `idPresupuesto` INTEGER(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY USING BTREE (`id`) COMMENT '',
   INDEX `ix_ped_vend` USING BTREE (`idVendedor`) COMMENT '',
   INDEX `idPresupuesto` USING BTREE (`idPresupuesto`) COMMENT ''
)ENGINE=MyISAM
AUTO_INCREMENT=86 AVG_ROW_LENGTH=572 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
COMMENT=''
;

#
# Structure for the `vendedores` table : 
#

CREATE TABLE `vendedores` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `Vendedor` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `Clave` VARCHAR(50) COLLATE utf8_general_ci DEFAULT NULL,
  `Activo` TINYINT(4) NOT NULL DEFAULT 1,
  PRIMARY KEY USING BTREE (`id`) COMMENT '',
  UNIQUE INDEX `vendedores_idx1` USING BTREE (`Vendedor`) COMMENT ''
)ENGINE=InnoDB
AUTO_INCREMENT=4 AVG_ROW_LENGTH=5461 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
COMMENT=''
;

#
# Structure for the `presupuestos` table : 
#

CREATE TABLE `presupuestos` (
  `id` INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) COLLATE utf8_general_ci NOT NULL,
  `apellido` VARCHAR(255) COLLATE utf8_general_ci NOT NULL,
  `telefono` VARCHAR(255) COLLATE utf8_general_ci NOT NULL,
  `presupuesto` TEXT COLLATE utf8_general_ci NOT NULL,
  `pedido` TEXT COLLATE utf8_general_ci NOT NULL,
  `subtotal` FLOAT NOT NULL,
  `descuento` FLOAT NOT NULL,
  `total` FLOAT NOT NULL,
  `sena` FLOAT NOT NULL,
  `idVendedor` INTEGER(11) NOT NULL,
  PRIMARY KEY USING BTREE (`id`) COMMENT '',
   INDEX `idVendedor` USING BTREE (`idVendedor`) COMMENT '',
  CONSTRAINT `presupuestos_ibfk_1` FOREIGN KEY (`idVendedor`) REFERENCES `vendedores` (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=4 AVG_ROW_LENGTH=5461 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
COMMENT=''
;

#
# Structure for the `usuarios` table : 
#

CREATE TABLE `usuarios` (
  `idUsuario` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
  `Clave` VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
  `Nombre` VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
  `EMail` VARCHAR(255) COLLATE utf8_general_ci NOT NULL,
  `idLevel` INTEGER(11) NOT NULL,
  `UltimoAcceso` DATETIME DEFAULT NULL,
  `IP` VARCHAR(20) COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY USING BTREE (`idUsuario`) COMMENT '',
  UNIQUE INDEX `usuario` USING BTREE (`usuario`) COMMENT ''
)ENGINE=InnoDB
AUTO_INCREMENT=2 AVG_ROW_LENGTH=16384 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
COMMENT=''
;

#
# Structure for the `usuarios_ingresos` table : 
#

CREATE TABLE `usuarios_ingresos` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(50) COLLATE latin1_general_ci NOT NULL,
  `Fecha` DATETIME NOT NULL,
  `IP` VARCHAR(20) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY USING BTREE (`id`) COMMENT '',
   INDEX `usuario` USING BTREE (`usuario`) COMMENT ''
)ENGINE=InnoDB
AUTO_INCREMENT=29 AVG_ROW_LENGTH=585 CHARACTER SET 'latin1' COLLATE 'latin1_general_ci'
COMMENT=''
;

#
# Structure for the `usuarioslevelpermissions` table : 
#

CREATE TABLE `usuarioslevelpermissions` (
  `UserLevelID` INTEGER(11) NOT NULL,
  `TableName` VARCHAR(50) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `Permission` INTEGER(11) NOT NULL,
  PRIMARY KEY USING BTREE (`UserLevelID`, `TableName`) COMMENT ''
)ENGINE=MyISAM
AVG_ROW_LENGTH=24 CHARACTER SET 'latin1' COLLATE 'latin1_general_ci'
COMMENT=''
;

#
# Structure for the `usuarioslevels` table : 
#

CREATE TABLE `usuarioslevels` (
  `UserLevelID` INTEGER(11) NOT NULL,
  `UserLevelName` VARCHAR(50) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY USING BTREE (`UserLevelID`) COMMENT ''
)ENGINE=MyISAM
AVG_ROW_LENGTH=21 CHARACTER SET 'latin1' COLLATE 'latin1_general_ci'
COMMENT=''
;


INSERT INTO `information` (`key`, `Title`, `Value`, `textarea`, `HelpMessage`) VALUES

  ('claveautorizacion','Clave de autorización','321',0,'Clave de autorización para compras fuera de límite'),
  ('comisionesevento','Escala de comisiones por evento','',2,'Escala de comisiones por evento'),
  ('descuentosmaxpresu','Escala de descuentos de los presupuestos','',2,'Escala de descuentos maximos en porcentaje, autorizados en base al monto del presupuesto'),
  ('escalavfotoind','Escala de comisiones por vendedor: Foto Individual','',2,'Escala de comisiones por vendedor: Foto Individual'),
  ('escalavfotopresu','Escala de comisiones por vendedor: Foto Presupuesto','',2,'Escala de comisiones por vendedor: Foto Presupuesto'),
  ('escalavvideoind','Escala de comisiones por vendedor: Video Individual','',2,'Escala de comisiones por vendedor: Video Individual'),
  ('escalavvideopresu','Escala de comisiones por vendedor: Video Presupuesto','',2,'Escala de comisiones por vendedor: Video presupuesto'),
  ('evento','Nombre del evento','CIAD 1',0,'Ingresar el nombre del evento para el cual se venderán las fotos y videos'),
  ('filespath','Carpeta donde se grabaran las fotos y videos a vender','C:\\dev\\Maxi\\maxterproducciones.com\\ecom\\files_aero',0,'Ingrese la ruta completa donde se encuentran las fotos, videos y marcos disponibles, por ej.:\r\n\r\n<strong>c:\\maxter\\</strong>\r\n\r\nDentro de esta carpeta debe crear las siguientes carpetas:\r\n<ul>\r\n<li><strong>fotos/</strong>\r\nGuarde aqui las fotos y videos, puede crear subcarpetas para organizar las mismas.\r\n</li>\r\n<li><strong>marcos/</strong>\r\nGenere una carpeta por cada formato de imágen (utilizar el mismo nombre de carpeta que ingreso para cada formato de imágen), guarde PNG transparentes con la misma relación de aspecto que el formato de imágen para ser utilizados como marcos.\r\n</li>\r\n<li><strong>pedidos/</strong>\r\nAquí se guardarán los pedidos realizados.\r\n</li>\r\n</ul>'),
  ('horacorte','Hora de corte de entrega','19:00',0,'Hora de corte para las entregas, formato 24hs, ej: 09:00 o 15:00'),
  ('horadesde','Hora de comienzo de entrega','10:00',0,'Hora de comienzo para las entregas, formato 24hs, ej: 09:00 o 15:00'),
  ('horasretiro','Horas de procesamiento para entrega','3',0,'Cuantas horas deben transcurrir desde que se encarga hasta que se retira el pedido, ej: 3'),
  ('maxventapedido','Máximo venta de pedido','1000',0,'Limite de pedido'),
  ('previewseconds','Duración de la previsualización de videos','10',0,'Ingrese la cantidad de segundos que durará la previsualización de los videos'),
  ('textovideos','Texto a mostrar en la previsualización de videos','<p>\r\n\tTexto a <strong><span style=\"font-size:16px;\"><span style=\"color: rgb(255, 0, 0);\"><span style=\"background-color: rgb(255, 255, 0);\">mostrar en la previsualizaci&oacute;n de videosTexto a mostrar </span></span></span></strong>en la previsualizaci&oacute;n de videosTexto a mostrar en la previsualizaci&oacute;n de videosTexto a mostrar en la previsualizaci&oacute;n de videosTexto a mostrar en la previsualizaci&oacute;n de videos Texto a mostrar en la previsualizaci&oacute;n de videosTexto a mostrar en la previsualizaci&oacute;n de videosTexto a mostrar en la previsualizaci&oacute;n de videos</p>',1,'Texto a mostrar en la previsualización de videos');
COMMIT;


INSERT INTO `usuarios` (`idUsuario`, `usuario`, `Clave`, `Nombre`, `EMail`, `idLevel`, `UltimoAcceso`, `IP`) VALUES

  (1,'admin','admin','Administrador','info@maxterproducciones.com',-1,'2017-08-01 08:09:09','::1');
COMMIT;

#
# Data for the `usuarios_ingresos` table  (LIMIT -471,500)
#


#
# Data for the `usuarioslevelpermissions` table  (LIMIT -490,500)
#

INSERT INTO `usuarioslevelpermissions` (`UserLevelID`, `TableName`, `Permission`) VALUES

  (2,'formato_coreo',7),
  (2,'formato_imagen',7),
  (2,'formato_video',7),
  (2,'pedidos',15),
  (2,'pedidosvend',7),
  (2,'usuarios',0),
  (2,'usuarioslevels',0),
  (2,'usuarios_ingresos',0),
  (2,'vendedores',15);
COMMIT;

#
# Data for the `usuarioslevels` table  (LIMIT -496,500)
#

INSERT INTO `usuarioslevels` (`UserLevelID`, `UserLevelName`) VALUES

  (-1,'Administrador'),
  (1,'Usuario'),
  (2,'Cajero');
COMMIT;



INSERT INTO `information` (`key`, `Title`, `Value`, `textarea`, `HelpMessage`)
VALUES ('descuentosmax', 'Descuentos máximos pedidos individuales', '{"dd":["0"],"dh":["0"],"dm":["100"]}', '2', 'Escala de descuentos máximos para pedidos individuales');

ALTER TABLE `presupuestos`
ADD `estado` int(11) NULL DEFAULT '0';

ALTER TABLE `presupuestos`
ADD `evento` varchar(255) NULL;

ALTER TABLE `pedidos`
ADD `sena` float NULL DEFAULT '0';

COMMIT;




/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;