<?php

class ReferentielMonstre extends Zend_Db_Table {
	protected $_name = 'ref_monstre';
	protected $_primary = "id_ref_monstre";

	public function findAll() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('ref_monstre', '*')
		->from('type_monstre', '*')
		->from('taille_monstre', '*')
		->where('ref_monstre.id_fk_type_ref_monstre = type_monstre.id_type_monstre')
		->where('ref_monstre.id_fk_taille_ref_monstre = taille_monstre.id_taille_monstre');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}
}
