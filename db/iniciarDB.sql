-- Adminer 4.8.1 MySQL 5.5.5-10.4.21-MariaDB-log dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `turnos`;
CREATE DATABASE `turnos` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `turnos`;

DROP TABLE IF EXISTS `entidads`;
CREATE TABLE `entidads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `detalle` varchar(300) NOT NULL,
  `solicitadoPor` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  CONSTRAINT `entidads_ibfk_2` FOREIGN KEY (`username`) REFERENCES `users` (`id`) ON DELETE NO ACTION,
  CONSTRAINT `entidads_ibfk_3` FOREIGN KEY (`username`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `id_grupo` bit(1) NOT NULL,
  `token` int(11) NOT NULL,
  `codigo` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `prestadoras`;
CREATE TABLE `prestadoras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cuit` varchar(20) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



DROP TABLE IF EXISTS `profesionales`;
CREATE TABLE `profesionales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dni` int(8) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 2023-01-31 21:29:53