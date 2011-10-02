<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_Charrette
{

	public static function afficheBarrePoids($charrette)
	{
		$largeur = (($charrette["poids_transporte"] * 100) / $charrette["poids_transportable"]) * 2;
		$titre = "Poids transportable";
		$texte = "La charrette porte actuellement " . $charrette["poids_transporte"] . " Kg.<br />";
		$texte .= "Elle peut porter jusqu'&agrave; " . $charrette["poids_transportable"] . " Kg.<br />";

		if ($largeur > 200) {
			$largeur = 200;
		}
		$retour = "<div class='barre_poids_charrette braltip'>" . Bral_Helper_Tooltip::render($texte, $titre);
		$retour .= "<img src='" . Zend_Registry::get('config')->url->static . "/images/barres/barre_poids.gif' height='13px' width=" . $largeur . "></div>";

		return $retour;
	}

	public static function afficheBarreDurabilite($charrette)
	{

		$largeur = (($charrette["durabilite_actuelle"] * 100) / $charrette["durabilite_max"]) * 2;
		$titre = "Durabilité";
		$texte = "La charrette a une durabilit&eacute; maximum de " . $charrette["durabilite_max"] . ".<br />";
		$texte .= "Il lui reste " . $charrette["durabilite_actuelle"] . ".<br />";
		$texte .= "Elle sera automatiquement d&eacute;truite &agrave; une durabilit&eacute; de 0 si vous ne la faite pas r&eacute;parer avant.<br />";

		if ($largeur > 200) {
			$largeur = 200;
		}
		$retour = "<div class='barre_poids_charrette braltip'>" . Bral_Helper_Tooltip::render($texte, $titre);
		$retour .= "<img src='" . Zend_Registry::get('config')->url->static . "/images/barres/barre_poids.gif' height='13px' width=" . $largeur . " style='top-10px'></div>";

		return $retour;
	}
}
