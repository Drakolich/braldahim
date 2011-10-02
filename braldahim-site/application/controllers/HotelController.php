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
class HotelController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->config = Zend_Registry::get('config');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Bral_Util_ConvertDate');
	}

	function indexAction()
	{
		Zend_Loader::loadClass("Bral_Hotel_Factory");
		Zend_Loader::loadClass("Bral_Util_Poids");

		$box = Bral_Hotel_Factory::getBox($this->_request, $this->view);

		$this->view = $box->getPreparedView();

		$this->render();
	}
}