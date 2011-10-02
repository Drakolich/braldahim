<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Construiredependance extends Bral_Communaute_Communaute
{

    function getTitreOnglet()
    {
    }

    function setDisplay($display)
    {
    }

    function getNomInterne()
    {
        return "box_action";
    }

    function getTitre()
    {
        return "Construire une dépendance";
    }

    function prepareCommun()
    {

        Zend_Loader::loadClass("Bral_Util_Communaute");
        if (!Bral_Util_Communaute::possedeUnHall($this->view->user->id_fk_communaute_braldun)) {
            throw new Zend_Exception("Bral_Communaute_Construirebatiment :: Hall invalide idC:" . $this->view->user->id_fk_communaute_braldun);
        }

        $this->view->nomLieu = null;

        if ($this->view->user->rangCommunaute > Bral_Util_Communaute::ID_RANG_TENANCIER) {
            throw new Zend_Exception(get_class($this) . " Vous n'êtes pas tenancier de la communauté " . $this->view->user->rangCommunaute);
        }

        Zend_Loader::loadClass("Communaute");
        $communauteTable = new Communaute();
        $communaute = $communauteTable->findById($this->view->user->id_fk_communaute_braldun);
        if (count($communaute) != 1) {
            throw new Zend_Exception("Bral_Communaute_Initialiserbatiment :: Erreur Technique :" . count($communaute));
        } else {
            $communaute = $communaute[0];
        }

        Zend_Loader::loadClass('Lieu');
        Zend_Loader::loadClass('Palissade');

        $this->distance = 3;
        $this->view->x_min = $communaute["x_communaute"] - $this->distance;
        $this->view->x_max = $communaute["x_communaute"] + $this->distance;
        $this->view->y_min = $communaute["y_communaute"] - $this->distance;
        $this->view->y_max = $communaute["y_communaute"] + $this->distance;
        $this->view->nb_cases = 3;

        $lieuxTable = new Lieu();
        $lieux = $lieuxTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $communaute["z_communaute"]);
        $palissadeTable = new Palissade();
        $palissades = $palissadeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $communaute["z_communaute"]);

        $defautChecked = false;

        for ($j = $this->distance; $j >= -$this->distance; $j--) {
            $change_level = true;
            for ($i = -$this->distance; $i <= $this->distance; $i++) {
                $x = $communaute["x_communaute"] + $i;
                $y = $communaute["y_communaute"] + $j;
                $z = $communaute["z_communaute"];

                $display = $x;
                $display .= " ; ";
                $display .= $y;

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

                foreach ($lieux as $l) {
                    if ($x == $l["x_lieu"] && $y == $l["y_lieu"] && $z == $l["z_lieu"]) {
                        $valid = false;
                        break;
                    }
                }

                foreach ($palissades as $p) {
                    if ($x == $p["x_palissade"] && $y == $p["y_palissade"] && $z == $p["z_palissade"]) {
                        $valid = false;
                        break;
                    }
                }

                if ($valid === true && $defautChecked == false) {
                    $default = "checked";
                    $defautChecked = true;
                    $this->view->monterPalissadeOk = true;
                } else {
                    $default = "";
                }

                $tab[] = array("x_offset" => $i,
                    "y_offset" => $j,
                    "default" => $default,
                    "display" => $display,
                    "change_level" => $change_level, // nouvelle ligne dans le tableau
                    "valid" => $valid
                );

                $tabValidation[$i][$j] = $valid;

                if ($change_level) {
                    $change_level = false;
                }
            }
        }

        // on selectionne tous les lieux de la communaute

        Zend_Loader::loadClass('TypeLieu');
        $typeLieuTable = new TypeLieu();
        $typesLieux = $typeLieuTable->findByTypeDependance();

        $tabTypesLieux = null;
        foreach ($typesLieux as $t) {
            $display = null;
            foreach ($lieux as $l) { // si dans les lieux, il y a déjà un lieu de même type
                if ($t["id_type_lieu"] == $l["id_fk_type_lieu"] && $l["id_fk_communaute_lieu"] == $this->view->user->id_fk_communaute_braldun) {
                    // on ne pourra pas construire un bâtiment du même type une seconde fois
                    $display = false;
                } else if ($t["id_fk_type_lieu_type_dependance"] == $l["id_fk_type_lieu"] && $l["niveau_lieu"] >= $t["niveau_type_dependance"] && $l["niveau_lieu"] == $l["niveau_prochain_lieu"]) {
                    if ($display !== false) {
                        $display = true;
                    }
                }
            }
            if ($display) {
                $tabTypesLieux[$t["id_type_lieu"]]["type"] = $t;
                $tabTypesLieux[$t["id_type_lieu"]]["selected"] = "";
            }
        }

        $this->view->tableau = $tab;
        $this->tableauValidation = $tabValidation;
        $this->view->typeLieux = $tabTypesLieux;

        $this->view->nb_pa = 1;
        $this->communaute = $communaute;
    }

    function prepareFormulaire()
    {
    }

    function prepareResultat()
    {
        if ($this->view->assezDePa == false) {
            return;
        }

        if (((int)$this->_request->get("valeur_1") . "" != $this->_request->get("valeur_1") . "")) {
            throw new Zend_Exception(get_class($this) . " Type invalide : " . $this->_request->get("valeur_1"));
        } else {
            $idTypeLieu = (int)$this->_request->get("valeur_1");
        }

        if (!array_key_exists($idTypeLieu, $this->view->typeLieux)) {
            throw new Zend_Exception(get_class($this) . " Type invalide 2 : " . $idTypeLieu);
        }

        $x_y = $this->_request->get("valeur_2");
        list ($offset_x, $offset_y) = preg_split("/h/", $x_y);

        if ($offset_x < -$this->view->nb_cases || $offset_x > $this->view->nb_cases) {
            throw new Zend_Exception(get_class($this) . " Position X impossible : " . $offset_x);
        }

        if ($offset_y < -$this->view->nb_cases || $offset_y > $this->view->nb_cases) {
            throw new Zend_Exception(get_class($this) . " Position Y impossible : " . $offset_y);
        }

        if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
            throw new Zend_Exception(get_class($this) . " Position XY impossible : " . $offset_x . $offset_y);
        }

        $x = $this->communaute["x_communaute"] + $offset_x;
        $y = $this->communaute["y_communaute"] + $offset_y;

        $this->initialiser($idTypeLieu, $x, $y);
        $this->majBraldun();
    }

    private function initialiser($idTypeLieu, $x, $y)
    {

        Zend_Loader::loadClass('Bral_Util_Communaute');
        Zend_Loader::loadClass('Communaute');

        $communauteTable = new Communaute();
        $communauteRowset = $communauteTable->findById($this->view->user->id_fk_communaute_braldun);
        if (count($communauteRowset) == 1) {
            $communaute = $communauteRowset[0];
        }

        $lieuTable = new Lieu();

        $nomLieu = $this->view->typeLieux[$idTypeLieu]["type"]["nom_type_lieu"] . " de la communauté " . $communaute["nom_communaute"];

        $data = array(
            'nom_lieu' => $nomLieu,
            'description_lieu' => "",
            'x_lieu' => $x,
            'y_lieu' => $y,
            'z_lieu' => 0,
            'etat_lieu' => 100,
            'id_fk_type_lieu' => $idTypeLieu,
            'id_fk_communaute_lieu' => $this->view->user->id_fk_communaute_braldun,
            'date_creation_lieu' => date("Y-m-d H:i:s"),
        );

        $lieuTable->insert($data);

        $this->view->nomLieu = $nomLieu;

        Zend_Loader::loadClass("TypeEvenementCommunaute");
        Zend_Loader::loadClass("Bral_Util_EvenementCommunaute");

        $details = $nomLieu;
        $detailsBot = "La dépendance -" . $nomLieu . "- a été construite. " . PHP_EOL;
        $detailsBot = "Attention, la perte d'un niveau d'un Bâtiment dans un domaine peut faire perdre une dépendance. " . PHP_EOL;
        $detailsBot = "Consultez l'arbre des Communautés pour plus d'informations. " . PHP_EOL;

        $detailsBot .= PHP_EOL . PHP_EOL . "Action réalisée par [b" . $this->view->user->id_braldun . "]";
        Bral_Util_EvenementCommunaute::ajoutEvenements($this->view->user->id_fk_communaute_braldun, TypeEvenementCommunaute::ID_TYPE_INITIALISATION_DEPENDANCE, $details, $detailsBot, $this->view);

    }

    function getListBoxRefresh()
    {
        $tab = array("box_profil", "box_lieu", "box_communaute", "box_evenements", "box_communaute_evenements", "box_cockpit");
        if ($this->view->nomLieu != null) {
            $tab[] = "box_vue";
        }
        return $tab;
    }

}