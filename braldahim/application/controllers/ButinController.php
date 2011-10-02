<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class ButinController extends Bral_Controller_Action
{

	function askactionAction()
	{
		$this->doactionAction();
	}

	public function doactionAction()
	{
		Zend_Loader :: loadClass("Bral_Butin_Factory");
		$this->doBralAction("Bral_Butin_Factory");
	}
}
