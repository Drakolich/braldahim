<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Util_Lien
{

	public static function remplaceBaliseParNomEtJs($texteOriginal, $avecJs = true)
	{
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("Braldun");

		// Monstre
		$texte = preg_replace_callback("/\[m(.*?)]/si",
			create_function('$matches', self::getFunctionMonstre($avecJs)), $texteOriginal);

		// Braldun
		$texte = preg_replace_callback("/\[b(.*?)]/si",
			create_function('$matches', self::getFunctionBraldun($avecJs)), $texte);

		// Lieu
		$texte = preg_replace_callback("/\[l(.*?)]/si",
			create_function(
				'$matches', '
			$l = new Lieu();
			$nom = $l->findNomById($matches[1]);
			return $nom;'
			)
			, $texte);

		return $texte;
	}

	private static function getFunctionMonstre($avecJs = true)
	{
		$retour = '$m = new Monstre();';
		$retour .= '$nom = "";';
		if ($avecJs) $retour .= '$nom = "<label class=\'alabel\' onclick=\"javascript:ouvrirWin(\'' . Zend_Registry::get('config')->url->game . '/voir/monstre/?monstre=".$matches[1]."\');\">";';
		$retour .= '$nom .= $m->findNomById($matches[1]);';
		if ($avecJs) $retour .= '$nom .= "</label>";';
		$retour .= 'return $nom;';
		return $retour;
	}

	private static function getFunctionBraldun($avecJs = true)
	{
		$retour = '$h = new Braldun();';
		$retour .= '$nom = "";';
		if ($avecJs) $retour .= '$nom = "<label class=\'alabel\' onclick=\"javascript:ouvrirWin(\'' . Zend_Registry::get('config')->url->game . '/voir/braldun/?braldun=".$matches[1]."\');\">";';
		$retour .= '$nom .= $h->findNomById($matches[1]);';
		if ($avecJs) $retour .= '$nom .= "</label>";';
		$retour .= 'return $nom;';
		return $retour;
	}

	public static function getJsBraldun($id, $texte)
	{
		return "<label class='alabel' onclick=\"javascript:ouvrirWin(''" . Zend_Registry::get('config')->url->game . "/voir/braldun/?braldun=" . $id . "');\">" . $texte . "</label>";
	}
}
