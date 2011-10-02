<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Potion
{

    const HISTORIQUE_CREATION_ID = 1;
    const HISTORIQUE_UTILISER_ID = 2;
    const HISTORIQUE_ACHETER_ID = 3;
    const HISTORIQUE_VENDRE_ID = 4;
    const HISTORIQUE_TRANSBAHUTER_ID = 5;

    public static function getNomType($typePotion)
    {
        switch ($typePotion) {
            case "potion":
                return "Potion";
                break;
            case "vernis_reparateur":
                return "Vernis réparateur";
                break;
            case "vernis_enchanteur":
                return "Vernis enchanteur";
                break;
            default:
                throw new Zend_Exception("Bral_Util_Potion::getNomType typePotion invalide id:" . $typePotion);
                break;
        }
    }

    public static function insertHistorique($idTypeHistoriquePotion, $idPotion, $details)
    {
        Zend_Loader::loadClass("Bral_Util_Lien");
        $detailsTransforme = Bral_Util_Lien::remplaceBaliseParNomEtJs($details);

        Zend_Loader::loadClass('HistoriquePotion');
        $historiquePotionTable = new HistoriquePotion();

        $data = array(
            'date_historique_potion' => date("Y-m-d H:i:s"),
            'id_fk_type_historique_potion' => $idTypeHistoriquePotion,
            'id_fk_historique_potion' => $idPotion,
            'details_historique_potion' => $detailsTransforme,
        );
        $historiquePotionTable->insert($data);
    }

    public static function prepareTabPotions($potions)
    {

        $tabPotions = null;
        foreach ($potions as $p) {
            $tabPotions[] = array(
                "id_potion" => $p["id_potion"],
                "id_type_potion" => $p["id_type_potion"],
                "id_fk_type_potion" => $p["id_fk_type_potion"],
                "id_fk_type_qualite_potion" => $p["id_fk_type_qualite_potion"],
                "nom_systeme_type_qualite" => $p["nom_systeme_type_qualite"],
                "nom" => $p["nom_type_potion"],
                "de" => $p["de_type_potion"],
                "qualite" => $p["nom_type_qualite"],
                "niveau" => $p["niveau_potion"],
                "caracteristique" => $p["caract_type_potion"],
                "bm_type" => $p["bm_type_potion"],
                "caracteristique2" => $p["caract2_type_potion"],
                "bm2_type" => $p["bm2_type_potion"],
                "nom_type" => self::getNomType($p["type_potion"]),
                "type_potion" => $p["type_potion"],
                'template_m_type_potion' => $p["template_m_type_potion"],
                'template_f_type_potion' => $p["template_f_type_potion"],
                'id_fk_type_ingredient_type_potion' => $p["id_fk_type_ingredient_type_potion"],
            );
        }

        return $tabPotions;
    }

    public static function possedePotion($idBraldun, $idPotion)
    {
        Zend_Loader::loadClass("CharrettePotion");
        Zend_Loader::loadClass("EchoppePotion");
        Zend_Loader::loadClass("LabanPotion");

        $table = new LabanPotion();
        $potion = $table->findByIdBraldun($idBraldun, null, $idPotion);
        if ($potion != null) {
            return true;
        }

        $table = new CharrettePotion();
        $potion = $table->findByIdBraldun($idBraldun, null, $idPotion);
        if ($potion != null) {
            return true;
        }

        $table = new EchoppePotion();
        $potion = $table->findByIdEchoppe($idBraldun, null, $idPotion);
        if ($potion != null) {
            return true;
        }

        return false;
    }
}
