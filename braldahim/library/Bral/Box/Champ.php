<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Champ extends Bral_Box_Box
{

    function getTitreOnglet()
    {
        return "Champ";
    }

    function getNomInterne()
    {
        return "box_lieu";
    }

    function setDisplay($display)
    {
        $this->view->display = $display;
    }

    function render()
    {
    }
}
