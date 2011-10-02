<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Util
{

	public static function marqueAJouer($x, $y)
	{
		self::marqueMonstresAJouer($x, $y);
		self::marqueGroupesAJouer($x, $y);
	}

	private static function marqueMonstresAJouer($x, $y)
	{
		Zend_Loader::loadClass('Monstre');

		$config = Zend_Registry::get('config');

		$monstreTable = new Monstre();
		$data = array('date_a_jouer_monstre' => date("Y-m-d H:i:s"));

		$xMin = $x - $config->game->monstre->ajouer->nbcases;
		$xMax = $x + $config->game->monstre->ajouer->nbcases;
		$yMin = $y - $config->game->monstre->ajouer->nbcases;
		$yMax = $y + $config->game->monstre->ajouer->nbcases;

		$where = 'x_monstre >= ' . $xMin;
		$where .= ' AND x_monstre <= ' . $xMax;
		$where .= ' AND y_monstre >= ' . $yMin;
		$where .= ' AND y_monstre <= ' . $yMax;
		$where .= ' AND id_fk_groupe_monstre is NULL';
		$where .= ' AND date_a_jouer_monstre is NULL';

		$monstreTable->update($data, $where);
	}

	private static function marqueGroupesAJouer($x, $y)
	{
		Zend_Loader::loadClass('GroupeMonstre');

		$config = Zend_Registry::get('config');

		$groupeMonstreTable = new GroupeMonstre();
		$data = array('date_a_jouer_groupe_monstre' => date("Y-m-d H:i:s"));

		$xMin = $x - $config->game->monstre->ajouer->nbcases;
		$xMax = $x + $config->game->monstre->ajouer->nbcases;
		$yMin = $y - $config->game->monstre->ajouer->nbcases;
		$yMax = $y + $config->game->monstre->ajouer->nbcases;

		$where = 'x_direction_groupe_monstre >= ' . $xMin;
		$where .= ' AND x_direction_groupe_monstre <= ' . $xMax;
		$where .= ' AND y_direction_groupe_monstre >= ' . $yMin;
		$where .= ' AND y_direction_groupe_monstre <= ' . $yMax;
		$where .= ' AND date_a_jouer_groupe_monstre is NULL';

		$groupeMonstreTable->update($data, $where);
	}
}