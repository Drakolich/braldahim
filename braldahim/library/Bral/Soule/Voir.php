<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Soule_Voir extends Bral_Soule_Soule
{

	function getNomInterne()
	{
		return "box_soule_interne";
	}

	function render()
	{
		return $this->view->render("soule/voir.phtml");
	}

	function getTitreAction()
	{
	}

	public function calculNbPa()
	{
	}

	function prepareCommun()
	{
		Zend_Loader::loadClass('Bral_Util_Lien');
		Zend_Loader::loadClass('Bral_Util_Soule');
		Zend_Loader::loadClass('SouleEquipe');
		Zend_Loader::loadClass('SouleMatch');
		Zend_Loader::loadClass('SouleTerrain');

		if ($this->request->get("id_terrain") != "") {
			$this->idTerrainEnCours = Bral_Util_Controle::getValeurIntVerif($this->request->get("id_terrain"));
		} else if ($this->idTerrainDefaut != null) {
			$this->idTerrainEnCours = $this->idTerrainDefaut;
		}
		if ($this->idTerrainEnCours == null || $this->idTerrainEnCours <= 0) {
			throw new Zend_Exception(get_class($this) . " idTerrainEnCours null" . $this->request->get("id_terrain"));
		}

		$this->niveauTerrainBraldun = floor($this->view->user->niveau_braldun / 10);

		$souleTerrainTable = new SouleTerrain();
		$terrainRowset = $souleTerrainTable->findByIdTerrain($this->idTerrainEnCours);
		$this->view->terrainCourant = $terrainRowset;

		$souleMatchTable = new SouleMatch();
		$matchs = $souleMatchTable->findEnCoursByIdTerrain($this->idTerrainEnCours);
		$this->matchEnCours = null;

		if ($matchs != null) {
			$this->matchEnCours = $matchs[0];
		} else if (count($matchs) > 1) {
			throw new Zend_Exception(" Bral_Soule_Voir - Erreur calcul match en cours. idTerrain:" . $this->idTerrainEnCours);
		}
		$this->calculInscription();
		Bral_Util_Soule::prepareEquipes($this->matchEnCours, $this->view, $this->view->terrainCourant["niveau_soule_terrain"]);

		$this->prepareMatch();

		if ($this->matchEnCours != null) {
			$this->prepareEvenements();
			$this->prepareMessages();
		}
	}

	function prepareFormulaire()
	{
	}

	function prepareResultat()
	{
	}

	function getListBoxRefresh()
	{
	}


	private function calculInscription()
	{
		$this->view->inscriptionPossible = false;
		$this->view->inscriptionNonPossibleInfo = "";

		$this->view->desinscriptionPossible = false;
		$this->view->deinscriptionNonPossibleInfo = "";

		$this->view->souleInfo = "";

		// on regarde si le joueur n'est pas déjà inscrit
		$souleEquipeTable = new SouleEquipe();
		$nombre = $souleEquipeTable->countNonDebuteByIdBraldun($this->view->user->id_braldun);

		if ($this->matchEnCours != null) { // s'il un match en cours
			$this->view->inscriptionNonPossibleInfo = "Il y a un match en cours sur ce terrain";
		} else if ($this->niveauTerrainBraldun != $this->view->terrainCourant["niveau_soule_terrain"] && $nombre == 0) {
			$this->view->inscriptionNonPossibleInfo = "Vous ne pouvez pas vous inscrire sur ce terrain qui n'est pas de votre niveau";
			/*} else if ($this->view->user->est_engage_braldun == "oui") {
							$this->view->inscriptionNonPossibleInfo = "Vous ne pouvez pas vous inscrire, vous êtes engagé";
							*/
		} else if ($this->view->user->est_soule_braldun == "oui") {
			$this->view->inscriptionNonPossibleInfo = "Vous ne pouvez pas vous inscrire, vous êtes déjà en plein match";
		} else if ($this->matchEnCours == null) { // s'il n'y a pas de match en cours

			if ($nombre == 0) { // si le joueur n'est pas déjà inscrit
				// on regarde s'il n'y a pas plus de 80 joueurs
				$nombreJoueurs = $souleEquipeTable->countNonDebuteByNiveauTerrain($this->niveauTerrainBraldun);
				if ($nombreJoueurs < $this->view->config->game->soule->max->joueurs) {
					$this->view->inscriptionPossible = true;
				}
			} else {
				$this->view->inscriptionNonPossibleInfo = "Vous êtes déjà inscrit à un match";

				$souleMatchTable = new SouleMatch();
				$matchs = $souleMatchTable->findNonDebuteByIdTerrain($this->idTerrainEnCours);

				if (count($matchs) == 1) {
					$this->view->inscriptionNonPossibleInfo .= " sur ce terrain";

					if ($matchs[0]["nb_jours_quota_soule_match"] == 0) {
						$this->view->desinscriptionPossible = true;
					} else {
						$this->view->deinscriptionNonPossibleInfo = "Le match va bientôt débuter, vous ne pouvez plus vous désinscrire";

						if ($matchs[0]["nb_jours_quota_soule_match"] == 1) {
							$this->view->souleInfo = "Le match va bientôt débuter après demain";
						} else if ($matchs[0]["nb_jours_quota_soule_match"] == 2) {
							$this->view->souleInfo = "Le match va bientôt débuter demain";
						}

					}
				} else {
					$this->view->inscriptionNonPossibleInfo .= " sur un autre terrain";
				}
			}
		}
	}

	private function prepareMatch()
	{
		$porteur = null;
		if ($this->matchEnCours != null && $this->matchEnCours["id_fk_joueur_ballon_soule_match"] != null) {
			$idPorteur = $this->matchEnCours["id_fk_joueur_ballon_soule_match"];
			$braldunTable = new Braldun();
			$braldun = $braldunTable->findById($idPorteur);
			if ($braldun != null) {
				$porteur = $braldun->toArray();
			}
		}

		$this->view->porteur = $porteur;
		$this->view->matchEnCours = $this->matchEnCours;
	}

	private function prepareEvenements()
	{
		Zend_Loader::loadClass("Evenement");
		$evenementTable = new Evenement();
		$rowset = $evenementTable->findByIdMatch($this->matchEnCours["id_soule_match"]);

		$tab = null;
		foreach ($rowset as $r) {
			$braldun = $r["prenom_braldun"] . " " . $r["nom_braldun"] . " (" . $r["id_braldun"] . ")";
			$tab[] = array("date_evenement" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y à H:i:s ', $r["date_evenement"]),
				"braldun_evenement" => $braldun,
				"details_evenement" => $r["details_evenement"]);
		}
		$this->view->evenements = $tab;
	}

	private function prepareMessages()
	{
		Zend_Loader::loadClass("SouleMessage");
		$souleMessageTable = new SouleMessage();
		$tab = null;

		if ($this->view->user->id_fk_soule_match_braldun == $this->matchEnCours["id_soule_match"]) {
			$rowset = $souleMessageTable->findByIdMatchAndCamp($this->matchEnCours["id_soule_match"], $this->view->user->soule_camp_braldun);

			foreach ($rowset as $r) {
				$braldun = $r["prenom_braldun"] . " " . $r["nom_braldun"] . " (" . $r["id_braldun"] . ")";
				$tab[] = array("date" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y à H:i:s ', $r["date_soule_message"]),
					"braldun" => $braldun,
					"message" => $r["message_soule_message"]);
			}
		}
		$this->view->souleMessages = $tab;
	}
}