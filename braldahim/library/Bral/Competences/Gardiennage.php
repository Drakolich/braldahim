<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Gardiennage extends Bral_Competences_Competence
{

	function prepareCommun()
	{
		Zend_Loader::loadClass("Gardiennage");

		$this->tabJoursDebut = null;

		for ($i = 1; $i <= 10; $i++) {
			$this->tabJoursDebut[] =
				array("valeur" => date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $i, date("Y"))),
					"affichage" => date("d/m/Y", mktime(0, 0, 0, date("m"), date("d") + $i, date("Y"))));
			$this->tabJoursDebutValides[] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $i, date("Y")));
		}
	}

	function prepareFormulaire()
	{
		$tabGardiens = null;
		$gardiennageTable = new Gardiennage();
		$gardiens = $gardiennageTable->findGardiens($this->view->user->id_braldun);
		$gardiennageEnCours = $gardiennageTable->findGardiennageEnCours($this->view->user->id_braldun);

		foreach ($gardiens as $gardien) {
			$tabGardiens[] = array(
				"id_gardien" => $gardien["id_fk_gardien_gardiennage"],
				"nom_gardien" => $gardien["nom_braldun"],
				"prenom_gardien" => $gardien["prenom_braldun"]);
		}

		$nbJours = 0;

		$gardiennage30Jours = $gardiennageTable->findGardiennage30Jours($this->view->user->id_braldun);
		foreach ($gardiennage30Jours as $g) {
			$nbJours = $nbJours + $g["nb_jours_gardiennage"];
		}

		if ($nbJours >= 5) {
			$this->view->messageMax = "Vous avez déjà programmé 5 jours de gardiennage depuis 30 jours.";
		} elseif (count($gardiennageEnCours) < $this->view->config->game->gardiennage->nb_max_en_cours) {
			$this->view->tabJoursDebut = $this->tabJoursDebut;
			$this->view->tabGardiens = $tabGardiens;
			$this->view->nbEnCours = count($gardiennageEnCours);
			$this->view->nbMax = $this->view->config->game->gardiennage->nb_max_en_cours;
		} else {
			$this->view->messageMax = "Vous avez d&eacute;j&agrave; deux gardiennages en cours<br /><br /> Vous ne pouvez plus en  cr&eacute;er";
		}

	}

	function prepareResultat()
	{
		if ($this->request->get("valeur_1") == "nouveau") {
			$this->nouveauGardiennage();
			$this->view->tabGardiennage = $this->voirGardiennage();
			$this->view->tabGardiennageTrenteJours = $this->voirGardiennage(true);
		} elseif ($this->request->get("valeur_1") == "voir") {
			$this->view->tabGardiennage = $this->voirGardiennage();
			$this->view->tabGardiennageTrenteJours = $this->voirGardiennage(true);
		} else {
			throw new Zend_Exception(get_class($this) . " Action invalide : " . $this->request->get("valeur_1"));
		}
	}


	function getListBoxRefresh()
	{
		return null;
	}

	private function nouveauGardiennage()
	{
		Zend_Loader::loadClass('Zend_Filter');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Zend_Filter_StringTrim');
		Zend_Loader::loadClass('Zend_Filter_HtmlEntities');

		$filtreChaine = new Zend_Filter();
		$filtreChaine->addFilter(new Zend_Filter_StringTrim())
			->addFilter(new Zend_Filter_StripTags());

		$premierJour = $this->request->get("valeur_2");
		$nbJour = $this->request->get("valeur_3");
		$idGardienExistant = trim($this->request->get("valeur_4"));
		$idNouveauGardien = $this->request->get("valeur_5");
		$commentaire = mb_substr($filtreChaine->filter($this->request->get("valeur_6")), 0, 100);

		// Verification du premier jour
		if (!in_array($premierJour, $this->tabJoursDebutValides)) {
			throw new Zend_Exception(get_class($this) . " Premier jour invalide : " . $premierJour);
		}

		// Verification du nombre de jour
		if (intval($nbJour) == 0 || intval($nbJour) < -1 || intval($nbJour) > 5) {
			throw new Zend_Exception(get_class($this) . " Nombre de jour(s) invalide : " . $nbJour);
		}

		$idGardien = null;
		if (intval($idGardienExistant) != 0 && intval($idGardienExistant) != -1) {
			$idGardien = intval($idGardienExistant);
		} elseif (intval($idNouveauGardien) != 0) {
			$idGardien = intval($idNouveauGardien);
		} else {
			throw new Zend_Exception(get_class($this) . " Gardien invalide : existant:" . $idGardienExistant . " nouveau:" . $idNouveauGardien);
		}

		// Il ne faut pas que le gardien soit le joueur lui même
		if ($idGardien == $this->view->user->id_braldun) {
			throw new Zend_Exception(get_class($this) . " Gardien invalide : Vous ne devez pas être le gardien de vous même. Gardien:" . $idGardien . " Vous:" . $this->view->user->id_braldun);
		}

		$break = explode("-", $premierJour);
		$jour = $break[2];
		$mois = $break[1];
		$annee = $break[0];
		$dernierJour = date("Y-m-d", mktime(0, 0, 0, $mois, $jour + $nbJour - 1, $annee));
		$dernierJourTexte = date("d/m/Y", mktime(0, 0, 0, $mois, $jour + $nbJour - 1, $annee));
		$premierJourTexte = date("d/m/Y", mktime(0, 0, 0, $mois, $jour, $annee));

		$gardiennageTable = new Gardiennage();
		$data = array(
			'id_fk_braldun_gardiennage' => $this->view->user->id_braldun,
			'id_fk_gardien_gardiennage' => $idGardien,
			'date_debut_gardiennage' => $premierJour,
			'date_fin_gardiennage' => $dernierJour,
			'nb_jours_gardiennage' => $nbJour,
			'commentaire_gardiennage' => $commentaire
		);
		$gardiennageTable->insert($data);
		$this->view->nouveauGardiennage = true;

		$message = "[Ceci est un message automatique de gardiennage]" . PHP_EOL;
		$message .= $this->view->user->prenom_braldun . " " . $this->view->user->nom_braldun;
		$message .= " (" . $this->view->user->id_braldun . ") vous confie son braldun." . PHP_EOL;
		$message .= " Premier jour de garde : " . $premierJourTexte . PHP_EOL;
		$message .= " Dernier jour de garde (inclus) : " . $dernierJourTexte . PHP_EOL;
		$message .= " Nombre de jours : " . $nbJour . PHP_EOL;
		$message .= " Commentaire : " . $commentaire . PHP_EOL;

		$data = Bral_Util_Messagerie::envoiMessageAutomatique($this->view->user->id_braldun, $idGardien, $message, $this->view);
	}

	private function voirGardiennage($trenteJours = false)
	{
		$this->view->nouveauGardiennage = false;

		$gardiennageTable = new Gardiennage();
		if ($trenteJours) {
			$gardiennages = $gardiennageTable->findGardiennage30Jours($this->view->user->id_braldun);
		} else {
			$gardiennages = $gardiennageTable->findGardiennageEnCours($this->view->user->id_braldun);
		}

		$tabGardiennage = null;

		foreach ($gardiennages as $g) {
			$tabGardiennage[] = array(
				"id_gardien" => $g["id_fk_gardien_gardiennage"],
				"nom_gardien" => $g["nom_braldun"],
				"prenom_gardien" => $g["prenom_braldun"],
				"date_debut" => $g["date_debut_gardiennage"],
				"nb_jours" => $g["nb_jours_gardiennage"],
				"commentaire" => $g["commentaire_gardiennage"]);
		}
		return $tabGardiennage;
	}
}