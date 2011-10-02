<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Jetpoilagratter extends Bral_Monstres_Competences_Attaque
{

    public function calculJetAttaque()
    {
    }

    public function calculDegat($estCritique)
    {
    }

    public function actionSpecifique()
    {
        Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - actionSpecifique - enter");

        Zend_Loader::loadClass("Bral_Util_Effets");

        $malus = $this->monstre["niveau_monstre"] + Bral_Util_De::get_1d6();
        $nbTours = Bral_Util_De::get_1d3();

        $jetMonstre = Bral_Util_De::getLanceDe6(self::$config->game->base_vigueur + $this->monstre["vigueur_base_monstre"]);
        $jetMonstre = $jetMonstre + $this->monstre["vigueur_bm_monstre"];

        $jetBraldun = Bral_Util_De::getLanceDe6(self::$config->game->base_agilite + $this->cible["agilite_base_braldun"]);
        $jetBraldun = $jetBraldun + $this->cible["agilite_bm_braldun"] + $this->cible["agilite_bbdf_braldun"];

        if ($jetBraldun > $jetMonstre) {
            $malus = floor($malus / 2);
        }

        Bral_Util_Effets::ajouteEtAppliqueEffetBraldun($this->cible["id_braldun"], Bral_Util_Effets::CARACT_ARMURE, Bral_Util_Effets::TYPE_MALUS, $nbTours, $malus, "Jet de poil à gratter");
        $this->majEvenement($this->cible, $malus, $nbTours, $jetMonstre, $jetBraldun);

        Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - actionSpecifique - exit");
        return null;
    }

    private function majEvenement($braldun, $malus, $nbTours, $jetMonstre, $jetBraldun)
    {
        Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - majEvenement - enter");
        $idTypeEvenement = self::$config->game->evenements->type->attaquer;
        $details = "[m" . $this->monstre["id_monstre"] . "] jette du poil à gratter sur [b" . $braldun["id_braldun"] . "]";
        $detailsBot = $this->getDetailsBot($malus, $nbTours, $jetMonstre, $jetBraldun);
        Bral_Util_Evenement::majEvenementsFromVieMonstre($braldun["id_braldun"], $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $braldun["niveau_braldun"], $this->view);
        Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - majEvenement - exit");
    }

    protected function getDetailsBot($malus, $nbTours, $jetMonstre, $jetBraldun)
    {
        Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - getDetailsBot - enter");
        $retour = "";
        $retour .= $this->monstre["nom_type_monstre"] . " (" . $this->monstre["id_monstre"] . ") vous jette du poil à gratter :";
        $retour .= PHP_EOL . "Jet du Monstre (jet de vigueur) : " . $jetMonstre;
        $retour .= PHP_EOL . "Jet de résistance (jet d'agilite) : " . $jetBraldun;
        if ($jetBraldun > $jetMonstre) {
            $retour .= PHP_EOL . "Vous avez résisté au jet, le malus est divisé par 2.";
        } else {
            $retour .= PHP_EOL . "Vous n'avez pas résisté au jet.";
        }
        $retour .= PHP_EOL . "Malus sur votre armure : -" . $malus;
        $retour .= PHP_EOL . "Nombre de tours : " . $nbTours;
        Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - getDetailsBot - exit");
        return $retour;
    }
}