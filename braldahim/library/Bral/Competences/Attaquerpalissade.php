<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Attaquerpalissade extends Bral_Competences_Competence
{

    function prepareCommun()
    {
        Zend_Loader::loadClass('Bral_Util_Attaque');
        Zend_Loader::loadClass('Palissade');
        Zend_Loader::loadClass("BraldunEquipement");

        $this->view->attaquerPalissadeOk = false;

        $armeTirPortee = false;
        $braldunEquipement = new BraldunEquipement();
        $equipementPorteRowset = $braldunEquipement->findByTypePiece($this->view->user->id_braldun, "arme_tir");

        if (count($equipementPorteRowset) > 0) {
            $armeTirPortee = true;
        } else if ($this->view->user->est_intangible_braldun == "non") {
            $this->distance = 1;
            $this->view->x_min = $this->view->user->x_braldun - $this->distance;
            $this->view->x_max = $this->view->user->x_braldun + $this->distance;
            $this->view->y_min = $this->view->user->y_braldun - $this->distance;
            $this->view->y_max = $this->view->user->y_braldun + $this->distance;

            $palissadeTable = new Palissade();
            $palissades = $palissadeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_braldun);
            $defautChecked = false;

            for ($j = $this->distance; $j >= -$this->distance; $j--) {
                $change_level = true;
                for ($i = -$this->distance; $i <= $this->distance; $i++) {
                    $x = $this->view->user->x_braldun + $i;
                    $y = $this->view->user->y_braldun + $j;

                    $display = $x;
                    $display .= " ; ";
                    $display .= $y;

                    $valid = false;
                    $palissade = null;
                    $est_destructible = null;

                    foreach ($palissades as $p) {
                        if ($x == $p["x_palissade"] && $y == $p["y_palissade"]) {
                            $est_destructible = $p["est_destructible_palissade"];
                            if ($est_destructible == "oui") {
                                $valid = true;
                                $palissade = $p;
                            }
                            break;
                        }
                    }

                    if ($valid === true && $defautChecked == false) {
                        $default = "checked";
                        $defautChecked = true;
                        $this->view->attaquerPalissadeOk = true;
                    } else {
                        $default = "";
                    }

                    $tab[] = array("x_offset" => $i,
                        "y_offset" => $j,
                        "default" => $default,
                        "display" => $display,
                        "change_level" => $change_level, // nouvelle ligne dans le tableau
                        "valid" => $valid,
                        "est_destructible" => $est_destructible,
                    );

                    if ($this->request->get("valeur_1") != null) { // attaque palissade en cours
                        $x_y = $this->request->get("valeur_1");
                        list ($offset_x, $offset_y) = preg_split("/h/", $x_y);
                        if ($offset_x == $i && $offset_y == $j && $valid == true) {
                            $this->view->palissade = $palissade;
                        }
                    }

                    $tabValidation[$i][$j] = $valid;

                    if ($change_level) {
                        $change_level = false;
                    }
                }
            }
            $this->view->tableau = $tab;
            $this->tableauValidation = $tabValidation;
        }
        $this->view->armeTirPortee = $armeTirPortee;
    }

    function prepareFormulaire()
    {
        if ($this->view->assezDePa == false) {
            return;
        }
    }

    function prepareResultat()
    {
        // Verification des Pa
        if ($this->view->assezDePa == false) {
            throw new Zend_Exception(get_class($this) . " Pas assez de PA : " . $this->view->user->pa_braldun);
        }

        if ($this->view->attaquerPalissadeOk == false) {
            throw new Zend_Exception(get_class($this) . " Attaquer Palissade interdit");
        }

        // on verifie que l'on peut attaquer une palissade sur la case
        $x_y = $this->request->get("valeur_1");
        list ($offset_x, $offset_y) = preg_split("/h/", $x_y);
        if ($offset_x < -$this->distance || $offset_x > $this->distance) {
            throw new Zend_Exception(get_class($this) . " AttaquerPalissade X impossible : " . $offset_x);
        }

        if ($offset_y < -$this->distance || $offset_y > $this->distance) {
            throw new Zend_Exception(get_class($this) . " AttaquerPalissade Y impossible : " . $offset_y);
        }

        if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
            throw new Zend_Exception(get_class($this) . " AttaquerPalissade XY impossible : " . $offset_x . $offset_y);
        }

        if ($this->view->palissade == null) {
            throw new Zend_Exception(get_class($this) . " AttaquerPalissade Null");
        }

        $idType = $this->view->config->game->evenements->type->attaquer;
        $details = "[b" . $this->view->user->id_braldun . "] a attaqué une palissade";
        $this->setDetailsEvenement($details, $idType);
        $this->setEvenementQueSurOkJet1(false);
        $this->calculAttaquerPalissade();
        $this->calculPx();
        $this->calculBalanceFaim();
        $this->majBraldun();
    }

    private function calculAttaquerPalissade()
    {

        $tabDegats = Bral_Util_Attaque::calculDegatAttaqueNormale($this->view->user);
        $this->view->degats = $tabDegats["noncritique"];

        $this->view->detruire = false;
        $this->view->apparitionMonstre = false;
        $this->view->degatsInfliges = $this->view->degats - $this->view->palissade["armure_naturelle_palissade"];

        if ($this->view->degatsInfliges < 0) {
            $this->view->degatsInfliges = 0;
        }

        $this->view->palissade["pv_restant_palissade"] = $this->view->palissade["pv_restant_palissade"] - $this->view->degatsInfliges;
        if ($this->view->palissade["pv_restant_palissade"] <= 0) {
            $this->view->palissade["pv_restant_palissade"] = 0;
            $this->view->detruire = true;
            $this->calculRuine();
        }

        $palissadeTable = new Palissade();

        if ($this->view->detruire) {
            $where = "id_palissade=" . intval($this->view->palissade["id_palissade"]);
            $palissadeTable->delete($where);
        } else {
            if ($this->view->degatsInfliges > 0) {
                $data = array(
                    "pv_restant_palissade" => $this->view->palissade["pv_restant_palissade"],
                );

                $where = "id_palissade=" . intval($this->view->palissade["id_palissade"]);
                $palissadeTable->update($data, $where);
            }
        }

        unset($palissadeTable);
    }

    /**
     * En cas de destruction de la palissade, on regarde s'il y a une ruine à côté.
     */
    private function calculRuine()
    {

        Zend_Loader::loadClass("Lieu");
        Zend_Loader::loadClass("TypeLieu");

        $lieuTable = new Lieu();
        $xMin = $this->view->palissade["x_palissade"] - 1;
        $yMin = $this->view->palissade["y_palissade"] - 1;
        $xMax = $this->view->palissade["x_palissade"] + 1;
        $yMax = $this->view->palissade["y_palissade"] + 1;
        $z = $this->view->palissade["z_palissade"];
        $ruines = $lieuTable->selectVue($xMin, $yMin, $xMax, $yMax, $z, TypeLieu::ID_TYPE_RUINE);
        if ($ruines == null || count($ruines) <= 0) {
            return; // pas de ruine à côté
        }
        $ruine = $ruines[0];

        Zend_Loader::loadClass("Nid");
        Zend_Loader::loadClass("ZoneNid");
        Zend_Loader::loadClass("TypeMonstre");
        Zend_Loader::loadClass("Braldun");

        $nidTable = new Nid();
        $zoneNidTable = new ZoneNid();
        $braldunTable = new Braldun();

        // récupération d'une zone de nid
        $zonesNids = $zoneNidTable->findByCase($ruine["x_lieu"], $ruine["y_lieu"], $ruine["z_lieu"]);
        if (count($zonesNids) == 0) {
            throw new Zend_Exception("Erreur AttaquerPalissade. zonesNids invalide:x:" . $ruine["x_lieu"] . ", y:" . $ruine["y_lieu"] . ", z:" . $ruine["z_lieu"]);
        }

        $xMin = $this->view->palissade["x_palissade"] - 10;
        $yMin = $this->view->palissade["y_palissade"] + 10;
        $xMax = $this->view->palissade["x_palissade"] - 10;
        $yMax = $this->view->palissade["y_palissade"] + 10;

        $bralduns = $braldunTable->selectVue($xMin, $yMin, $xMax, $yMax, $z);
        $nbMin = 1;
        $nbMax = 1;
        if ($bralduns != null && count($bralduns) > 2) {
            $nbMax = count($bralduns);
        }
        if ($nbMax > 8) {
            $nbMax = 8;
        }

        $zoneNid = $zonesNids[0];

        $nbMonstres = Bral_Util_De::get_de_specifique($nbMin, $nbMax);
        $data["x_nid"] = $ruine["x_lieu"];
        $data["y_nid"] = $ruine["y_lieu"];
        $data["z_nid"] = $ruine["z_lieu"];
        $data["nb_monstres_total_nid"] = $nbMonstres;
        $data["nb_monstres_restants_nid"] = $nbMonstres;

        $data["id_fk_zone_nid"] = $zoneNid["id_zone_nid"];
        $data["id_fk_type_monstre_nid"] = TypeMonstre::ID_TYPE_DRAGON;

        $data["id_fk_donjon_nid"] = null;
        $data["date_creation_nid"] = date("Y-m-d H:i:s");
        $data["date_generation_nid"] = Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), -1);

        $nidTable->insert($data);

        $this->view->apparitionMonstre = true;
    }

    protected function calculPx()
    {
        $this->view->calcul_px_generique = false;
        if ($this->view->degatsInfliges > 0) {
            $this->view->nb_px = 1;
        } else {
            $this->view->nb_px = 0;
        }
        $this->view->nb_px_perso = $this->view->nb_px;
    }

    function getListBoxRefresh()
    {
        return $this->constructListBoxRefresh(array("box_vue"));
    }
}
