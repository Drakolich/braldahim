<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class CharretteMateriel extends Zend_Db_Table
{
	protected $_name = 'charrette_materiel';
	protected $_primary = array('id_charrette_materiel');

	function findByIdConteneur($idCharrette)
	{
		return $this->findByIdCharrette($idCharrette);
	}

	function findByIdCharrette($idCharrette)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_materiel', '*')
			->from('type_materiel', '*')
			->from('materiel', '*')
			->where('id_charrette_materiel = id_materiel')
			->where('id_fk_type_materiel = id_type_materiel')
			->where('id_fk_charrette_materiel = ?', intval($idCharrette));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdBraldun($idBraldun, $idMateriel = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_materiel', '*')
			->from('type_materiel', '*')
			->from('materiel', '*')
			->from('charrette', '*')
			->where('id_charrette_materiel = id_materiel')
			->where('id_fk_type_materiel = id_type_materiel')
			->where('id_fk_charrette_materiel = id_charrette');
		if ($idMateriel != null) {
			$select->where('id_materiel = ?', intval($idMateriel));
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}