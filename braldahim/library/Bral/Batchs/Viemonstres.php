<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_Viemonstres extends Bral_Batchs_Batch
{

    public function calculBatchImpl()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Viemonstres - calculBatchImpl - enter -");
        $retour = null;

        Zend_Loader::loadClass("Evenement");
        Zend_Loader::loadClass('GroupeMonstre');
        Zend_Loader::loadClass('Monstre');
        Zend_Loader::loadClass("Bral_Monstres_VieGroupes");
        Zend_Loader::loadClass("Bral_Monstres_VieGroupesNuee");
        Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
        Zend_Loader::loadClass("Bral_Monstres_VieSolitaire");
        Zend_Loader::loadClass("Bral_Monstres_VieGibier");
        Zend_Loader::loadClass("Bral_Util_Evenement");
        Zend_Loader::loadClass("Bral_Util_Attaque");
        Zend_Loader::loadClass("Bral_Util_Vie");
        Zend_Loader::loadClass("Bral_Monstres_Competences_Factory");
        Zend_Loader::loadClass("Bral_Monstres_Competences_Competence");
        Zend_Loader::loadClass("Bral_Monstres_Competences_Reperage");
        Zend_Loader::loadClass("Bral_Monstres_Competences_Prereperage");
        Zend_Loader::loadClass("Bral_Monstres_Competences_Deplacement");
        Zend_Loader::loadClass("TypeMonstreMCompetence");
        Zend_Loader::loadClass("Ville");

        $villeTable = new Ville();
        $villes = $villeTable->fetchAll();

        $vieGroupe = new Bral_Monstres_VieGroupesNuee($this->view, $villes);
        $vieGroupe->action();

        $vieSolitaire = new Bral_Monstres_VieSolitaire($this->view, $villes);
        $vieSolitaire->action();

        $vieGibier = new Bral_Monstres_VieGibier($this->view, $villes);
        $vieGibier->action();

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Viemonstres - calculBatchImpl - exit -");
        return $retour;
    }

    private function purgeBatch()
    {
    }
}