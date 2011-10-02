<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Scripts_Laban extends Bral_Scripts_Conteneur
{

	public function calculScriptImpl()
	{
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Laban - calculScriptImpl - enter -");

		$retour = null;
		$this->calculConteneur("Laban", $retour);

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Laban - calculScriptImpl - exit -");
		return $retour;
	}
}