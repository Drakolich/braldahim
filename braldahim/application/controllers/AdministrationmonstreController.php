<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class AdministrationmonstreController extends Zend_Controller_Action
{

	private $_tabCreation = null;

	function init()
	{
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlAdmin();

		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');

		Zend_Loader::loadClass('ReferentielMonstre');
		Zend_Loader::loadClass('GroupeMonstre');
		Zend_Loader::loadClass('Monstre');
		Zend_Loader::loadClass('TailleMonstre');
		Zend_Loader::loadClass('TypeMonstre');
		Zend_Loader::loadClass('Ville');
		Zend_Loader::loadClass('Zone');
		Zend_Loader::loadClass('Bral_Util_Niveau');

		Zend_Loader::loadClass('Zend_Filter');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Zend_Filter_StringTrim');

		Zend_Loader::loadClass("Bral_Util_ConvertDate");

		$this->prepareCommun();
	}

	function indexAction()
	{
		$this->render();
	}

	function creationAction()
	{
		$creation = false;
		if ($this->_request->isPost()) {
			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());

			$id_fk_type_ref_monstre = $filter->filter($this->_request->getPost('id_type'));
			$x_min_zone = $filter->filter($this->_request->getPost('x_min_zone'));
			$x_max_zone = $filter->filter($this->_request->getPost('x_max_zone'));
			$y_min_zone = $filter->filter($this->_request->getPost('y_min_zone'));
			$y_max_zone = $filter->filter($this->_request->getPost('y_max_zone'));

			$x_min_position = null;
			$x_max_position = null;
			$y_min_position = null;
			$y_max_position = null;
			$x_position = null;
			$y_position = null;

			if ($this->_request->getPost('x_min_position') != "") {
				$x_min_position = $filter->filter($this->_request->getPost('x_min_position'));
			}
			if ($this->_request->getPost('x_max_position') != "") {
				$x_max_position = $filter->filter($this->_request->getPost('x_max_position'));
			}
			if ($this->_request->getPost('y_min_position') != "") {
				$y_min_position = $filter->filter($this->_request->getPost('y_min_position'));
			}
			if ($this->_request->getPost('y_max_position') != "") {
				$y_max_position = $filter->filter($this->_request->getPost('y_max_position'));
			}
			if ($this->_request->getPost('x_position') != "") {
				$x_position = $filter->filter($this->_request->getPost('x_position'));
			}
			if ($this->_request->getPost('y_position') != "") {
				$y_position = $filter->filter($this->_request->getPost('y_position'));
			}

			$nombre = $filter->filter($this->_request->getPost('nombre'));

			if (((int)$id_fk_type_ref_monstre . "" != $id_fk_type_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. id_fk_type_ref_monstre : " . $id_fk_type_ref_monstre);
			}

			if (((int)$x_min_zone . "" != $x_min_zone . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. x_min_zone : " . $x_min_zone);
			}
			if (((int)$x_max_zone . "" != $x_max_zone . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. x_max_zone : " . $x_max_zone);
			}
			if (((int)$y_min_zone . "" != $y_min_zone . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. y_min_zone : " . $y_min_zone);
			}
			if (((int)$y_max_zone . "" != $y_max_zone . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. y_max_zone : " . $y_max_zone);
			}

			if ($x_min_position != "" && ((int)$x_min_position . "" != $x_min_position . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. x_min_position : " . $x_min_position);
			}
			if ($x_max_position != "" && ((int)$x_max_position . "" != $x_max_position . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. x_max_position : " . $x_max_position);
			}
			if ($y_min_position != "" && ((int)$y_min_position . "" != $y_min_position . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. y_min_position : " . $y_min_position);
			}
			if ($y_max_position != "" && ((int)$y_max_position . "" != $y_max_position . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. y_max_position : " . $y_max_position);
			}
			if ($x_position != "" && ((int)$x_position . "" != $x_position . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. x_position : " . $x_position);
			}
			if ($y_position != "" && ((int)$y_position . "" != $y_position . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. y_position : " . $y_position);
			}

			$referenceCourante = $this->recupereReferenceMonstre($id_fk_type_ref_monstre);
			$this->view->nb_creation_monstres = 0;
			$this->view->nb_creation_groupes_monstres = 0;
			$creation = true;
			if ($referenceCourante["id_type_groupe_monstre"] > 1) { //1 => type Solitaire

				for ($i = 1; $i < $nombre; $i++) {
					$nb_membres = Bral_Util_De::get_de_specifique($referenceCourante["nb_membres_min"], $referenceCourante["nb_membres_max"]);
					$i = $i + $nb_membres;
					$x_min_groupe = Bral_Util_De::get_de_specifique($x_min_zone, $x_max_zone);
					$y_min_groupe = Bral_Util_De::get_de_specifique($y_min_zone, $y_max_zone);
					if ($referenceCourante["id_type_groupe_monstre"] > 2) { //2 => nuée : tous sur la même case
						$x_max_groupe = $x_min_groupe + 4;
						$y_max_groupe = $y_min_groupe + 4;
					} else {
						$x_max_groupe = $x_min_groupe;
						$y_max_groupe = $y_min_groupe;
					}
					$id_groupe = $this->creationGroupe($referenceCourante["id_type_groupe_monstre"], $nb_membres);
					$num_role_a = Bral_Util_De::get_de_specifique(1, $nb_membres);
					$num_role_b = Bral_Util_De::get_de_specifique(1, $nb_membres);
					while ($num_role_a == $num_role_b) {
						$num_role_b = Bral_Util_De::get_de_specifique(1, $nb_membres);
					}
					for ($j = 1; $j <= $nb_membres; $j++) {
						$est_role_a = false;
						$est_role_b = false;
						if ($j == $num_role_a) {
							$est_role_a = true;
						}
						if ($j == $num_role_b) {
							$est_role_b = true;
						}

						$this->creationCalcul($referenceCourante, $x_min_groupe, $x_max_groupe, $y_min_groupe, $y_max_groupe, $x_min_position, $x_max_position, $y_min_position, $y_max_position, $x_position, $y_position, $id_groupe, $est_role_a, $est_role_b);
					}
				}
			} else {
				// insertion de solitaires
				for ($i = 1; $i <= $nombre; $i++) {
					$this->creationCalcul($referenceCourante, $x_min_zone, $x_max_zone, $y_min_zone, $y_max_zone, $x_min_position, $x_max_position, $y_min_position, $y_max_position, $x_position, $y_position);
				}
				$this->_tabCreation["groupesMonstres"] = null;
			}
			$this->view->tabCreation = $this->_tabCreation;
		}

		$this->view->creation = $creation;
		$this->render();
	}

	private function recupereReferenceMonstre($id_fk_type_ref_monstre, $taille = 1)
	{
		$referenceCourante = null;
		foreach ($this->view->refMonstre as $r) {
			if (($id_fk_type_ref_monstre == $r["id_type_monstre"]) && ((int)$taille == (int)$r["id_taille_monstre"])) {
				$referenceCourante = $r;
				break;
			}
		}

		if ($referenceCourante == null) {
			throw new Zend_Exception(get_class($this) . " creationCalcul referenceCourante invalide. id_fk_type_ref_monstre=" . $id_fk_type_ref_monstre . " taille=" . $taille);
		}
		return $referenceCourante;
	}

	private function creationGroupe($id_type, $nb_membres)
	{
		$data = array(
			"id_fk_type_groupe_monstre" => $id_type,
			"date_creation_groupe_monstre" => date("Y-m-d H:i:s"),
			"id_fk_braldun_cible_groupe_monstre" => null,
			"nb_membres_max_groupe_monstre" => $nb_membres,
			"nb_membres_restant_groupe_monstre" => $nb_membres,
			"phase_tactique_groupe_monstre" => 0,
			"id_role_a_groupe_monstre" => null,
			"id_role_b_groupe_monstre" => null
		);

		$groupeMonstreTable = new GroupeMonstre();
		$id_groupe = $groupeMonstreTable->insert($data);
		$data["id_groupe_monstre"] = $id_groupe;
		$this->_tabCreation["groupesMonstres"][] = $data;
		return $id_groupe;
	}

	private function creationCalcul($referenceCourante, $x_min_zone, $x_max_zone, $y_min_zone, $y_max_zone, $x_min_position, $x_max_position, $y_min_position, $y_max_position, $x_position, $y_position, $id_groupe_monstre = null, $est_role_a = false, $est_role_b = false)
	{

		$id_fk_taille_monstre = $this->creationCalculTaille();
		$referenceCourante = $this->recupereReferenceMonstre($referenceCourante["id_type_monstre"], $id_fk_taille_monstre);

		$id_fk_type_monstre = $referenceCourante["id_type_monstre"];
		$id_type_groupe_monstre = $referenceCourante["id_type_groupe_monstre"];

		$niveau_monstre = Bral_Util_De::get_de_specifique($referenceCourante["niveau_min"], $referenceCourante["niveau_max"]);

		if ($x_position != null && $y_position != null) {
			$x_monstre = $x_position;
			$y_monstre = $y_position;
		} else {
			$x_monstre = Bral_Util_De::get_de_specifique($x_min_zone, $x_max_zone);
			$y_monstre = Bral_Util_De::get_de_specifique($y_min_zone, $y_max_zone);
		}

		$niveau_force = Bral_Util_De::get_de_specifique($referenceCourante["min_niveau_force"], $referenceCourante["max_niveau_force"]);
		$niveau_sagesse = Bral_Util_De::get_de_specifique($referenceCourante["min_niveau_sagesse"], $referenceCourante["max_niveau_sagesse"]);
		$niveau_agilite = Bral_Util_De::get_de_specifique($referenceCourante["min_niveau_agilite"], $referenceCourante["max_niveau_agilite"]);
		$niveau_vigueur = Bral_Util_De::get_de_specifique($referenceCourante["min_niveau_vigueur"], $referenceCourante["max_niveau_vigueur"]);

		$bm_force = $referenceCourante["bm_force"];
		$bm_sagesse = $referenceCourante["bm_sagesse"];
		$bm_agilite = $referenceCourante["bm_agilite"];
		$bm_vigueur = $referenceCourante["bm_vigueur"];

		$force_base_monstre = $this->view->config->game->inscription->force_base + $niveau_force;
		$sagesse_base_monstre = $this->view->config->game->inscription->sagesse_base + $niveau_sagesse;
		$agilite_base_monstre = $this->view->config->game->inscription->agilite_base + $niveau_agilite;
		$vigueur_base_monstre = $this->view->config->game->inscription->vigueur_base + $niveau_vigueur;

		$bm_attaque = $referenceCourante["bm_attaque"];
		$bm_defense = $referenceCourante["bm_defense"];
		$bm_degat = $referenceCourante["bm_degat"];

		//REG
		$regeneration_monstre = floor(($niveau_sagesse / 4) + 1);

		//ARMNAT
		$armure_naturelle_monstre = floor(($force_base_monstre + $vigueur_base_monstre) / 5);

		//DLA
		$dla_monstre = Bral_Util_ConvertDate::get_time_from_minutes(720 - 10 * $niveau_sagesse);
		$date_fin_tour_monstre = Bral_Util_ConvertDate::get_date_add_time_to_date(date("Y-m-d H:i:s"), $dla_monstre);

		//PV
		$pv_restant_monstre = 20 + $niveau_vigueur * 4;

		// Vue
		$vue_monstre = $referenceCourante["vue"];

		$data = array(
			"id_fk_type_monstre" => $id_fk_type_monstre,
			"id_fk_taille_monstre" => $id_fk_taille_monstre,
			"id_fk_groupe_monstre" => $id_groupe_monstre,
			"x_monstre" => $x_monstre,
			"y_monstre" => $y_monstre,
			"x_direction_monstre" => $x_monstre,
			"y_direction_monstre" => $y_monstre,
			"x_min_monstre" => $x_min_position,
			"x_max_monstre" => $x_max_position,
			"y_min_monstre" => $y_min_position,
			"y_max_monstre" => $y_max_position,
			"id_fk_braldun_cible_monstre" => null,
			"pv_restant_monstre" => $pv_restant_monstre,
			"pv_max_monstre" => $pv_restant_monstre,
			"niveau_monstre" => $niveau_monstre,
			"vue_monstre" => $vue_monstre,
			"force_base_monstre" => $force_base_monstre,
			"force_bm_monstre" => 0,
			"force_bm_init_monstre" => $bm_force,
			"agilite_base_monstre" => $agilite_base_monstre,
			"agilite_bm_monstre" => 0,
			"agilite_bm_init_monstre" => $bm_agilite,
			"sagesse_base_monstre" => $sagesse_base_monstre,
			"sagesse_bm_monstre" => 0,
			"sagesse_bm_init_monstre" => $bm_sagesse,
			"vigueur_base_monstre" => $vigueur_base_monstre,
			"vigueur_bm_monstre" => 0,
			"vigueur_bm_init_monstre" => $bm_vigueur,
			"bm_init_attaque_monstre" => $bm_attaque,
			"bm_init_defense_monstre" => $bm_defense,
			"bm_init_degat_monstre" => $bm_degat,
			"regeneration_monstre" => $regeneration_monstre,
			"armure_naturelle_monstre" => $armure_naturelle_monstre,
			"date_fin_tour_monstre" => $date_fin_tour_monstre,
			"duree_base_tour_monstre" => $dla_monstre,
			"nb_kill_monstre" => 0,
			"date_creation_monstre" => date("Y-m-d H:i:s"),
			"est_mort_monstre" => 'non',
			"pa_monstre" => $this->view->config->game->pa_max,
		);

		$monstreTable = new Monstre();
		$id_monstre = $monstreTable->insert($data);

		$data["id_monstre"] = $id_monstre;
		$data["taille"] = $referenceCourante["taille"];
		$data["nom_type"] = $referenceCourante["nom_type"];

		$this->_tabCreation["monstres"][] = $data;

		// mise à jour des roles
		if (($est_role_a === true) || ($est_role_b === true)) {
			if ($est_role_a) {
				$data = array(
					"id_role_a_groupe_monstre" => $id_monstre,
					"x_direction_groupe_monstre" => $x_monstre,
					"y_direction_groupe_monstre" => $y_monstre,
					"date_fin_tour_groupe_monstre" => $date_fin_tour_monstre,
				);
			}
			if ($est_role_b) {
				$data = array("id_role_b_groupe_monstre" => $id_monstre);
			}
			$groupeMonstreTable = new GroupeMonstre();
			$where = "id_groupe_monstre=" . $id_groupe_monstre;
			$groupeMonstreTable->update($data, $where);
		}
	}

	private function creationCalculTaille()
	{
		$id_taille = null;

		$n = Bral_Util_De::get_de_specifique(1, 100);
		$total = 0;
		foreach ($this->view->taillesMonstre as $t) {
			$total = $total + $t["pourcentage_apparition"];
			if ($total >= $n) {
				$id_taille = $t["id_taille_monstre"];
				break;
			}
		}
		return $id_taille;
	}

	function referentielAction()
	{
		$modifier = false;
		$nomAction = '';
		if ($this->_request->isPost()) {
			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());

			$id_fk_type_ref_monstre = $filter->filter($this->_request->getPost('id_type'));
			$id_fk_taille_ref_monstre = $filter->filter($this->_request->getPost('id_taille'));
			$niveau_min_ref_monstre = $filter->filter($this->_request->getPost('niveau_min'));
			$niveau_max_ref_monstre = $filter->filter($this->_request->getPost('niveau_max'));
			$min_niveau_force_ref_monstre = $filter->filter($this->_request->getPost('min_niveau_force'));
			$max_niveau_force_ref_monstre = $filter->filter($this->_request->getPost('max_niveau_force'));
			$bm_force_ref_monstre = $filter->filter($this->_request->getPost('bm_force'));
			$min_niveau_sagesse_ref_monstre = $filter->filter($this->_request->getPost('min_niveau_sagesse'));
			$max_niveau_sagesse_ref_monstre = $filter->filter($this->_request->getPost('max_niveau_sagesse'));
			$bm_sagesse_ref_monstre = $filter->filter($this->_request->getPost('bm_sagesse'));
			$min_niveau_vigueur_ref_monstre = $filter->filter($this->_request->getPost('min_niveau_vigueur'));
			$max_niveau_vigueur_ref_monstre = $filter->filter($this->_request->getPost('max_niveau_vigueur'));
			$bm_vigueur_ref_monstre = $filter->filter($this->_request->getPost('bm_vigueur'));
			$min_niveau_agilite_ref_monstre = $filter->filter($this->_request->getPost('min_niveau_agilite'));
			$max_niveau_agilite_ref_monstre = $filter->filter($this->_request->getPost('max_niveau_agilite'));
			$bm_agilite_ref_monstre = $filter->filter($this->_request->getPost('bm_agilite'));
			$bm_attaque_ref_monstre = $filter->filter($this->_request->getPost('bm_attaque'));
			$bm_defense_ref_monstre = $filter->filter($this->_request->getPost('bm_defense'));
			$bm_degat_ref_monstre = $filter->filter($this->_request->getPost('bm_degat'));
			$vue_ref_monstre = $filter->filter($this->_request->getPost('vue'));

			$max_alea_pourcentage_armure_naturelle_ref_monstre = $filter->filter($this->_request->getPost('alea_max_armnat'));
			$min_alea_pourcentage_armure_naturelle_ref_monstre = $filter->filter($this->_request->getPost('alea_min_armnat'));
			$coef_pv_min_ref_monstre = $filter->filter($this->_request->getPost('coef_pv_min_ref_monstre'));
			$coef_pv_max_ref_monstre = $filter->filter($this->_request->getPost('coef_pv_max_ref_monstre'));

			if (((int)$id_fk_type_ref_monstre . "" != $id_fk_type_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. id_fk_type_ref_monstre : " . $id_fk_type_ref_monstre);
			}
			if (((int)$id_fk_taille_ref_monstre . "" != $id_fk_taille_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. id_fk_taille_ref_monstre : " . $id_fk_taille_ref_monstre);
			}
			if (((int)$niveau_min_ref_monstre . "" != $niveau_min_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. niveau_min_ref_monstre : " . $niveau_min_ref_monstre);
			}
			if (((int)$niveau_max_ref_monstre . "" != $niveau_max_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. niveau_max_ref_monstre : " . $niveau_max_ref_monstre);
			}
			if (((int)$min_niveau_force_ref_monstre . "" != $min_niveau_force_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. min_niveau_force_ref_monstre : " . $min_niveau_force_ref_monstre);
			}
			if (((int)$max_niveau_force_ref_monstre . "" != $max_niveau_force_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. max_niveau_force_ref_monstre : " . $max_niveau_force_ref_monstre);
			}
			if (((int)$bm_force_ref_monstre . "" != $bm_force_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. bm_force_ref_monstre : " . $bm_force_ref_monstre);
			}
			if (((int)$min_niveau_sagesse_ref_monstre . "" != $min_niveau_sagesse_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. min_niveau_sagesse_ref_monstre : " . $min_niveau_sagesse_ref_monstre);
			}
			if (((int)$max_niveau_sagesse_ref_monstre . "" != $max_niveau_sagesse_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. max_niveau_sagesse_ref_monstre : " . $max_niveau_sagesse_ref_monstre);
			}
			if (((int)$bm_sagesse_ref_monstre . "" != $bm_sagesse_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. bm_sagesse_ref_monstre : " . $bm_sagesse_ref_monstre);
			}
			if (((int)$min_niveau_vigueur_ref_monstre . "" != $min_niveau_vigueur_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. min_niveau_vigueur_ref_monstre : " . $min_niveau_vigueur_ref_monstre);
			}
			if (((int)$max_niveau_vigueur_ref_monstre . "" != $max_niveau_vigueur_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. max_niveau_vigueur_ref_monstre : " . $max_niveau_vigueur_ref_monstre);
			}
			if (((int)$bm_vigueur_ref_monstre . "" != $bm_vigueur_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. bm_vigueur_ref_monstre : " . $bm_vigueur_ref_monstre);
			}
			if (((int)$min_niveau_agilite_ref_monstre . "" != $min_niveau_agilite_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. min_niveau_agilite_ref_monstre : " . $min_niveau_agilite_ref_monstre);
			}
			if (((int)$max_niveau_agilite_ref_monstre . "" != $max_niveau_agilite_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. max_niveau_agilite_ref_monstre : " . $max_niveau_agilite_ref_monstre);
			}
			if (((int)$bm_agilite_ref_monstre . "" != $bm_agilite_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. bm_agilite_ref_monstre : " . $bm_agilite_ref_monstre);
			}
			if (((int)$bm_attaque_ref_monstre . "" != $bm_attaque_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. bm_attaque_ref_monstre : " . $bm_attaque_ref_monstre);
			}
			if (((int)$bm_defense_ref_monstre . "" != $bm_defense_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. bm_defense_ref_monstre : " . $bm_defense_ref_monstre);
			}
			if (((int)$bm_degat_ref_monstre . "" != $bm_degat_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. bm_degat_ref_monstre : " . $bm_degat_ref_monstre);
			}
			if (((int)$vue_ref_monstre . "" != $vue_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. vue_ref_monstre : " . $vue_ref_monstre);
			}
			if (((int)$min_alea_pourcentage_armure_naturelle_ref_monstre . "" != $min_alea_pourcentage_armure_naturelle_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. min_alea_pourcentage_armure_naturelle_ref_monstre : " . $min_alea_pourcentage_armure_naturelle_ref_monstre);
			}
			if (((int)$max_alea_pourcentage_armure_naturelle_ref_monstre . "" != $max_alea_pourcentage_armure_naturelle_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. max_alea_pourcentage_armure_naturelle_ref_monstre : " . $max_alea_pourcentage_armure_naturelle_ref_monstre);
			}
			if (((float)$coef_pv_min_ref_monstre . "" != $coef_pv_min_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. coef_pv_min_ref_monstre : " . $coef_pv_min_ref_monstre);
			}
			if (((float)$coef_pv_max_ref_monstre . "" != $coef_pv_max_ref_monstre . "")) {
				throw new Zend_Exception(get_class($this) . " Valeur invalide. coef_pv_max_ref_monstre : " . $coef_pv_max_ref_monstre);
			}

			$data = array(
				"id_fk_type_ref_monstre" => $id_fk_type_ref_monstre,
				"id_fk_taille_ref_monstre" => $id_fk_taille_ref_monstre,
				"niveau_min_ref_monstre" => $niveau_min_ref_monstre,
				"niveau_max_ref_monstre" => $niveau_max_ref_monstre,
				"min_niveau_vigueur_ref_monstre" => $min_niveau_vigueur_ref_monstre,
				"max_niveau_vigueur_ref_monstre" => $max_niveau_vigueur_ref_monstre,
				"bm_vigueur_ref_monstre" => $bm_vigueur_ref_monstre,
				"min_niveau_agilite_ref_monstre" => $min_niveau_agilite_ref_monstre,
				"max_niveau_agilite_ref_monstre" => $max_niveau_agilite_ref_monstre,
				"bm_agilite_ref_monstre" => $bm_agilite_ref_monstre,
				"min_niveau_sagesse_ref_monstre" => $min_niveau_sagesse_ref_monstre,
				"max_niveau_sagesse_ref_monstre" => $max_niveau_sagesse_ref_monstre,
				"bm_sagesse_ref_monstre" => $bm_sagesse_ref_monstre,
				"min_niveau_force_ref_monstre" => $min_niveau_force_ref_monstre,
				"max_niveau_force_ref_monstre" => $max_niveau_force_ref_monstre,
				"bm_force_ref_monstre" => $bm_force_ref_monstre,
				"bm_attaque_ref_monstre" => $bm_attaque_ref_monstre,
				"bm_defense_ref_monstre" => $bm_defense_ref_monstre,
				"bm_degat_ref_monstre" => $bm_degat_ref_monstre,
				"vue_ref_monstre" => $vue_ref_monstre,
				"max_alea_pourcentage_armure_naturelle_ref_monstre" => $max_alea_pourcentage_armure_naturelle_ref_monstre,
				"min_alea_pourcentage_armure_naturelle_ref_monstre" => $min_alea_pourcentage_armure_naturelle_ref_monstre,
				"coef_pv_min_ref_monstre" => $coef_pv_min_ref_monstre,
				"coef_pv_max_ref_monstre" => $coef_pv_max_ref_monstre,
			);

			$refTable = new ReferentielMonstre();
			if ($this->_request->getParam('update', 0) != 0) { // Mise à jour
				$modifier = true;
				$where = "id_ref_monstre=" . (int)$this->_request->getParam('update', 0);
				$refTable->update($data, $where);
			} else {
				// Insertion
				$refTable->insert($data);
			}
			$this->prepareCommun();
		} else if ($this->_request->getParam('modifier', 0) != 0) {
			$modifier = true;
			$nomAction = 'update/' . $this->_request->getParam('modifier');
		}
		$this->referentielPrepare();
		$this->view->modifier = $modifier;
		$this->view->nomAction = $nomAction;
		$this->render();
	}

	private function referentielPrepare()
	{
		$ref = null;
		$tailles = null;
		$types = null;
		$referenceCourante = array(
			"id_ref_monstre" => '',
			"id_type_monstre" => '',
			"id_taille_monstre" => '',
			"niveau_min" => '',
			"niveau_max" => '',
			"min_niveau_force" => '',
			"max_niveau_force" => '',
			"bm_force" => '',
			"min_niveau_sagesse" => '',
			"max_niveau_sagesse" => '',
			"bm_sagesse" => '',
			"min_niveau_vigueur" => '',
			"max_niveau_vigueur" => '',
			"bm_vigueur" => '',
			"min_niveau_agilite" => '',
			"max_niveau_agilite" => '',
			"bm_agilite" => '',
			"bm_attaque" => '',
			"bm_defense" => '',
			"bm_degat" => '',
			"vue" => '',
			"coef_pv_min_ref_monstre" => '',
			"coef_pv_max_ref_monstre" => '',
			"alea_min_armnat" => '',
			"alea_max_armnat" => '',
		);

		$idModifier = $this->_request->getParam('modifier', 0);
		if ($idModifier > 0) {
			$id = $idModifier;
		} else {
			$id = $this->_request->getParam('update', 0);
		}

		if ($id > 0) { // si l'on veut modifier une reference, on prepare l'objet
			$referenceCourante = $this->view->refMonstre[$id];
		}
		$this->view->referenceCourante = $referenceCourante;
	}

	private function prepareCommun()
	{
		$ref = null;
		$tailles = null;
		$types = null;
		$stats = null;

		$refTable = new ReferentielMonstre();
		$taillesTable = new TailleMonstre();
		$typesTable = new TypeMonstre();
		$monstresTable = new Monstre();
		$zoneTable = new Zone();
		$groupeMonstreTable = new GroupeMonstre();
		$villeTable = new Ville();

		$refRowset = $refTable->findAll();
		$taillesRowset = $taillesTable->fetchall();
		$typesRowset = $typesTable->fetchAllAvecTypeGroupe();
		$zonesRowset = $zoneTable->fetchAllAvecEnvironnement();
		$villesRowset = $villeTable->fetchAll();

		$action = $this->_request->getParam('action');

		foreach ($refRowset as $r) {
			if ($r["genre_type_monstre"] == 'feminin') {
				$m_taille = $r["nom_taille_f_monstre"];
			} else {
				$m_taille = $r["nom_taille_m_monstre"];
			}

			$estimationPvMin = floor(((20 + $r["min_niveau_vigueur_ref_monstre"] * 4) * 2) * $r["coef_pv_min_ref_monstre"]);
			$estimationPvMax = floor(((20 + $r["max_niveau_vigueur_ref_monstre"] * 4) * 2) * $r["coef_pv_min_ref_monstre"]);

			$ref[$r["id_ref_monstre"]] = array(
				"id_ref_monstre" => $r["id_ref_monstre"],
				"nom_type" => $r["nom_type_monstre"],
				"id_type_monstre" => $r["id_fk_type_ref_monstre"],
				"id_type_groupe_monstre" => $r["id_fk_type_groupe_monstre"],
				"id_taille_monstre" => $r["id_fk_taille_ref_monstre"],
				"taille" => $m_taille,
				"niveau_min" => $r["niveau_min_ref_monstre"],
				"niveau_max" => $r["niveau_max_ref_monstre"],
				"min_niveau_force" => $r["min_niveau_force_ref_monstre"],
				"max_niveau_force" => $r["max_niveau_force_ref_monstre"],
				"bm_force" => $r["bm_force_ref_monstre"],
				"min_niveau_sagesse" => $r["min_niveau_sagesse_ref_monstre"],
				"max_niveau_sagesse" => $r["max_niveau_sagesse_ref_monstre"],
				"bm_sagesse" => $r["bm_sagesse_ref_monstre"],
				"min_niveau_vigueur" => $r["min_niveau_vigueur_ref_monstre"],
				"max_niveau_vigueur" => $r["max_niveau_vigueur_ref_monstre"],
				"bm_vigueur" => $r["bm_vigueur_ref_monstre"],
				"min_niveau_agilite" => $r["min_niveau_agilite_ref_monstre"],
				"max_niveau_agilite" => $r["max_niveau_agilite_ref_monstre"],
				"bm_agilite" => $r["bm_agilite_ref_monstre"],
				"bm_attaque" => $r["bm_attaque_ref_monstre"],
				"bm_defense" => $r["bm_defense_ref_monstre"],
				"bm_degat" => $r["bm_degat_ref_monstre"],
				"vue" => $r["vue_ref_monstre"],
				"nb_membres_min" => $r["nb_membres_min_type_groupe_monstre"],
				"nb_membres_max" => $r["nb_membres_max_type_groupe_monstre"],
				"nom_groupe_monstre" => $r["nom_groupe_monstre"],
				"coef_pv_min_ref_monstre" => $r["coef_pv_min_ref_monstre"],
				"coef_pv_max_ref_monstre" => $r["coef_pv_max_ref_monstre"],
				"alea_min_armnat" => $r["min_alea_pourcentage_armure_naturelle_ref_monstre"],
				"alea_max_armnat" => $r["max_alea_pourcentage_armure_naturelle_ref_monstre"],
				"estimation_pv_min" => $estimationPvMin,
				"estimation_pv_max" => $estimationPvMax,
			);
		}

		foreach ($taillesRowset as $t) {
			$tailles[$t->id_taille_monstre] = array(
				"id_taille_monstre" => $t->id_taille_monstre,
				"nom_feminin" => $t->nom_taille_f_monstre,
				"nom_masculin" => $t->nom_taille_m_monstre,
				"pourcentage_apparition" => $t->pourcentage_taille_monstre
			);

			$stats["nb_monstre_par_taille"][] = array(
				"nom_feminin" => $t->nom_taille_f_monstre,
				"nom_masculin" => $t->nom_taille_m_monstre,
				"nombre" => $monstresTable->countAllByTaille($t->id_taille_monstre)
			);
		}

		foreach ($typesRowset as $t) {
			$types[$t["id_type_monstre"]] = array(
				"id_type_monstre" => $t["id_type_monstre"],
				"nom_type" => $t["nom_type_monstre"],
				"nom_groupe_monstre" => $t["nom_groupe_monstre"],
				"nb_membres_min" => $t["nb_membres_min_type_groupe_monstre"],
				"nb_membres_max" => $t["nb_membres_max_type_groupe_monstre"],
			);

			$stats["nb_monstre_par_type"][] = array(
				"nom_type" => $t["nom_type_monstre"],
				"nombre" => $monstresTable->countAllByType($t["id_type_monstre"])
			);
		}

		if ($action != "referentiel") {
			foreach ($zonesRowset as $z) {
				$nombreMonstres = $monstresTable->countVue($z["x_min_zone"], $z["y_min_zone"], $z["x_max_zone"], $z["y_max_zone"], 0);
				$nombreCases = ($z["x_max_zone"] - $z["x_min_zone"]) * ($z["y_max_zone"] - $z["y_min_zone"]);
				if ($nombreMonstres > 0 && $nombreCases > 0) {
					$couverture = ($nombreMonstres * 100) / $nombreCases;
				} else {
					$couverture = 0;
				}

				$villes = "";
				foreach ($villesRowset as $v) {
					if ($z["x_min_zone"] <= $v->x_max_ville && $z["x_max_zone"] >= $v->x_min_ville &&
						$z["y_min_zone"] <= $v->y_max_ville && $z["y_max_zone"] >= $v->y_min_ville
					) {
						$villes .= $v->nom_ville . ", ";
					}
				}

				$zones[] = array("id_zone" => $z["id_zone"],
					"x_min" => $z["x_min_zone"],
					"x_max" => $z["x_max_zone"],
					"y_min" => $z["y_min_zone"],
					"y_max" => $z["y_max_zone"],
					"environnement" => $z["nom_environnement"],
					"nombre_monstres" => $nombreMonstres,
					"nombre_cases" => $nombreCases,
					"couverture" => round($couverture, 5),
					"villes" => $villes
				);
			}
		}

		$stats["nb_monstres"] = $monstresTable->countAll();
		$stats["nb_groupes"] = $groupeMonstreTable->countAll();
		$stats["couverture_globale"] = round(($stats["nb_monstres"] * 100) / ((abs($this->view->config->game->x_min) + $this->view->config->game->x_max) * (abs($this->view->config->game->y_min) + $this->view->config->game->y_max)), 5);

		if ($action != "referentiel") {
			$this->view->stats = $stats;
			$this->view->zones = $zones;
		}
		$this->view->taillesMonstre = $tailles;
		$this->view->typesMonstre = $types;
		$this->view->refMonstre = $ref;
	}

	function monstreAction()
	{
		Zend_Loader::loadClass('Monstre');

		$this->modificationMonstre = false;

		if ($this->_request->isPost() && $this->_request->get('idmonstre') == $this->_request->getPost("id_monstre")) {
			$modification = "";

			$monstreTable = new Monstre();

			$where = $monstreTable->getAdapter()->quoteInto('id_monstre = ?', (int)$this->_request->getPost('id_monstre'));
			$monstreRowset = $monstreTable->fetchRow($where);
			$monstre = $monstreRowset->toArray();

			$tabPost = $this->_request->getPost();
			foreach ($tabPost as $key => $value) {
				if ($key != 'id_monstre' && mb_substr($key, -8) == "_monstre") {

					if ($monstre[$key] != $value) {
						$modification .= " ==> Valeur modifiée : ";
					}
					$modification .= "$key avant: " . $monstre[$key] . " apres:" . $value;
					$modification .= PHP_EOL;

					if ($value == '') {
						$value = null;
						$data [$key] = $value;
					} else {
						$data [$key] = stripslashes($value);
					}
				}
			}

			$where = "id_monstre=" . $this->_request->getPost("id_monstre");
			$monstreTable->update($data, $where);
			$this->view->modificationMonstre = true;

			$config = Zend_Registry::get('config');
			if ($config->general->mail->exception->use == '1') {
				Zend_Loader::loadClass("Bral_Util_Mail");
				$mail = Bral_Util_Mail::getNewZendMail();

				$mail->setFrom($config->general->mail->administration->from, $config->general->mail->administration->nom);
				$mail->addTo($config->general->mail->administration->from, $config->general->mail->administration->nom);
				$mail->setSubject("[Braldahim-Admin Jeu] Administration Monstre " . $this->_request->getPost("id_monstre"));
				$texte = "--------> Utilisateur " . $this->view->user->prenom_braldun . " " . $this->view->user->nom_braldun . " (" . $this->view->user->id_braldun . ")" . PHP_EOL;
				$texte .= PHP_EOL . $modification;

				$mail->setBodyText($texte);
				$mail->send();
			}

		}

		$this->monstrePrepare();
		$this->render();
	}

	private function monstrePrepare()
	{
		$monstreTable = new Monstre();

		$where = $monstreTable->getAdapter()->quoteInto('id_monstre = ?', (int)$this->_request->get('idmonstre'));
		$monstreRowset = $monstreTable->fetchRow($where);

		if (count($monstreRowset) == 1) {
			$this->view->monstre = $monstreRowset->toArray();
		} else {
			$this->view->monstre = $monstreRowset;
		}
		$this->view->id_monstre = $this->_request->get('idmonstre');

		if ($this->_request->get('mode') == "" || $this->_request->get('mode') == "simple") {
			$this->view->mode = "simple";
			$keySimple [] = "id_monstre";
			$keySimple [] = "x_monstre";
			$keySimple [] = "y_monstre";
			$keySimple [] = "pa_monstre";
			$keySimple [] = "date_fin_tour_monstre";
			$this->view->keySimple = $keySimple;
		} else {
			$this->view->mode = "complexe";
		}
	}

	public function repartitionAction()
	{
		Zend_Loader::loadClass("CreationNids");
		Zend_Loader::loadClass("Nid");
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("ZoneNid");

		$nidTable = new Nid();
		$monstreTable = new Monstre();

		$monstres = $monstreTable->countAllByTypeAndIdZoneNid();
		$nids = $nidTable->countMonstresACreerByTypeMonstreAndIdZone();

		$creationNidsTable = new CreationNids();

		$zoneNidTable = new ZoneNid();
		$zones = $zoneNidTable->fetchAll();

		$tabZones = null;

		$typeMonstreTable = new TypeMonstre();
		$tousTypesMontres = $typeMonstreTable->fetchAllSansGibier();

		Zend_Loader::loadClass("TypeGroupeMonstre");
		$tabTypeGroupe = TypeGroupeMonstre::getStaticTypes();
		foreach ($tousTypesMontres as $t) {
			$tabTypesMonstres[$t["id_type_monstre"]]["nom"] = $t["nom_type_monstre"];
			$tabTypesMonstres[$t["id_type_monstre"]]["type"] = $tabTypeGroupe[$t["id_fk_type_groupe_monstre"]];
		}

		$this->view->typesMonstres = $tabTypesMonstres;
		Zend_Loader::loadClass("TypeGroupeMonstre");

		foreach ($zones as $z) {
			$tab = array("id_zone_nid" => $z["id_zone_nid"]);
			$tab["nbVivants"] = 0;
			$tab["nbDansNids"] = 0;
			$tab["vivants"] = null;
			$tab["niveauMoyen"] = null;
			$tab["dansNids"] = null;
			$tab["details"] = array();
			foreach ($monstres as $m) {
				$tab["details"][$m["id_fk_type_monstre"]]["dansNids"] = 0;
				if ($z["id_zone_nid"] == $m["id_fk_zone_nid_monstre"]) {
					$tab["nbVivants"] = $tab["nbVivants"] + $m["nombre"];
					$tab["details"][$m["id_fk_type_monstre"]]["vivants"] = $m["nombre"];
					$tab["details"][$m["id_fk_type_monstre"]]["niveauMoyen"] = floor($m["totalNiveau"] / $m["nombre"]);
				}
			}

			foreach ($nids as $n) {
				if ($z["id_zone_nid"] == $n["id_fk_zone_nid"]) {
					$tab["nbDansNids"] = $tab["nbDansNids"] + $n["nombre"];
					$tab["details"][$n["id_fk_type_monstre_nid"]]["dansNids"] = $n["nombre"];
				}
				if (!array_key_exists($n["id_fk_type_monstre_nid"], $tab["details"]) || !array_key_exists("dansNids", $tab["details"][$n["id_fk_type_monstre_nid"]])) {
					$tab["details"][$n["id_fk_type_monstre_nid"]]["dansNids"] = 0;
				}
			}

			$creationNidsRowset = $creationNidsTable->findByIdZoneNid($z["id_zone_nid"]);

			$nbCasesDansZone = ($z["x_max_zone_nid"] - $z["x_min_zone_nid"]) * ($z["y_max_zone_nid"] - $z["y_min_zone_nid"]);

			$tab["estVille"] = $z["est_ville_zone_nid"];

			if ($tab["estVille"] == "oui") {
				$tab["couvertureDemandee"] = "non applicable";
			} else {
				$tab["couvertureDemandee"] = $z["couverture_zone_nid"] . "%";
			}
			$nbTypesTotalDansZone = count($creationNidsRowset);

			foreach ($creationNidsRowset as $c) {
				if (!array_key_exists($c["id_fk_type_monstre_creation_nid"], $tab["details"]) || !array_key_exists("vivants", $tab["details"][$c["id_fk_type_monstre_creation_nid"]])
					|| !array_key_exists("niveauMoyen", $tab["details"][$c["id_fk_type_monstre_creation_nid"]])
				) {
					$tab["details"][$c["id_fk_type_monstre_creation_nid"]]["vivants"] = 0;
					$tab["details"][$c["id_fk_type_monstre_creation_nid"]]["niveauMoyen"] = 0;
				}

				if (!array_key_exists($c["id_fk_type_monstre_creation_nid"], $tab["details"]) || !array_key_exists("dansNids", $tab["details"][$c["id_fk_type_monstre_creation_nid"]])) {
					$tab["details"][$c["id_fk_type_monstre_creation_nid"]]["dansNids"] = 0;
				}

				$tab["details"][$c["id_fk_type_monstre_creation_nid"]]["totalReel"] = $tab["details"][$c["id_fk_type_monstre_creation_nid"]]["vivants"] + $tab["details"][$c["id_fk_type_monstre_creation_nid"]]["dansNids"];

				if ($c["nb_monstres_ville_creation_nid"] != null) { // ville
					$tab["details"][$c["id_fk_type_monstre_creation_nid"]]["totalDemande"] = $c["nb_monstres_ville_creation_nid"];
				} else {
					$nbPourcentMonstresParTypeAAvoir = $z["couverture_zone_nid"] / $nbTypesTotalDansZone;
					$nbMonstresParTypeAAvoir = $nbPourcentMonstresParTypeAAvoir * $nbCasesDansZone / 100;

					$tab["details"][$c["id_fk_type_monstre_creation_nid"]]["totalDemande"] = number_format($nbMonstresParTypeAAvoir, 2);
				}

				$tab["details"][$c["id_fk_type_monstre_creation_nid"]]["manque"] = number_format($tab["details"][$c["id_fk_type_monstre_creation_nid"]]["totalDemande"] - $tab["details"][$c["id_fk_type_monstre_creation_nid"]]["totalReel"], 2);
			}

			$tab["nbTotal"] = $tab["nbDansNids"] + $tab["nbVivants"];
			$tab["couvertureReelle"] = number_format($tab["nbTotal"] * 100 / $nbCasesDansZone, 2);
			$tabZones[] = $tab;
		}

		$this->view->zonesNids = $tabZones;
		$this->render();
	}

}
