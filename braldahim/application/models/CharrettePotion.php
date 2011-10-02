<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class CharrettePotion extends Zend_Db_Table
{
	protected $_name = 'charrette_potion';
	protected $_primary = array('id_charrette_potion');

	function findByIdConteneur($idCharrette)
	{
		return $this->findByIdCharrette($idCharrette);
	}

	function countByIdConteneur($idCharrette)
	{
		return $this->countByIdCharrette($idCharrette);
	}

	function findByIdCharrette($idCharrette, $idTypePotion = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_potion', '*')
			->from('type_potion')
			->from('type_qualite')
			->from('potion')
			->where('id_charrette_potion = id_potion')
			->where('id_fk_type_potion = id_type_potion')
			->where('id_fk_type_qualite_potion = id_type_qualite')
			->where('id_fk_charrette_potion = ?', intval($idCharrette));
		if ($idTypePotion != null) {
			$select->where('id_type_potion = ?', intval($idTypePotion));
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdBraldun($idBraldun, $idTypePotion = null, $idPotion = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_potion', '*')
			->from('type_potion')
			->from('type_qualite')
			->from('potion')
			->from('charrette')
			->where('id_charrette_potion = id_potion')
			->where('id_fk_type_potion = id_type_potion')
			->where('id_fk_type_qualite_potion = id_type_qualite')
			->where('id_fk_charrette_potion = id_charrette')
			->where('id_fk_braldun_charrette = ?', intval($idBraldun));
		if ($idTypePotion != null) {
			$select->where('id_type_potion = ?', intval($idTypePotion));
		}
		if ($idPotion != null) {
			$select->where('id_potion = ?', intval($idPotion));
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function countByIdCharrette($idCharrette)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('charrette_potion', 'count(*) as nombre')
			->where('id_fk_charrette_potion = ' . intval($idCharrette));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}
