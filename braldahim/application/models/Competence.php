<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Competence extends Zend_Db_Table
{
	protected $_name = 'competence';
	protected $_primary = 'id_competence';
	protected $_dependentTables = array('bralduns_competences');

	public function findBasiques()
	{
		$where = $this->getAdapter()->quoteInto("type_competence = ?", "basic");
		return $this->fetchAll($where);
	}

	public function findCommunesInscription($niveau)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('competence', '*')
			->where('type_competence = ?', "commun")
			->where('niveau_requis_competence = 0');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findCommunesByNiveauAndNiveauSagesse($niveau, $niveauSagesse)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('competence', '*')
			->where('type_competence = ?', "commun")
			->where('niveau_requis_competence <= ?', $niveau)
			->where('niveau_sagesse_requis_competence <= ?', $niveauSagesse);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	public function findByIdMetier($idMetier)
	{
		$where = $this->getAdapter()->quoteInto("id_fk_metier_competence = ?", $idMetier);
		return $this->fetchAll($where);
	}

	function findAll()
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('competence', '*')
			->joinLeft('metier', 'id_fk_metier_competence = id_metier')
			->order('id_competence ASC');
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}