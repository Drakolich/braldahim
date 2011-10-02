<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Profil extends Bral_Box_Box
{

	function getTitreOnglet()
	{
		return "Profil";
	}

	function getNomInterne()
	{
		return "box_profil";
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function getChargementInBoxes()
	{
		return false;
	}

	function render()
	{
		//if ($this->view->affichageInterne) {
		$this->data();
		//}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render("interface/profil.phtml");
	}

	function data()
	{
		Zend_Loader :: loadClass("Bral_Helper_Profil");
		Zend_Loader :: loadClass("Bral_Util_Tour");
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->vue_nb_cases = Bral_Util_Commun::getVueBase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		$this->view->armure_totale = $this->view->user->armure_naturelle_braldun + $this->view->user->armure_equipement_braldun + $this->view->user->armure_bm_braldun;
		if ($this->view->armure_totale < 0) {
			$this->view->armure_totale = 0;
		}

		$tmp = "" . substr($this->view->user->poids_transporte_braldun, 0, 6);

		// Correction Bug sur Float, sur l'action semer
		//if (strlen($this->view->user->poids_transporte_braldun) > 10 && strpos($tmp, ".") !== false) {
		if (strlen($this->view->user->poids_transporte_braldun) > 10) {

			/*
							* tmpInit=10.720000000000000639tmp=10.720tmp2a=10tmp2b=10.72poidsTransporteBraldunCorrige=10.72
							* affiche => 10.720000000000000639  / 17 !!
							*/
			//echo "tmpInit=".$this->view->user->poids_transporte_braldun;
			//$tmp = "".substr($this->view->user->poids_transporte_braldun, 0, 6);
			//echo "tmp=".$tmp;
			//$pos = strpos($tmp, ".");
			//$tmp2 = "".substr($tmp, 0, $pos);
			//echo "tmp2a=".$tmp2;
			//$tmp2 .= "".substr($tmp, $pos, 3);
			//echo "tmp2b=".$tmp2;
			//$this->view->poidsTransporteBraldunCorrige = $tmp2;//$tmp;
			//echo "poidsTransporteBraldunCorrige=".$this->view->poidsTransporteBraldunCorrige ;
			//if (strlen($this->view->poidsTransporteBraldunCorrige) > 10) {
			//	$this->view->poidsTransporteBraldunCorrige = '?';
			//}
			/*
						   ====> ne fonctionne pas !
						   */
			$this->view->poidsTransporteBraldunCorrige = '?';
		} else {
			//echo "AA=".strlen($this->view->user->poids_transporte_braldun);
			$this->view->poidsTransporteBraldunCorrige = $this->view->user->poids_transporte_braldun;
		}
	}
}
