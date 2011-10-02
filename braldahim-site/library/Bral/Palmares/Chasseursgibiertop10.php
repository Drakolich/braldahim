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
class Bral_Palmares_Chasseursgibiertop10 extends Bral_Palmares_Box
{

	function getTitreOnglet()
	{
		return "Top 10";
	}

	function getNomInterne()
	{
		return "box_onglet_chasseursgibiertop10";
	}

	function getNomClasse()
	{
		return "chasseursgibiertop10";
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function render()
	{
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/chasseursgibier_top10.phtml");
	}

	private function prepare()
	{
		Zend_Loader::loadClass("Evenement");
		$mdate = $this->getTabDateFiltre();
		$evenementTable = new Evenement();
		$type = $this->view->config->game->evenements->type->killgibier;
		$rowset = $evenementTable->findTop10($mdate["dateDebut"], $mdate["dateFin"], $type, true);
		$this->view->top10 = $rowset;
	}
}