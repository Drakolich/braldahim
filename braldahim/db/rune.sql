-- phpMyAdmin SQL Dump-- version 2.9.2-- http://www.phpmyadmin.net-- -- Serveur: localhost-- G�n�r� le : Mardi 06 Novembre 2007 � 22:47-- Version du serveur: 5.0.33-- Version de PHP: 5.2.0-- -- Base de donn�es: `braldahim`-- -- ---------------------------------------------------------- -- Structure de la table `rune`-- CREATE TABLE `rune` (  `id_rune` int(11) NOT NULL auto_increment,  `id_fk_type_rune` int(11) NOT NULL,  `x_rune` int(11) NOT NULL,  `y_rune` int(11) NOT NULL,  PRIMARY KEY  (`id_rune`),  KEY `xy_rune` (`x_rune`,`y_rune`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;