<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Blabla extends Zend_Db_Table
{

	protected $_name = 'blabla';
	protected $_primary = array('id_blabla');

	public function countByPositionAndDate($x, $y, $z, $nbCases, $date, $withoutIdBraldun)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('blabla', 'count(id_blabla) as nombre');
		$select->where('x_blabla >= ?', intval($x) - $nbCases);
		$select->where('x_blabla <= ?', intval($x) + $nbCases);
		$select->where('y_blabla >= ?', intval($y) - $nbCases);
		$select->where('y_blabla <= ?', intval($y) + $nbCases);
		$select->where('z_blabla = ?', intval($z));
		$select->where('est_censure_blabla = ?', 'non');
		$select->where('date_blabla > ?', $date);
		$select->where('id_braldun <> ?', $withoutIdBraldun);
		$select->order('id_blabla desc');
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		$nombre = $result[0]["nombre"];
		return $nombre;
	}

	public function findByPosition($x, $y, $z, $nbCases)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('blabla', '*');
		$select->from('braldun', '*');
		$select->where('id_fk_braldun_blabla = id_braldun');
		$select->where('x_blabla >= ?', intval($x) - $nbCases);
		$select->where('x_blabla <= ?', intval($x) + $nbCases);
		$select->where('y_blabla >= ?', intval($y) - $nbCases);
		$select->where('y_blabla <= ?', intval($y) + $nbCases);
		$select->where('z_blabla = ?', intval($z));
		$select->where('est_censure_blabla = ?', 'non');
		$select->order('id_blabla desc');
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}

	function selectAll()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('blabla', '*');
		$select->from('braldun', '*');
		$select->where('id_fk_braldun_blabla = id_braldun');
		$select->order('id_blabla desc');
		$sql = $select->__toString();
		$result = $db->fetchAll($sql);
		return $result;
	}

	public function findById($id)
	{
		$where = $this->getAdapter()->quoteInto('id_blabla = ?', (int)$id);
		return $this->fetchRow($where);
	}
}
