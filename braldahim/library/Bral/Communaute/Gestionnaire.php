<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Gestionnaire extends Bral_Communaute_Communaute
{

    function getTitreOnglet()
    {
    }

    function setDisplay($display)
    {
        $this->view->display = $display;
    }

    function getTitre()
    {
        return null;
    }

    function getListBoxRefresh()
    {
        return array("box_communaute_evenements");
    }

    function prepareCommun()
    {
        Zend_Loader::loadClass("Communaute");
        Zend_Loader::loadClass("RangCommunaute");

        $this->preparePage();

        $this->view->isUpdateGestionnaire = false;

        if ($this->_request->get("caction") == "do_communaute_gestionnaire") {
            $this->updateGestionnaire();
        }
    }

    function prepareFormulaire()
    {
    }

    function prepareResultat()
    {
    }

    function getNomInterne()
    {
        return "box_communaute_gestion_interne";
    }

    function preparePage()
    {
        Zend_Loader::loadClass('Bral_Util_Communaute');

        $communauteTable = new Communaute();
        $communauteRowset = $communauteTable->findById($this->view->user->id_fk_communaute_braldun);
        if (count($communauteRowset) == 1) {
            $communaute = $communauteRowset[0];
        }

        if (!$this->view->user->rangCommunaute == Bral_Util_Communaute::ID_RANG_GESTIONNAIRE) {
            throw new Zend_Exception(get_class($this) . " Vous n'êtes pas gestionaire de la communauté");
        }
        if ($communaute == null) {
            throw new Zend_Exception(get_class($this) . " Communaute Invalide");
        }

        $this->communaute = $communaute;
    }

    private function prepareRender()
    {
        $c = array(
            "prenom_braldun" => $this->communaute["prenom_braldun"],
            "nom_braldun" => $this->communaute["nom_braldun"],
            "id_braldun" => $this->communaute["id_braldun"],
        );
        $this->view->communaute = $c;

        $braldunTable = new Braldun();
        $braldunRowset = $braldunTable->findByIdCommunaute($this->communaute["id_communaute"]);
        $tabMembres = null;

        foreach ($braldunRowset as $m) {
            if ($m["ordre_rang_communaute"] != 1) { // on ne met pas le gestionnaire actuel dans la liste
                $tabMembres[] = array(
                    "id_braldun" => $m["id_braldun"],
                    "nom_braldun" => $m["nom_braldun"],
                    "prenom_braldun" => $m["prenom_braldun"],
                    "id_rang_communaute" => $m["id_rang_communaute"],
                    "nom_rang_communaute" => $m["nom_rang_communaute"],
                    "sexe_braldun" => $m["sexe_braldun"],
                );
            }
        }

        $this->view->tabMembres = $tabMembres;
    }

    public function render()
    {
        $this->prepareRender();
        $this->view->nom_interne = $this->getNomInterne();
        return $this->view->render("interface/communaute/gerer/gestionnaire.phtml");
    }

    private function updateGestionnaire()
    {
        $idBraldun = Bral_Util_Controle::getValeurIntVerif($this->_request->getPost("valeur_1"));

        $this->prepareRender();

        $braldunTrouve = false;
        foreach ($this->view->tabMembres as $m) {
            if ($m["id_braldun"] == $idBraldun) {
                $nouveauGestionnaire = $m;
                $braldunTrouve = true;
                break;
            }
        }

        if ($braldunTrouve == false) {
            throw new Zend_Exception(get_class($this) . " Braldûn Invalide:" . $idBraldun);
        }

        $communauteTable = new Communaute();
        $data = array("id_fk_braldun_gestionnaire_communaute" => $idBraldun);
        $where = " id_communaute=" . $this->communaute["id_communaute"];
        $communauteTable->update($data, $where);

        $braldunTable = new Braldun();
        $rangCommunauteTable = new RangCommunaute();
        $rowSet = $rangCommunauteTable->findRangCreateur($this->communaute["id_communaute"]);

        $data = array('id_fk_rang_communaute_braldun' => $rowSet["id_rang_communaute"]);
        $where = 'id_braldun = ' . $idBraldun;
        $braldunTable->update($data, $where);

        $rowSet = $rangCommunauteTable->findRangSecond($this->communaute["id_communaute"]);
        $this->view->user->id_fk_rang_communaute_braldun = $rowSet["id_rang_communaute"];
        $this->view->user->rangCommunaute = null;
        $data = array(
            'id_fk_rang_communaute_braldun' => $this->view->user->id_fk_rang_communaute_braldun
        );
        $where = 'id_braldun = ' . $this->view->user->id_braldun;
        $braldunTable->update($data, $where);

        $message = "[Ceci est un message automatique de communauté]" . PHP_EOL;
        if ($nouveauGestionnaire['sexe_braldun'] == "feminin") {
            $message .= " Vous êtes devenue la nouvelle gestionnaire !" . PHP_EOL;
        } else {
            $message .= " Vous êtes devenu le nouveau gestionnaire de la communauté !" . PHP_EOL;
        }

        Bral_Util_Messagerie::envoiMessageAutomatique($this->view->user->id_braldun, $nouveauGestionnaire['id_braldun'], $message, $this->view);

        Zend_Loader::loadClass("TypeEvenementCommunaute");
        Zend_Loader::loadClass("Bral_Util_EvenementCommunaute");

        $details = "[b" . $nouveauGestionnaire['id_braldun'] . "]";
        $detailsBot = "[b" . $nouveauGestionnaire['id_braldun'] . "] est ";

        if ($nouveauGestionnaire['sexe_braldun'] == "feminin") {
            $detailsBot .= " devenue la nouvelle gestionnaire." . PHP_EOL;
        } else {
            $detailsBot .= " devenu le nouveau gestionnaire." . PHP_EOL;
        }

        $detailsBot .= PHP_EOL . "Action réalisée par [b" . $this->view->user->id_braldun . "]";
        Bral_Util_EvenementCommunaute::ajoutEvenements($this->view->user->id_fk_communaute_braldun, TypeEvenementCommunaute::ID_TYPE_GESTIONNAIRE, $details, $detailsBot, $this->view);

        $this->view->isUpdateGestionnaire = true;
    }

}
