<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Equipement extends Zend_Db_Table
{
	protected $_name = 'equipement';
	protected $_primary = array('id_equipement');

	function findByIdEquipement($idEquipement)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('equipement', '*')
			->where('id_equipement = ?', (int)$idEquipement);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByIdEquipementWithDetails($idEquipement)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('recette_equipements')
			->from('type_equipement')
			->from('type_qualite')
			->from('type_emplacement')
			->from('type_piece')
			->from('equipement', '*')
			->from('type_ingredient')
			->where('id_type_ingredient = id_fk_type_ingredient_base_type_equipement')
			->where('id_fk_recette_equipement = id_recette_equipement')
			->where('id_fk_type_recette_equipement = id_type_equipement')
			->where('id_fk_type_qualite_recette_equipement = id_type_qualite')
			->where('id_fk_type_emplacement_recette_equipement = id_type_emplacement')
			->where('id_fk_type_piece_type_equipement = id_type_piece')
			->joinLeft('mot_runique', 'id_fk_mot_runique_equipement = id_mot_runique');

		if ($idEquipement != "all") {
			$select->where('id_equipement = ?', intval($idEquipement));
		}
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByIdsEquipement($tabId)
	{
		$where = "";
		if ($tabId == null || count($tabId) == 0) {
			return null;
		}

		foreach ($tabId as $id) {
			if ($where == "") {
				$or = "";
			} else {
				$or = " OR ";
			}
			$where .= " $or id_equipement=" . (int)$id;
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('equipement', '*')
			->where($where);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
