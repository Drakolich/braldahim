<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class ElementAliment extends Zend_Db_Table
{
	protected $_name = 'element_aliment';
	protected $_primary = array('id_element_aliment');

	function selectVue($x_min, $y_min, $x_max, $y_max, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element_aliment', '*')
			->from('type_aliment')
			->from('type_qualite')
			->from('aliment')
			->where('id_aliment = id_element_aliment')
			->where('id_fk_type_aliment = id_type_aliment')
			->where('id_fk_type_qualite_aliment = id_type_qualite')
			->where('x_element_aliment <= ?', $x_max)
			->where('x_element_aliment >= ?', $x_min)
			->where('y_element_aliment <= ?', $y_max)
			->where('y_element_aliment >= ?', $y_min)
			->where('z_element_aliment = ?', $z);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z)
	{
		return $this->selectVue($x, $y, $x, $y, $z);
	}
}
