<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Elaborer extends Bral_Competences_Competence
{

    const NOM_METIER = "apothicaire";

    function prepareCommun()
    {
        Zend_Loader::loadClass("Echoppe");
        Zend_Loader::loadClass("Bral_Util_Metier");

        $id_type_courant = $this->request->get("type_potion");
        $niveau_courant = $this->request->get("niveau_courant");

        $typePotionCourante = null;

        // On regarde si le Braldûn est dans une de ses echopppes
        $echoppeTable = new Echoppe();
        $echoppes = $echoppeTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

        $this->view->elaborerEchoppeOk = false;
        if ($echoppes == null || count($echoppes) == 0) {
            $this->view->elaborerEchoppeOk = false;
            return;
        }

        $idEchoppe = -1;
        foreach ($echoppes as $e) {
            if ($e["id_fk_braldun_echoppe"] == $this->view->user->id_braldun &&
                    $e["nom_systeme_metier"] == self::NOM_METIER &&
                    $e["x_echoppe"] == $this->view->user->x_braldun &&
                    $e["y_echoppe"] == $this->view->user->y_braldun &&
                    $e["z_echoppe"] == $this->view->user->z_braldun
            ) {
                $this->view->elaborerEchoppeOk = true;
                $idEchoppe = $e["id_echoppe"];

                $echoppeCourante = array(
                    'id_echoppe' => $e["id_echoppe"],
                    'x_echoppe' => $e["x_echoppe"],
                    'y_echoppe' => $e["y_echoppe"],
                    'id_metier' => $e["id_metier"],
                    'quantite_cuir_arriere_echoppe' => $e["quantite_cuir_arriere_echoppe"],
                    'quantite_fourrure_arriere_echoppe' => $e["quantite_fourrure_arriere_echoppe"],
                    'quantite_planche_arriere_echoppe' => $e["quantite_planche_arriere_echoppe"],
                );
                break;
            }
        }

        if ($this->view->elaborerEchoppeOk == false) {
            return;
        }

        Zend_Loader::loadClass("TypePotion");
        $typePotionTable = new TypePotion();
        $typePotionRowset = $typePotionTable->findAll();
        $tabTypePotion = null;

        Zend_Loader::loadClass("Bral_Util_Potion");

        foreach ($typePotionRowset as $t) {
            $selected = "";
            if ($id_type_courant == $t["id_type_potion"]) {
                $selected = "selected";
            }
            $t = array(
                'id_type_potion' => $t["id_type_potion"],
                'nom_type_potion' => $t["nom_type_potion"],
                'type_potion' => $t["type_potion"],
                'selected' => $selected,
                'bm_type_potion' => $t["bm_type_potion"],
                'caract_type_potion' => $t["caract_type_potion"],
                'bm2_type_potion' => $t["bm2_type_potion"],
                'caract2_type_potion' => $t["caract2_type_potion"],
                'nom_type_ingredient' => $t["nom_type_ingredient"],
                'nom_systeme_type_ingredient' => $t["nom_systeme_type_ingredient"],
                'id_fk_type_minerai_ingredient' => $t["id_fk_type_minerai_ingredient"],
            );
            if ($id_type_courant == $t["id_type_potion"]) {
                $typePotionCourante = $t;
            }

            if ($t["type_potion"] == "potion") {
                $t["nom_type_potion"] .= " (" . $t["bm_type_potion"] . " sur " . $t["caract_type_potion"] . ")";
            } else if ($t["type_potion"] == "vernis_enchanteur") {
                $t["nom_type_potion"] .= " (" . $t["bm_type_potion"] . " sur " . $t["caract_type_potion"] . ", " . $t["bm2_type_potion"] . " sur " . $t["caract2_type_potion"] . ")";
            }

            if ($t["type_potion"] == "potion" || $this->view->user->niveau_braldun > 9) {
                $tabTypePotion[$t["type_potion"]]["liste"][] = $t;
                $tabTypePotion[$t["type_potion"]]["nom"] = Bral_Util_Potion::getNomType($t["type_potion"]);
            }
        }

        $tabNiveaux = null;
        $tabCout = null;
        $this->view->ressourcesOk = true;
        $this->view->etape1 = false;
        $this->view->typePotionCourante = null;
        $this->view->cout = null;
        $this->view->niveaux = null;
        $this->view->elaborerPlanteOk = false;

        $this->idEchoppe = $idEchoppe;
        $this->echoppeCourante = $echoppeCourante;

        if (isset($typePotionCourante)) {
            $this->prepareCourant($typePotionCourante, $idEchoppe);
        }
        $this->view->typePotion = $tabTypePotion;
    }

    function prepareCourant($typePotionCourante, $idEchoppe)
    {
        Zend_Loader::loadClass("EchoppePartieplante");
        $tabPartiePlantes = null;
        $echoppePlanteTable = new EchoppePartieplante();
        $partiesPlantes = $echoppePlanteTable->findByIdEchoppe($idEchoppe);

        Zend_Loader::loadClass("EchoppePartieplante");
        $this->view->etape1 = true;

        if ($typePotionCourante["type_potion"] == "potion") {
            for ($i = 0; $i <= $this->view->user->niveau_braldun / 10; $i++) {
                $tabNiveaux[$i] = array('niveauText' => 'Niveau ' . $i, 'ressourcesOk' => true);
            }
            $this->preparePotionCourante($typePotionCourante, $tabNiveaux, $partiesPlantes);
        } else {
            for ($i = 1; $i <= $this->view->user->niveau_braldun / 10; $i++) {
                $tabNiveaux[$i] = array('niveauText' => 'Niveau ' . $i, 'ressourcesOk' => true);
            }
            $this->prepareVernisCourant($typePotionCourante, $tabNiveaux, $partiesPlantes);
        }
    }

    function preparePotionCourante($typePotionCourante, &$tabNiveaux, $partiesPlantes)
    {
        Zend_Loader::loadClass("RecettePotions");

        $recettePotionsTable = new RecettePotions();
        $recettePotions = $recettePotionsTable->findByIdTypePotion($typePotionCourante["id_type_potion"]);

        if ($partiesPlantes != null) {
            foreach ($partiesPlantes as $m) {
                if ($m["quantite_preparee_echoppe_partieplante"] >= 1) {
                    $tabPartiePlantes[$m["id_fk_type_plante_echoppe_partieplante"]][$m["id_fk_type_echoppe_partieplante"]] = array(
                        "nom_type_partieplante" => $m["nom_type_partieplante"],
                        "nom_type" => $m["nom_type_plante"],
                        "quantite_preparees" => $m["quantite_preparee_echoppe_partieplante"],
                    );
                    $this->view->elaborerPlanteOk = true;
                }
            }
        }

        foreach ($tabNiveaux as $k => $v) {
            foreach ($recettePotions as $r) {
                $tabCout[$k][] = array(
                    "nom_type_plante" => $r["nom_type_plante"],
                    "id_type_plante" => $r["id_type_plante"],
                    "nom_type_partieplante" => $r["nom_type_partieplante"],
                    "id_type_partieplante" => $r["id_type_partieplante"],
                    "cout" => ($r["coef_recette_potion"] + $k),
                );

                if (isset($tabPartiePlantes[$r["id_fk_type_plante_recette_potion"]]) && (isset($tabPartiePlantes[$r["id_fk_type_plante_recette_potion"]][$r["id_fk_type_partieplante_recette_potion"]]["quantite_preparees"]))) {
                    if (($r["coef_recette_potion"] + $k) > $tabPartiePlantes[$r["id_fk_type_plante_recette_potion"]][$r["id_fk_type_partieplante_recette_potion"]]["quantite_preparees"]) {
                        $tabNiveaux[$k]["ressourcesOk"] = false;
                    }
                } else {
                    $tabNiveaux[$k]["ressourcesOk"] = false;
                }
            }
        }

        $this->view->cout = $tabCout;
        $this->view->niveaux = $tabNiveaux;
        $this->view->typePotionCourante = $typePotionCourante;
    }

    function prepareVernisCourant($typePotionCourante, &$tabNiveaux, $partiesPlantes)
    {
        Zend_Loader::loadClass("RecetteVernis");

        $recetteVernisTable = new RecetteVernis();
        $recetteVernis = $recetteVernisTable->findByIdTypePotion($typePotionCourante["id_type_potion"]);

        $tabPartiePlantes = array();

        if ($partiesPlantes != null) {
            foreach ($partiesPlantes as $m) {
                if ($m["quantite_preparee_echoppe_partieplante"] >= 1) {
                    if ($m["quantite_preparee_echoppe_partieplante"] > 1) {
                        $s = "s";
                    } else {
                        $s = "";
                    }
                    $tabPartiePlantes[$m["id_fk_type_echoppe_partieplante"]][$m["id_fk_type_plante_echoppe_partieplante"]] = array(
                        "id_calcule" => $m["id_fk_type_echoppe_partieplante"] . "-" . $m["id_fk_type_plante_echoppe_partieplante"],
                        "nom_type_partieplante" => $m["nom_type_partieplante"] . $s,
                        "nom_type" => $m["nom_type_plante"],
                        "quantite_preparees" => $m["quantite_preparee_echoppe_partieplante"],
                        "id_type_partieplante" => $m["id_fk_type_echoppe_partieplante"],
                    );
                    $this->view->elaborerPlanteOk = true;
                }
            }
        }

        $tabCout = null;
        $tabCoutIngredient = null;

        Zend_Loader::loadClass("EchoppeMinerai");

        $echoppeMineraiTable = new EchoppeMinerai();
        $minerais = $echoppeMineraiTable->findByIdEchoppe($this->idEchoppe);

        foreach ($tabNiveaux as $k => $v) {
            $n = 0;
            foreach ($recetteVernis as $r) {
                $n++;
                $tabCout[$k][] = array(
                    "nom_type_plante" => "Plante choisie n°" . $n,
                    "nom_type_partieplante" => $r["nom_type_partieplante"],
                    "id_type_partieplante" => $r["id_type_partieplante"],
                    "cout" => $k + 1,
                );
            }
            if ($typePotionCourante["type_potion"] == "vernis_enchanteur") {
                $n++;
                $tabCout[$k][] = array(
                    "nom_type_plante" => "Plante choisie n°" . $n,
                    "nom_type_partieplante" => "élément",
                    "id_type_partieplante" => -1,
                    "cout" => $k + 3,
                );
            } else if ($typePotionCourante["type_potion"] == "vernis_reparateur") {
                $ressourcesOk = false;
                $cout = $k + 1;
                if ($typePotionCourante["nom_systeme_type_ingredient"] == "anga" ||
                        $typePotionCourante["nom_systeme_type_ingredient"] == "galvorn" ||
                        $typePotionCourante["nom_systeme_type_ingredient"] == "mithril" ||
                        $typePotionCourante["nom_systeme_type_ingredient"] == "tambe"
                ) {
                    $nom = $typePotionCourante["nom_type_ingredient"] . " : " . ($k + 1) . " lingots";
                    foreach ($minerais as $m) {
                        if ($m["id_fk_type_echoppe_minerai"] == $typePotionCourante["id_fk_type_minerai_ingredient"] && $m["quantite_lingots_echoppe_minerai"] >= $cout) {
                            $ressourcesOk = true;
                            break;
                        }
                    }
                } else {
                    $nom = $typePotionCourante["nom_type_ingredient"] . " : " . ($k + 1);
                    if ($this->echoppeCourante["quantite_" . $typePotionCourante["nom_systeme_type_ingredient"] . "_arriere_echoppe"] >= $cout) {
                        $ressourcesOk = true;
                    }
                }

                if ($ressourcesOk == false) {
                    $tabNiveaux[$k]["ressourcesOk"] = false;
                }

                $tabCoutIngredient[$k][] = array(
                    "nom" => $nom,
                    "nom_systeme" => $typePotionCourante["nom_systeme_type_ingredient"],
                    "id_type_minerai" => $typePotionCourante["id_fk_type_minerai_ingredient"],
                    "cout" => $cout,
                    "ressourcesOk" => $ressourcesOk,
                );
            }
        }

        $this->view->coutIngredient = $tabCoutIngredient;
        $this->view->cout = $tabCout;
        $this->view->niveaux = $tabNiveaux;
        $this->view->typePotionCourante = $typePotionCourante;
        $this->view->tabPartiePlantes = $tabPartiePlantes;
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

        // Verification elaborer
        if ($this->view->elaborerEchoppeOk == false) {
            throw new Zend_Exception(get_class($this) . " Elaborer Echoppe interdit ");
        }

        // verification ressources
        $idTypePotion = (int)$this->request->get("valeur_1");
        $niveau = (int)$this->request->get("valeur_2");

        if ($idTypePotion != $this->view->typePotionCourante["id_type_potion"]) {
            throw new Zend_Exception(get_class($this) . " idTypePotion interdit A=" . $idTypePotion . " B=" . $this->view->typePotionCourante["id_type_potion"]);
        }

        if ($this->view->typePotionCourante["type_potion"] == "potion") {
            $niveauxOk = false;
            foreach ($this->view->niveaux as $k => $v) {
                if ($k == $niveau && $v["ressourcesOk"] === true) {
                    $niveauxOk = true;
                }
            }
            if ($niveauxOk == false) {
                throw new Zend_Exception(get_class($this) . " Niveau interdit ");
            }
        } else {
            $ingredient1 = $this->request->get("valeur_3");
            $ingredient2 = $this->request->get("valeur_4");
            $ingredient3 = $this->request->get("valeur_5");
        }

        // calcul des jets
        $this->calculJets();

        $coef = 2;
        if ($this->view->okJet1 === true) {
            $coef = 1;
        }

        if ($this->view->typePotionCourante["type_potion"] == "potion") {
            $this->calculCoutElaborerPotion($niveau, $coef);
        } else {
            $this->calculCoutElaborerVernis($niveau, $ingredient1, $ingredient2, $ingredient3, $coef);
        }

        if ($this->view->okJet1 === true) {
            $this->calculElaborer($idTypePotion, $niveau);
        }

        if ($this->view->nbPotions > 1) {
            $s = "s";
        } else {
            $s = "";
        }

        if ($this->view->typePotionCourante["type_potion"] == "potion") {
            $nom = "potion" . $s;
        } elseif ($this->view->typePotionCourante["type_potion"] == "vernis_enchanteur") {
            $nom = "vernis enchanteur" . $s;
        } else { // reparateur
            $nom = "vernis réparateur" . $s;
        }
        $this->view->nomPotionVernis = $nom;
        $this->calculPx();
        $this->calculBalanceFaim();
        $this->majBraldun();
    }

    private function calculCoutElaborerPotion($niveau, $coef)
    {
        Zend_Loader::loadClass("EchoppePartieplante");
        $echoppePartiePlanteTable = new EchoppePartieplante();
        foreach ($this->view->cout[$niveau] as $c) {
            $data = array('quantite_preparee_echoppe_partieplante' => -intval($c["cout"] / $coef),
                'id_fk_type_echoppe_partieplante' => $c["id_type_partieplante"],
                'id_fk_type_plante_echoppe_partieplante' => $c["id_type_plante"],
                'id_fk_echoppe_echoppe_partieplante' => $this->idEchoppe);
            $echoppePartiePlanteTable->insertOrUpdate($data);
        }
    }

    private function calculCoutElaborerVernis($niveau, $ingredient1, $ingredient2, $ingredient3, $coef)
    {
        Zend_Loader::loadClass("EchoppePartieplante");
        $echoppePartiePlanteTable = new EchoppePartieplante();

        list($idTypePartiePlante, $idTypePlante) = preg_split("/-/", $ingredient1);

        $data['id_fk_echoppe_echoppe_partieplante'] = $this->idEchoppe;

        $this->calculCoutElaborerVernisDb($echoppePartiePlanteTable, $niveau, $data, $idTypePartiePlante, $idTypePlante, $coef);
        if ($ingredient3 != -2) { // enchanteur
            list($idTypePartiePlante, $idTypePlante) = preg_split("/-/", $ingredient2);
            $this->calculCoutElaborerVernisDb($echoppePartiePlanteTable, $niveau, $data, $idTypePartiePlante, $idTypePlante, $coef);
            list($idTypePartiePlante, $idTypePlante) = preg_split("/-/", $ingredient3);
            $this->calculCoutElaborerVernisDb($echoppePartiePlanteTable, $niveau, $data, $idTypePartiePlante, $idTypePlante, $coef, true);
        } else if ($ingredient2 != -2) { // protecteur
            throw new Zend_Exception(get_class($this) . " Elaborer invalide calculCoutElaborerVernis");
        } else {
            $this->updateDbIngredient2($niveau, $data, $coef);
        }
    }

    private function calculCoutElaborerVernisDb($echoppePartiePlanteTable, $niveau, $data, $idTypePartiePlante, $idTypePlante, $coef, $estIngredient3 = false)
    {
        $traite = false;
        foreach ($this->view->cout[$niveau] as $c) {
            if ($c["id_type_partieplante"] == $idTypePartiePlante || ($estIngredient3 && $c["id_type_partieplante"] == -1)) {
                if (!isset($this->view->tabPartiePlantes[$idTypePartiePlante]) ||
                        !isset($this->view->tabPartiePlantes[$idTypePartiePlante][$idTypePlante]) ||
                        $this->view->tabPartiePlantes[$idTypePartiePlante][$idTypePlante]["quantite_preparees"] < intval($c["cout"])
                ) {
                    throw new Zend_Exception(get_class($this) . " Elaborer invalide calculCoutElaborerVernisDb 1 N:" . $niveau . " coef:" . $coef . " idT" . $idTypePartiePlante . " idP" . $idTypePlante . " cout:" . $c["cout"]);
                }
                $data['id_fk_type_echoppe_partieplante'] = $idTypePartiePlante;
                $data['id_fk_type_plante_echoppe_partieplante'] = $idTypePlante;
                $data['quantite_preparee_echoppe_partieplante'] = -intval($c["cout"] / $coef);
                $echoppePartiePlanteTable->insertOrUpdate($data);
                $traite = true;
                break;
            }
        }
        if ($traite == false) {
            throw new Zend_Exception(get_class($this) . " Elaborer invalide calculCoutElaborerVernisDb 2 N:" . $niveau . " coef:" . $coef . " idT" . $idTypePartiePlante . " idP" . $idTypePlante);
        }
    }

    private function updateDbIngredient2($niveau, $data, $coef)
    {

        $echoppeMineraiTable = new EchoppeMinerai();
        $minerais = $echoppeMineraiTable->findByIdEchoppe($this->idEchoppe);

        foreach ($this->view->coutIngredient[$niveau] as $c) {
            if ($c["nom_systeme"] == "planche" || $c["nom_systeme"] == "cuir" || $c["nom_systeme"] == "fourrure") {
                if ($this->echoppeCourante["quantite_" . $c["nom_systeme"] . "_arriere_echoppe"] < intval($c["cout"])) {
                    throw new Zend_Exception(get_class($this) . " Elaborer invalide updateDbIngredient2 1 N:" . $niveau . " coef:" . $coef . " n:" . $c["nom_systeme"]);
                }
                $echoppeTable = new Echoppe();
                $data['id_echoppe'] = $this->idEchoppe;
                $data['quantite_' . $c["nom_systeme"] . '_arriere_echoppe'] = -intval($c["cout"] / $coef);
                $echoppeTable->insertOrUpdate($data);
                break;
            } else { // lingot
                $traite = false;
                foreach ($minerais as $m) {
                    if ($m["id_fk_type_echoppe_minerai"] == $c["id_type_minerai"] && $m["quantite_lingots_echoppe_minerai"] >= $c["cout"]) {
                        $traite = true;
                    }
                }
                if ($traite == false) {
                    throw new Zend_Exception(get_class($this) . " Elaborer invalide updateDbIngredient2 3 N:" . $niveau . " coef:" . $coef . " n:" . $c["nom_systeme"]);
                }
                $data['id_fk_type_echoppe_minerai'] = $c["id_type_minerai"];
                $data['id_fk_echoppe_echoppe_minerai'] = $this->idEchoppe;
                $data['quantite_lingots_echoppe_minerai'] = -intval($c["cout"] / $coef);
                $echoppeMineraiTable->insertOrUpdate($data);
            }
        }
    }

    private function calculElaborer($idTypePotion, $niveau)
    {
        $this->view->effetRune = false;

        $maitrise = $this->braldun_competence["pourcentage_hcomp"] / 100;

        $chance_a = -0.375 * $maitrise + 53.75;
        $chance_b = 0.25 * $maitrise + 42.5;
        $chance_c = 0.125 * $maitrise + 3.75;

        /*
           * Seul le meilleur des n jets est gardé. n=(BM SAG/2)+1.
           */
        $n = (($this->view->user->sagesse_bm_braldun + $this->view->user->sagesse_bbdf_braldun) / 2) + 1;

        if ($n < 1) $n = 1;

        $tirage = 0;

        for ($i = 1; $i <= $n; $i++) {
            $tirageTemp = Bral_Util_De::get_1d100();
            if ($tirageTemp > $tirage) {
                $tirage = $tirageTemp;
            }
        }

        if (Bral_Util_Commun::isRunePortee($this->view->user->id_braldun, "AP")) { // s'il possede une rune AP
            $this->view->effetRune = true;
            $tirage = $tirage + 10;
            if ($tirage > 100) {
                $tirage = 100;
            }
        }

        $qualite = -1;
        if ($tirage > 0 && $tirage <= $chance_a) {
            $qualite = 1;
            $this->view->qualite = "m&eacute;diocre";
        } elseif ($tirage > $chance_a && $tirage <= $chance_a + $chance_b) {
            $qualite = 2;
            $this->view->qualite = "standard";
        } else {
            $qualite = 3;
            $this->view->qualite = "bonne";
        }
        $this->view->niveau = $niveau;
        $this->view->niveauQualite = $qualite;

        Zend_Loader::loadClass("IdsPotion");
        $idsPotionTable = new IdsPotion();

        Zend_Loader::loadClass("EchoppePotion");
        $echoppePotionTable = new EchoppePotion();
        $dataPotionEchoppe = array(
            'id_fk_echoppe_echoppe_potion' => $this->idEchoppe,
        );

        $dataPotion = array(
            'id_fk_type_potion' => $idTypePotion,
            'id_fk_type_qualite_potion' => $qualite,
            'niveau_potion' => $niveau,
        );
        $this->view->nbPotions = Bral_Util_De::get_2d3();

        Zend_Loader::loadClass("Potion");
        $potionTable = new Potion();
        $listPotions = "";

        for ($i = 1; $i <= $this->view->nbPotions; $i++) {
            $idPotion = $idsPotionTable->prepareNext();
            $dataPotionEchoppe['id_echoppe_potion'] = $idPotion;
            $dataPotion['id_potion'] = $idPotion;
            $potionTable->insert($dataPotion);
            $echoppePotionTable->insert($dataPotionEchoppe);

            $listPotions .= $idPotion . ", ";

            if ($this->view->typePotionCourante["type_potion"] == "potion") {
                $type = "la potion";
            } else {
                $type = "le vernis";
            }
            $details = "[b" . $this->view->user->id_braldun . "] a élaboré " . $type . " n°" . $dataPotionEchoppe['id_echoppe_potion'];
            Bral_Util_Potion::insertHistorique(Bral_Util_Potion::HISTORIQUE_CREATION_ID, $dataPotionEchoppe['id_echoppe_potion'], $details);
        }

        $this->view->idPotions = mb_substr($listPotions, 0, -2);

        Zend_Loader::loadClass("StatsFabricants");
        $statsFabricants = new StatsFabricants();
        $moisEnCours = mktime(0, 0, 0, date("m"), 2, date("Y"));
        $dataFabricants["niveau_braldun_stats_fabricants"] = $this->view->user->niveau_braldun;
        $dataFabricants["id_fk_braldun_stats_fabricants"] = $this->view->user->id_braldun;
        $dataFabricants["mois_stats_fabricants"] = date("Y-m-d", $moisEnCours);
        $dataFabricants["nb_piece_stats_fabricants"] = $this->view->nbPotions;
        $dataFabricants["somme_niveau_piece_stats_fabricants"] = $niveau;
        $dataFabricants["id_fk_metier_stats_fabricants"] = Bral_Util_Metier::METIER_APOTHICAIRE_ID;
        $statsFabricants->insertOrUpdate($dataFabricants);

        Zend_Loader::loadClass("Bral_Util_Competence");
        $nomSystemeCompetence = "produire" . self::NOM_METIER;
        $this->view->competenceAmelioree = Bral_Util_Competence::updateCompetence1d2($nomSystemeCompetence, $this->view->user->id_braldun);
    }

    // Gain : [(nivP+1)/(nivH+1)+1+NivQ]*10 PX
    public function calculPx()
    {
        $this->view->nb_px_commun = 0;
        $this->view->calcul_px_generique = true;
        if ($this->view->okJet1 === true) {
            $this->view->nb_px_perso = floor((($this->view->niveau + 1) / (floor($this->view->user->niveau_braldun / 10) + 1) + 1 + ($this->view->niveauQualite - 1)) * 5);
        } else {
            $this->view->nb_px_perso = 0;
        }
        $this->view->nb_px = floor($this->view->nb_px_perso + $this->view->nb_px_commun);
    }

    public function getIdEchoppeCourante()
    {
        if (isset($this->idEchoppe)) {
            return $this->idEchoppe;
        } else {
            return false;
        }
    }

    function getListBoxRefresh()
    {
        return $this->constructListBoxRefresh(array("box_competences", "box_echoppes"));
    }
}
