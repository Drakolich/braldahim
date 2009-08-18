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
class Bral_Helper_DetailEquipement {

 	public static function afficherPrix($e) {
    	Zend_Loader::loadClass("Bral_Helper_DetailPrix");
 		return Bral_Helper_DetailPrix::afficherPrix($e, "_echoppe_equipement");
    }

	public static function afficher($e) {
		//return "<span ".self::afficherJs($e).">".htmlspecialchars($e["nom"]).", n&deg;".$e["id_equipement"]."</span>";
		$retour = "<span ".self::afficherJs($e).">";
		$retour .= "<img src='/public/styles/braldahim_defaut/images/type_equipement/type_equipement_".$e["id_type_equipement"].".png' alt=\"".htmlspecialchars($e["nom"])."\"/>";
		$retour .= "</span>";
		return $retour;
	}

	public static function afficherJs($e) {
		$text = htmlspecialchars($e["nom"])." ".htmlspecialchars(addslashes($e["suffixe"]));
		$text .= " de qualit&eacute; ".htmlspecialchars($e["qualite"])." <br /><br />";
		$text .= "Num&eacute;ro de la pi&egrave;ce :".$e["id_equipement"]."<br />";
		$text .= "Niveau : ".$e["niveau"]."<br />";
		$text .= "Nom d\'origine : ".$e["nom_standard"]."<br />";
		$text .= "Emplacement : ".$e["emplacement"]."<br />";
		$text .= "&Eacute;tat : ".$e["etat_courant"]." / ".$e["etat_initial"]."<br />";
		$text .= "Ingr&eacute;dient de base : ".$e["ingredient"]."<br /><br />";
			
		$text .= "Caract&eacute;ristiques :<br />";
		$text .= self::displayBM("Armure", $e, "armure");
		$text .= self::displayBM("Force", $e, "force");
		$text .= self::displayBM("Agilit&eacute;", $e, "agilite");
		$text .= self::displayBM("Vigueur", $e, "vigueur");
		$text .= self::displayBM("Sagesse", $e, "sagesse");
		$text .= self::displayBM("Vue", $e, "vue");
		$text .= self::displayBM("BM Attaque", $e, "bm_attaque");
		$text .= self::displayBM("BM Defense", $e, "bm_defense");
		$text .= self::displayBM("BM D&eacute;g&acirc;ts", $e, "bm_degat");
		$text .= "Poids : ".$e["poids"];
		if (isset($e["bonus"]["vernis_bm_poids_equipement_bonus"])) {
			$text .= self::display("",$e["bonus"]["vernis_bm_poids_equipement_bonus"]);
		}
		$text .= " Kg";
		$text .= "<br>";
			
		if (count($e["bonus"]) > 0 && (!array_key_exists("nom_systeme_type_piece", $e) || $e["nom_systeme_type_piece"] != "munition")) {
			$text .= " Bonus r&eacute;gional: ";
			$text .= self::display("Armure", $e["bonus"]["armure_equipement_bonus"], "");
			$text .= self::display("Force", $e["bonus"]["force_equipement_bonus"], "");
			$text .= self::display("Agilit&eacute;", $e["bonus"]["agilite_equipement_bonus"], "");
			$text .= self::display("Vigueur", $e["bonus"]["vigueur_equipement_bonus"], "");
			$text .= self::display("Sagesse", $e["bonus"]["sagesse_equipement_bonus"], "");
		}

		if (!array_key_exists("nom_systeme_type_piece", $e) || $e["nom_systeme_type_piece"] != "munition") {
			$text .= "<br />Nombre d\'emplacement runique : ".$e["nb_runes"]."<br />";
			if (count($e["runes"]) > 1) $s='s'; else $s="";


			$text .= count($e["runes"]) ." rune$s sertie$s "."<br />";
			if (count($e["runes"]) > 0) {
				foreach($e["runes"] as $r) {
					$text .= "<img src=\'/public/images/runes/".$r["image_type_rune"]."\'  class=\'rune\' title=\'".$r["nom_type_rune"]." :".str_replace("'", "&#180;", htmlspecialchars(addslashes($r["effet_type_rune"])))."\' n&deg;".$r["id_rune_equipement_rune"]." alt=\'".$r["nom_type_rune"]."\' n&deg;".$r["id_rune_equipement_rune"]."  />";
				}
				if ($e["suffixe"] != null && $e["suffixe"] != "") {
					$text .= "<br />Mot runique associ&eacute; &agrave; ces runes : ".htmlspecialchars(addslashes($e["suffixe"]));
				} else {
					$text .= "<br />Aucun mot runique n\'est associ&eacute; &agrave; ces runes";
				}
			}
		}

		$text .= "<br />";
		Zend_Loader::loadClass("Bral_Helper_Tooltip");
		return Bral_Helper_Tooltip::jsTip($text);
	}

