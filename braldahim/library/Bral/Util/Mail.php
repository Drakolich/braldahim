<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Mail
{
	public static function getNewZendMail()
	{
		Zend_Loader::loadClass("Zend_Mail");

		$c = Zend_Registry::get('config');
		if ($c->general->mail->use_smtp_server == '1') {
			Zend_Loader::loadClass("Zend_Mail_Transport_Smtp");
			$transport = new Zend_Mail_Transport_Smtp($c->general->mail->smtp_server);
			Zend_Mail::setDefaultTransport($transport);
		}

		return new Zend_Mail("UTF-8");
	}

	public static function envoiMailAutomatique($braldun, $titre, $message, $view)
	{
		$c = Zend_Registry::get('config');

		if ($c->mail->envoi->automatique->actif == true) {
			$view->mailUrlJeu = $c->general->url;
			$view->mailAdresseSupport = $c->general->adresseSupport;
			$view->mailMessage = $message;
			$view->mailBraldun = $braldun;

			$contenuText = $view->render("messagerie/mailText.phtml");
			$contenuHtml = $view->render("messagerie/mailHtml.phtml");

			$mail = Bral_Util_Mail::getNewZendMail();
			$mail->setFrom($c->general->mail->from_email, $c->general->mail->from_nom);
			$mail->addTo($braldun["email_braldun"], $braldun["prenom_braldun"] . " " . $braldun["nom_braldun"]);
			$mail->setSubject($titre);
			$mail->setBodyText($contenuText);
			if ($c->general->envoi_mail_html == true) {
				$mail->setBodyHtml($contenuHtml);
			}
			$mail->send();
			Bral_Util_Log::mail()->trace("Bral_Util_Mail - envoiMailAutomatique -" . $braldun["email_braldun"] . " " . $braldun["prenom_braldun"] . " " . $braldun["nom_braldun"] . " - " . $titre);
		}
	}
}