<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Nom extends Zend_Db_Table
{
	protected $_name = 'nom';
	protected $_primary = 'id_nom';

	function fetchAllId()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('nom', 'id_nom');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findById($id)
	{
		$where = $this->getAdapter()->quoteInto('id_nom = ?', (int)$id);
		return $this->fetchRow($where);
	}
}
