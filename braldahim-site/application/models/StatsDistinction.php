<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class StatsDistinction extends Zend_Db_Table
{
	protected $_name = 'stats_distinction';
	protected $_primary = array('id_stats_distinction');

	function findTop10($dateDebut, $dateFin)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', array('nom_braldun', 'prenom_braldun', 'id_braldun'));
		$select->from('stats_distinction', 'SUM(points_stats_distinction) as nombre');
		$select->where('id_fk_braldun_stats_distinction = id_braldun');
		$select->where('mois_stats_distinction >= ?', $dateDebut);
		$select->where('mois_stats_distinction < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom_braldun', 'prenom_braldun', 'id_braldun'));
		$select->limit(10, 0);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByFamille($dateDebut, $dateFin)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', null);
		$select->from('stats_distinction', 'SUM(points_stats_distinction) as nombre');
		$select->from('nom', 'nom');
		$select->where('id_fk_braldun_stats_distinction = id_braldun');
		$select->where('id_nom = id_fk_nom_initial_braldun');
		$select->where('mois_stats_distinction >= ?', $dateDebut);
		$select->where('mois_stats_distinction < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('nom'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findByNiveau($dateDebut, $dateFin)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_distinction', array('SUM(points_stats_distinction) as nombre', 'floor(niveau_braldun_stats_distinction/10) as niveau'));
		$select->where('mois_stats_distinction >= ?', $dateDebut);
		$select->where('mois_stats_distinction < ?', $dateFin);
		$select->order("niveau ASC");
		$select->group(array('niveau'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function findBySexe($dateDebut, $dateFin)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('braldun', 'sexe_braldun');
		$select->from('stats_distinction', 'SUM(points_stats_distinction) as nombre');
		$select->where('id_fk_braldun_stats_distinction = id_braldun');
		$select->where('mois_stats_distinction >= ?', $dateDebut);
		$select->where('mois_stats_distinction < ?', $dateFin);
		$select->order("nombre DESC");
		$select->group(array('sexe_braldun'));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}