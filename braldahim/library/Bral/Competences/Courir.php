<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Courir extends Bral_Competences_Competence
{

	function prepareCommun()
	{
		Zend_Loader::loadClass('Palissade');
		Zend_Loader::loadClass('Bral_Util_Commun');
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass('Eau');
		Zend_Loader::loadClass('Tunnel');

		$charretteTable = new Charrette();
		$nombreCharrette = $charretteTable->countByIdBraldun($this->view->user->id_braldun);

		/*
				   * Si le Braldûn n'a pas de PA, on ne fait aucun traitement
				   */
		$this->calculNbPa();
		if ($this->view->assezDePa = false) {
			return;
		}

		$this->view->possedeCharrette = false;
		if ($nombreCharrette == 1) {
			$this->view->possedeCharrette = true;

			Zend_Loader::loadClass("Bral_Util_Charrette");
			$this->view->courirPossible = Bral_Util_Charrette::calculCourrirChargerPossible($this->view->user->id_braldun);
			if ($this->view->courirPossible == false) {
				return;
			}
		} else if ($nombreCharrette > 1) {
			throw new Zend_Exception(get_class($this) . " NB Charrette invalide idh:" . $this->view->user->id_braldun);
		}

		$this->view->estEngage = false;
		if ($this->view->user->est_engage_braldun == "oui") {
			$this->view->courirPossible = false;
			$this->view->estEngage = true;
			return;
		}

		$this->view->courirPossible = false;

		$environnement = Bral_Util_Commun::getEnvironnement($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		$eauTable = new Eau();
		$eaux = $eauTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		$estSurEau = false;
		if (count($eaux) == 1) {
			$estSurEau = true;
		}

		$this->view->nb_cases = 1;
		if ($environnement == "plaine" && $estSurEau == false) {
			$this->distance = 12;
		} else {
			$this->distance = 6;
		}

		$this->x_min = $this->view->user->x_braldun - $this->distance;
		$this->x_max = $this->view->user->x_braldun + $this->distance;
		$this->y_min = $this->view->user->y_braldun - $this->distance;
		$this->y_max = $this->view->user->y_braldun + $this->distance;

		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($this->x_min, $this->y_min, $this->x_max, $this->y_max, $this->view->user->z_braldun);

		$eaux = $eauTable->selectVue($this->x_min, $this->y_min, $this->x_max, $this->y_max, $this->view->user->z_braldun, false);

		Zend_Loader::loadClass("Zone");
		$zoneTable = new Zone();
		$zones = $zoneTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		// La requete ne doit renvoyer qu'une seule case
		if (count($zones) == 1) {
			$zone = $zones[0];
		} else {
			throw new Zend_Exception("Bral_Competences_Courir::prepareCommun : Nombre de case invalide");
		}

		$tunnels = null;
		if ($zone["est_mine_zone"] == "oui") {
			$tunnelTable = new Tunnel();
			$tunnels = $tunnelTable->selectVue($this->x_min, $this->y_min, $this->x_max, $this->y_max, $this->view->user->z_braldun);
		}

		$this->tabValidationEauPalissade = null;
		for ($j = $this->distance; $j >= -$this->distance; $j--) {
			for ($i = -$this->distance; $i <= $this->distance; $i++) {
				$x = $this->view->user->x_braldun + $i;
				$y = $this->view->user->y_braldun + $j;
				$this->tabValidationEauPalissade[$x][$y] = true;

				if ($zone["est_mine_zone"] == "oui") { // dans une mine
					$tunnelOk = false;
					foreach ($tunnels as $t) {
						if ($t["x_tunnel"] == $x && $t["y_tunnel"] == $y) { // tunnel trouvé
							$tunnelOk = true;
							break;
						}
					}
					if ($tunnelOk == false) { // si pas de tunnel trouvé => non accessible
						$this->tabValidationEauPalissade[$x][$y] = false;
					}
				}
			}
		}
		foreach ($palissades as $p) {
			$this->tabValidationEauPalissade[$p["x_palissade"]][$p["y_palissade"]] = false;
		}
		foreach ($eaux as $e) {
			$this->tabValidationEauPalissade[$e["x_eau"]][$e["y_eau"]] = false;
		}

		$defautChecked = false;

		for ($j = $this->view->nb_cases; $j >= -$this->view->nb_cases; $j--) {
			$change_level = true;
			for ($i = -$this->view->nb_cases; $i <= $this->view->nb_cases; $i++) {
				$x = $this->view->user->x_braldun + $i;
				$y = $this->view->user->y_braldun + $j;

				if ($j == 1 && $i == 0) {
					$display = " Vers le Nord";
				} elseif ($j == -1 && $i == 0) {
					$display = " Vers le Sud";
				} elseif ($j == 1 && $i == 1) {
					$display = " Vers le Nord Est";
				} elseif ($j == 1 && $i == -1) {
					$display = " Vers le Nord Ouest";
				} elseif ($j == 0 && $i == 1) {
					$display = " Vers l'Est";
				} elseif ($j == 0 && $i == -1) {
					$display = " Vers l'Ouest";
				} elseif ($j == -1 && $i == 1) {
					$display = " Vers le Sud Est";
				} elseif ($j == -1 && $i == -1) {
					$display = " Vers le Sud Ouest";
				} else {
					$display = "";
				}

				if (($j == 0 && $i == 0) == false) { // on n'affiche pas de boutons dans la case du milieu
					$valid = true;
				} else {
					$valid = false;
				}

				if ($x < $this->view->config->game->x_min || $x > $this->view->config->game->x_max
					|| $y < $this->view->config->game->y_min || $y > $this->view->config->game->y_max
				) { // on n'affiche pas de boutons dans la case du milieu
					$valid = false;
				}

				// on regarde s'il n'y a pas de palissade
				if ($this->tabValidationEauPalissade[$x][$y] === false) {
					$valid = false;
				}

				if ($valid === true && $defautChecked == false) {
					$default = "checked";
					$defautChecked = true;
					$this->view->courirPossible = true;
				} else {
					$default = "";
				}

				$tab[] = array("x_offset" => $i,
					"y_offset" => $j,
					"default" => $default,
					"display" => $display,
					"change_level" => $change_level, // nouvelle ligne dans le tableau
					"valid" => $valid);

				$tabValidation[$i][$j] = $valid;

				if ($change_level) {
					$change_level = false;
				}
			}
		}
		$this->view->tableau = $tab;
		$this->tableauValidation = $tabValidation;
		$this->view->distance = $this->distance;
	}

	function prepareFormulaire()
	{
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat()
	{
		$x_y = $this->request->get("valeur_1");
		list ($offset_x, $offset_y) = preg_split("/h/", $x_y);

		if ($offset_x < -$this->view->nb_cases || $offset_x > $this->view->nb_cases) {
			throw new Zend_Exception(get_class($this) . " Deplacement X impossible : " . $offset_x);
		}

		if ($offset_y < -$this->view->nb_cases || $offset_y > $this->view->nb_cases) {
			throw new Zend_Exception(get_class($this) . " Deplacement Y impossible : " . $offset_y);
		}

		if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
			throw new Zend_Exception(get_class($this) . " Deplacement XY impossible : " . $offset_x . $offset_y);
		}

		$this->calculPalissade($offset_x, $offset_y);

		$this->view->user->x_braldun = $this->view->user->x_braldun + $this->offset_x_calcul;
		$this->view->user->y_braldun = $this->view->user->y_braldun + $this->offset_y_calcul;

		Zend_Loader::loadClass("Bral_Util_Crevasse");
		$this->view->estCrevasseEvenement = Bral_Util_Crevasse::calculCrevasse($this->view->user);

		$id_type = $this->view->config->game->evenements->type->deplacement;
		$details = "[b" . $this->view->user->id_braldun . "] a couru";
		$this->setDetailsEvenement($details, $id_type);
		$this->setEvenementQueSurOkJet1(false);

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculFinMatchSoule();
		$this->majBraldun();

		Zend_Loader::loadClass("Bral_Util_Filature");
		Bral_Util_Filature::action($this->view->user, $this->view);

		if ($this->view->user->est_soule_braldun == "oui") {
			Zend_Loader::loadClass("Bral_Util_Soule");
			Bral_Util_Soule::deplacerAvecBallon($this->view->user, $this->offset_x_calcul, $this->offset_y_calcul);
		}
	}

	function getListBoxRefresh()
	{
		$tab = array("box_vue", "box_lieu");
		if ($this->view->user->est_soule_braldun == "oui") {
			$tab[] = "box_soule";
		}
		return $this->constructListBoxRefresh($tab);
	}

	private function calculPalissade($offset_x, $offset_y)
	{

		$x = $this->view->user->x_braldun;
		$y = $this->view->user->y_braldun;

		$k = 0;
		$this->view->eauPalissadeRencontree = false;

		for ($i = 1; $i <= $this->distance; $i++) {
			if ($this->tabValidationEauPalissade[$x + $i * $offset_x][$y + $i * $offset_y] == false
				|| $x + $i * $offset_x < $this->view->config->game->x_min
				|| $x + $i * $offset_x > $this->view->config->game->x_max
				|| $y + $i * $offset_y < $this->view->config->game->y_min
				|| $y + $i * $offset_y > $this->view->config->game->y_max
			) {
				$k = $i - 1;
				$this->view->eauPalissadeRencontree = true;
				break;
			} else {
				$k = $i;
			}
		}
		if ($k <> 0) {
			$this->offset_x_calcul = $k * $offset_x;
			$this->offset_y_calcul = $k * $offset_y;
		} else {
			$this->offset_x_calcul = $offset_x;
			$this->offset_y_calcul = $offset_y;
		}
	}
}