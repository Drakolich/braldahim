<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_Factory
{
    static function calculBatch($nomSystemeAction, $view = null, $param = null)
    {
        Zend_Loader::loadClass("Bral_Batchs_Batch");

        $construct = null;
        $construct = "Bral_Batchs_" . $nomSystemeAction;
        try {
            Zend_Loader::loadClass("Bral_Batchs_Boutique");
            Zend_Loader::loadClass($construct);
        } catch (Exception $e) {
            throw new Zend_Exception("Bral_Batchs_Factory construct invalide (classe): " . $nomSystemeAction);
        }

        // verification que la classe de l'action existe.
        if (($construct != null) && (class_exists($construct))) {
            $batchClasse = new $construct ($nomSystemeAction, $view);
            return $batchClasse->calculBatch($param);
        } else {
            throw new Zend_Exception("Bral_Boutique_Batch action invalide: " . $nomSystemeAction);
        }
    }

}