<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LabanMateriel extends Zend_Db_Table
{
	protected $_name = 'laban_materiel';
	protected $_primary = array('id_laban_materiel');

	function findByIdConteneur($id_braldun)
	{
		return $this->findByIdBraldun($id_braldun);
	}

	function findByIdBraldun($idBraldun, $idMateriel = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_materiel', '*')
			->from('type_materiel')
			->from('materiel', '*')
			->where('id_laban_materiel = id_materiel')
			->where('id_fk_type_materiel = id_type_materiel')
			->where('id_fk_braldun_laban_materiel = ?', intval($idBraldun));
		if ($idMateriel != null) {
			$select->where('id_materiel = ?', intval($idMateriel));
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}