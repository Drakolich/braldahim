<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class InterfaceController extends Zend_Controller_Action {
	private $xml_response = null;

	function init() {
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
		$this->view->estMobile = Zend_Registry::get("estMobile");
		$this->view->estIphone = Zend_Registry::get("estIphone");
	}

	function preDispatch() {
		$hasIdentity = Zend_Auth::getInstance()->hasIdentity();
		$controleOk = false;

		if ($this->view->config->general->actif != 1) {
			$this->_forward('logoutajax', 'auth');
		} else if ($this->_request->action == 'index' && (!$hasIdentity || !isset($this->view->user) || !isset($this->view->user->email_braldun))) {
			$this->_forward('logout', 'auth');
		} else if (!$hasIdentity || !isset($this->view->user) || !isset($this->view->user->email_braldun)
				   || ($this->_request->action != 'index' && $this->view->user->initialCall == false && $this->_request->get("dateAuth") != $this->view->user->dateAuth)
		) {
			if (!$hasIdentity) {
				Bral_Util_Log::tech()->warn("InterfaceController - logoutajax 1A - Session perdue: dateAuth" . $this->_request->get("dateAuth"));
			} else {
				$texte = "braldun:inconnu";
				if ($this->view != null && $this->view->user != null) {
					$texte = $this->view->user->prenom_braldun . " " . $this->view->user->nom_braldun . " (" . $this->view->user->id_braldun . ")";
				}
				Bral_Util_Log::tech()->warn("InterfaceController - logoutajax 1B " . $texte . " action=" . $this->_request->action . " uri=" . $this->_request->getRequestUri() . " initialCall=" . $this->view->user->initialCall . " dateAuth=" . $this->_request->get("dateAuth") . " dateAuth2=" . $this->view->user->dateAuth);
			}

			$this->_forward('logoutajax', 'auth');
		} else {
			Zend_Loader::loadClass('Bral_Util_BralSession');
			if (Bral_Util_BralSession::refreshSession() == false) {
				$texte = "braldun:inconnu";
				if ($this->view != null && $this->view->user != null) {
					$texte = $this->view->user->prenom_braldun . " " . $this->view->user->nom_braldun . " (" . $this->view->user->id_braldun . ")";
				}
				$texte .= " action=" . $this->_request->action . " uri=" . $this->_request->getRequestUri();
				Bral_Util_Log::tech()->warn("InterfaceController - logoutajax 2 " . $texte);
				if ($this->_request->action == 'index') {
					$this->_forward('logout', 'auth');
				} else {
					$this->_forward('logoutajax', 'auth');
				}
			} else {
				$controleOk = true;
			}
		}

		if ($controleOk === true) {

			$this->view->user = Zend_Auth::getInstance()->getIdentity(); // pour rafraichissement session
			if ($this->view->user == null) {
				// dernier contrôle
				$texte = " action=" . $this->_request->action . " uri=" . $this->_request->getRequestUri();
				Bral_Util_Log::tech()->warn("Bral_Controller_Action - logoutajax " . $texte);
				$this->_forward('logoutajax', 'auth');
				return;
			}

			if ($this->view->user->est_charte_validee_braldun == "non") {
				$this->_redirect('/charte');
			}

			$this->view->controleur = $this->_request->controller;

			$this->infoTour = false;

			if ($this->_request->action != 'index') {
				$this->xml_response = new Bral_Xml_Response();
				$t = Bral_Box_Factory::getTour($this->_request, $this->view, false);
				$warning = $t->getWarningFinTour();
				if ($t->activer()) {
					$xml_entry = new Bral_Xml_Entry();
					$xml_entry->set_type("display");
					$xml_entry->set_valeur("box_informations");
					$xml_entry->set_data($t->render());
					$this->xml_response->add_entry($xml_entry);
					unset($xml_entry);
					$this->infoTour = true;

					if ($this->_request->action != 'boxes') {
						$this->refreshAll();
					}
				} elseif ($warning != null) {
					$xml_entry = new Bral_Xml_Entry();
					$xml_entry->set_type("action");
					$xml_entry->set_valeur("warning");
					$xml_entry->set_data($warning);
					$this->xml_response->add_entry($xml_entry);
					unset($xml_entry);
				}
				unset($t);
			}
		}
	}

	function clearAction() {
		if ($this->infoTour == false) {
			$this->render();
		} else {
			$this->xml_response->render();
		}
	}

	function indexAction() {
		$this->render();
	}

	function loadAction() {
		$this->view->affichageInterne = true;
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$nomBox = $this->_request->get("box");
		$box = Bral_Box_Factory::getBox($nomBox, $this->_request, $this->view, true);

		if ($nomBox == "box_echoppes" || $nomBox == "box_echoppe") {
			// force render pour avoir htmlTri
			$xml_entry->set_data($box->render());
		} else {
			$xml_entry->set_box($box);
		}
		$xml_entry->set_valeur($box->getNomInterne());
		$this->xml_response->add_entry($xml_entry);

		$tabTables = $box->getTablesHtmlTri();
		if ($tabTables != false) {
			Bral_Controller_Action::addXmlEntryTableHtmlTri($this->xml_response, $tabTables);
		}

		Bral_Util_Messagerie::setXmlResponseMessagerie($this->xml_response, $this->view->user->id_braldun);
		unset($xml_entry);

		$this->xml_response->render();
	}

	function boxesAction() {
		// Si nouveau tour ou nouvelle phase, on ne charge pas toutes les boites.
		// elles seront chargées au clic sur Fermer sur la fenêtre d'information
		if ($this->infoTour) {
			$this->xml_response->render();
			return;
		}

		Zend_Loader::loadClass('BraldunsMetiers');
		Zend_Loader::loadClass('Bral_Util_Communaute');

		$tabTables = false;

		if ($this->view->estMobile) {
			$boite_a = "boite_m";
			$boite_b = "boite_m";
			$boite_c = "boite_m";
		} else {
			$boite_a = "boite_a";
			$boite_b = "boite_b";
			$boite_c = "boite_c";
		}

		try {

			if ($this->view->estMobile) {
				$this->addBox(Bral_Box_Factory::getPersonnage($this->_request, $this->view, false), $boite_a);
				$this->addBox(Bral_Box_Factory::getEffets($this->_request, $this->view, false), $boite_a);
				$this->addBox(Bral_Box_Factory::getCompetences($this->_request, $this->view, false), $boite_b);
			}

			$this->addBox(Bral_Box_Factory::getInterface($this->_request, $this->view, true), $boite_c);

			if ($this->view->user->est_testeur_vue_braldun == 'oui' && !$this->view->estMobile) {
				$this->addBox(Bral_Box_Factory::getInterfacetest($this->_request, $this->view, true), $boite_c);
			}

			if ($this->view->estMobile) {
				$this->addBox(Bral_Box_Factory::getBlabla($this->_request, $this->view, false), $boite_c);
				$this->addBox(Bral_Box_Factory::getLieu($this->_request, $this->view, false), $boite_c);
			}

			// uniquement s'il possède un metier dans les metiers possedant des echoppes
			$braldunsMetiers = new BraldunsMetiers();
			$possibleEchoppe = $braldunsMetiers->peutPossederEchoppeIdBraldun($this->view->user->id_braldun);
			if ($possibleEchoppe === true) {
				$this->addBox(Bral_Box_Factory::getEchoppes($this->_request, $this->view, false), $boite_c);
			}
			unset($braldunsMetiers);

			$this->addBox(Bral_Box_Factory::getChamps($this->_request, $this->view, false), $boite_c);

			if ($this->view->estMobile) {
				$this->addBox(Bral_Box_Factory::getLaban($this->_request, $this->view, false), $boite_c);
				$this->addBox(Bral_Box_Factory::getCharrette($this->_request, $this->view, false), $boite_c);
				$this->addBox(Bral_Box_Factory::getCoffre($this->_request, $this->view, false), $boite_c);
			}
			$this->addBox(Bral_Box_Factory::getEvenements($this->_request, $this->view, false), $boite_c);
			$this->addBox(Bral_Box_Factory::getCommunaute($this->_request, $this->view, false), $boite_c);
			$this->addBox(Bral_Box_Factory::getSoule($this->_request, $this->view, false), $boite_c);

			$this->addBox(Bral_Box_Factory::getQuetes($this->_request, $this->view, false), $boite_c);

			if ($this->view->estMobile) {
				$this->addBox(Bral_Box_Factory::getMessagerie($this->_request, $this->view, false), $boite_c);
				$this->addBox(Bral_Box_Factory::getCarnet($this->_request, $this->view, false), $boite_c);
			}

			$xml_entry = new Bral_Xml_Entry();
			$xml_entry->set_type("display");
			if ($this->view->estMobile) {
				$xml_entry->set_valeur("racinemobile");
			} else {
				$xml_entry->set_valeur("racine");
			}
			$xml_entry->set_data($this->getBoxesData());

		} catch (Zend_Exception $e) {
			$b = Bral_Box_Factory::getErreur($this->_request, $this->view, false, $e->getMessage());
			$xml_entry = new Bral_Xml_Entry();
			$xml_entry->set_type("display");
			$xml_entry->set_valeur($b->getNomInterne());
			$xml_entry->set_data($b->render());
		}

		$this->xml_response->add_entry($xml_entry);
		unset($xml_entry);
		$this->xml_response->render();
	}

	private function addBox($p, $position) {
		$this->m_list[$position][] = $p;
	}

	private function getBoxesData() {
		if ($this->view->estMobile) {
			$r = $this->getDataList("boite_m");
		} else {
			$r = '';
			/*$r = "<table width='99%'><tr valign='top'><td width='375px'>";*/
			//$r .= $this->getDataList("boite_a");
			//$r .= $this->getDataList("boite_b");
			/*$r .= "</td><td width='auto'>";*/
			$r .= $this->getDataList("boite_c");
			//			$r .= $this->getDataList("boite_d");
			/*$r .= "</td></tr></table>";*/
		}
		return $r;
	}

	private function getDataList($nom) {
		$l = $this->m_list[$nom];
		$liste = "";
		$data = "";
		$onglets = null;

		if ($nom != "aucune") {
			for ($i = 0; $i < count($l); $i++) {
				if ($i == 0 && !$this->view->estMobile) {
					$css = "actif";
				} else {
					$css = "inactif";
				}
				$tab = array("titre" => $l[$i]->getTitreOnglet(), "nom" => $l[$i]->getNomInterne(), "css" => $css, "chargementInBoxes" => $l[$i]->getChargementInBoxes());
				$onglets[] = $tab;
				$liste .= $l[$i]->getNomInterne();
				if ($i < count($l) - 1) {
					$liste .= ",";
				}
			}

			for ($i = 0; $i < count($l); $i++) {
				if ($i == 0 && !$this->view->estMobile) {
					if ($l[$i]->getNomInterne() == "box_interface") {
						$display = "block";
					} else {
						$display = "none";
					}
				} else {
					$display = "none";
				}

				$l[$i]->setDisplay($display);
				$data .= $l[$i]->render();
			}

			$this->view->onglets = $onglets;
			$this->view->liste = $liste;
			$this->view->data = $data;
			$this->view->conteneur = $nom;
			unset($onglets);
			unset($liste);
			unset($data);
			unset($nom);
			if ($this->view->estMobile) {
				return $this->view->render("interface/box_onglets_mobile.phtml");
			} else {
				return $this->view->render("interface/box_onglets.phtml");
			}
		}
	}

	private function refreshAll() {
		$boxToRefresh = array("box_profil", "box_metier", "box_titres", "box_equipement", "box_vue", "box_lieu", "box_competences", "box_laban", "box_coffre", "box_charrette", "box_soule", "box_quete", "box_messagerie");
		for ($i = 0; $i < count($boxToRefresh); $i++) {
			$xml_entry = new Bral_Xml_Entry();

			if ($boxToRefresh[$i] == "box_vue" || $boxToRefresh[$i] == "box_laban" || $boxToRefresh[$i] == "box_coffre" || $boxToRefresh[$i] == "box_charrette") {
				$xml_entry->set_type("load_box");
				$c = Bral_Box_Factory::getBox($boxToRefresh[$i], $this->_request, $this->view, false);
				$xml_entry->set_data(null);
			} else {
				$xml_entry->set_type("display");
				$c = Bral_Box_Factory::getBox($boxToRefresh[$i], $this->_request, $this->view, true);
				$xml_entry->set_data($c->render());
			}
			$xml_entry->set_valeur($c->getNomInterne());
			$this->xml_response->add_entry($xml_entry);
			unset($xml_entry);
			unset($c);
			unset($boxToRefresh[$i]);
		}
	}

	public function reloadAction() {
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("action");
		$xml_entry->set_valeur("goto");
		$xml_entry->set_data("/interface/");
		$this->xml_response->add_entry($xml_entry);
		unset($xml_entry);
		$this->xml_response->render();
	}
}
