<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class ZoneNid extends Zend_Db_Table
{
	protected $_name = 'zone_nid';
	protected $_primary = 'id_zone_nid';

	function findZonesHorsVille()
	{
		return $this->findZonesNids('non');
	}

	function findZonesVille()
	{
		return $this->findZonesNids('oui');
	}

	function findZonesByIdDonjon($idDonjon)
	{
		return $this->findZonesNids(null, $idDonjon);
	}

	public function findByIdList($listId)
	{
		return $this->findByList("id_zone_nid", $listId);
	}

	private function findByList($nomChamp, $listId)
	{
		$liste = "";
		if (count($listId) < 1) {
			$liste = "";
		} else {
			foreach ($listId as $id) {
				if ((int)$id . "" == $id . "") {
					if ($liste == "") {
						$liste = $id;
					} else {
						$liste = $liste . " OR " . $nomChamp . "=" . $id;
					}
				}
			}
		}

		if ($liste != "") {
			$db = $this->getAdapter();
			$select = $db->select();
			$select->from('zone_nid', '*')
				->where($nomChamp . '=' . $liste);
			$sql = $select->__toString();
			return $db->fetchAll($sql);
		} else {
			return null;
		}
	}

	function findById($idZoneNid)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('zone_nid', '*')
			->where('id_zone_nid = ?', intval($idZoneNid));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findZonesNids($estVille = null, $idDonjon = null, $estMine = 'non')
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('zone_nid', '*');

		if ($estVille != null) {
			$select->where('est_ville_zone_nid = ?', $estVille);
		}

		if ($idDonjon != null) {
			$select->where('id_fk_donjon_zone_nid = ?', $idDonjon);
		} else {
			$select->where('id_fk_donjon_zone_nid is NULL');
		}

		$select->where('est_mine_zone_nid like ?', $estMine);

		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('zone_nid', '*')
			->where('x_min_zone_nid <= ?', $x)
			->where('x_max_zone_nid >= ?', $x)
			->where('y_min_zone_nid <= ?', $y)
			->where('y_max_zone_nid >= ?', $y)
			->where('z_zone_nid = ?', $z);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}