	private static function displayBM($texte, $e, $bm) {
		$vernisBM = null;
		if (isset($e["bonus"]["vernis_bm_".$bm."_equipement_bonus"])) {
			$vernisBM = $e["bonus"]["vernis_bm_".$bm."_equipement_bonus"];
		}
		$valeur = $e[$bm];
		$text = null;
		if ($valeur != null && $valeur != 0) {
			$text = self::display($texte, $valeur, null, "");
			$text .= self::display("", $vernisBM, " (vernis)", "", true, " ");
			$text .= "<br>";
		} else if ($vernisBM != null) {
			$text = self::display($texte, $vernisBM, " (vernis)", "", true);
			$text .= "<br>";
		}
		return $text;
	}

	private static function display($display, $valeur, $unite = "", $br = "<br />", $bmVernis = false, $deuxPoints = " : ") {
		if ($valeur != null && (($bmVernis == false && $valeur != 0) || ($bmVernis == true)) ) {
			$plus = "";
			if ($valeur >= 0) {
				$plus = "+";
			}
			return $display .$deuxPoints.$plus.$valeur . $unite . $br;
		} else {
			return null;
		}
	}

	/**
	 * Affiche les recettes pour forger, fabriquer,
	 */
	public static function afficheRecette($caracs, $niveaux) {
		$retour = "";
		$retour .= "<div id='blanc'><br><br><br><br><br><br><br><br><br></div>";
		if (isset($caracs)) {
			foreach($niveaux as $k => $v) {
				$retour .= "<div id='caracs_niveau_".$k."' style='display:none'>";
				$retour .= "<table>";
				$retour .= "<th>Qual.</th>";
				$retour .= "<th>Empl.</th>";
				$retour .= "<th>Niv.</th>";
				$retour .= "<th>Poids</th>";
				$retour .= "<th>ARM</th>";
				$retour .= "<th>FOR</th>";
				$retour .= "<th>AGI</th>";
				$retour .= "<th>VIG</th>";
				$retour .= "<th>SAG</th>";
				$retour .= "<th>VUE</th>";
				$retour .= "<th>BM ATT</th>";
				$retour .= "<th>BM DEG</th>";
				$retour .= "<th>BM DEF</th>";
				if (array_key_exists($k, $caracs)) {
					foreach($caracs[$k] as $key => $val) {
						foreach($val as $c) {
							$retour .= "<tr>";
							$retour .= "<td>".$c["nom_qualite"]." </td>";
							$retour .= "<td>".$c["nom_emplacement"]." </td>";
							$retour .= "<td>".$c["niveau"]." </td>";
							$retour .= "<td>".$c["poids"]." </td>";
							$retour .= "<td>".$c["armure"]." </td>";
							$retour .= "<td>".$c["force"]." </td>";
							$retour .= "<td>".$c["agilite"]." </td>";
							$retour .= "<td>".$c["vigueur"]." </td>";
							$retour .= "<td>".$c["sagesse"]." </td>";
							$retour .= "<td>".$c["vue"]." </td>";
							$retour .= "<td>".$c["bm_attaque"]."</td>";
							$retour .= "<td>".$c["bm_degat"]." </td>";
							$retour .= "<td>".$c["bm_defense"]." </td>";
							$retour .= "</tr>";
						}
					}
				}
				$retour .= "</table>";
				$retour .= "</div>";
			}
		}

		return $retour;
	}

	public static function afficheRecetteJs($niveaux) {
		$retour = "
	 	$('blanc').style.display='none';
		 for (i=0; i<".count($niveaux)."; i++) {
		 	$('caracs_niveau_'+i).style.display='none';
		 }
		 $('caracs_niveau_'+this.value).style.display = 'block';
	 ";
		return $retour;
	}
}
