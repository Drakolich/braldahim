<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
/**
 * Ecriture de Log.
 * Les logs sont paramétrés dans le fichier de configuration.
 */
class Bral_Util_Log
{
    private static $instance = null;

    private static $authentification = null;
    private static $attaque = null;
    private static $batchs = null;
    private static $config = null;
    private static $erreur = null;
    private static $exception = null;
    private static $inscription = null;
    private static $mail = null;
    private static $potion = null;
    private static $profiler = null;
    private static $quete = null;
    private static $soule = null;
    private static $tech = null;
    private static $tour = null;
    private static $viemonstres = null;

    const FICHIER_AUTHENTIFICATION = "bral_authentification.log";
    const FICHIER_ATTAQUE = "bral_attaque.log";
    const FICHIER_BATCHS = "bral_batchs.log";
    const FICHIER_ERREUR = "bral_erreur.log";
    const FICHIER_EXCEPTION = "bral_exception.log";
    const FICHIER_INSCRIPTION = "bral_inscription.log";
    const FICHIER_MAIL = "bral_mail.log";
    const FICHIER_POTION = "bral_potion.log";
    const FICHIER_PROFILER = "bral_profiler.log";
    const FICHIER_SOULE = "bral_soule.log";
    const FICHIER_QUETE = "bral_quete.log";
    const FICHIER_TECH = "bral_tech.log";
    const FICHIER_TOUR = "bral_tour.log";
    const FICHIER_VIEMONSTRES = "bral_viemonstres.log";

    public static function authentification()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        if (self::$authentification == null) {
            self::$authentification = self::initLog(self::$config->log->repertoire . self::FICHIER_AUTHENTIFICATION, self::$config->log->niveau->authentification);
        }
        return self::$authentification;
    }

    public static function attaque()
    {
        if (self::$attaque == null) {
            self::initLogAttaque();
        }
        return self::$attaque;
    }

    public static function batchs()
    {
        if (self::$batchs == null) {
            self::initLogBatchs();
        }
        return self::$batchs;
    }

    public static function erreur()
    {
        if (self::$erreur == null) {
            self::initLogErreur();
        }
        return self::$erreur;
    }

    public static function exception()
    {
        if (self::$exception == null) {
            self::initLogException();
        }
        return self::$exception;
    }

    public static function inscription()
    {
        if (self::$inscription == null) {
            self::initLogInscription();
        }
        return self::$inscription;
    }

    public static function mail()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        if (self::$mail == null) {
            self::$mail = self::initLog(self::$config->log->repertoire . self::FICHIER_MAIL, self::$config->log->niveau->mail);
        }
        return self::$mail;
    }

    public static function potion()
    {
        if (self::$potion == null) {
            self::initLogPotion();
        }
        return self::$potion;
    }

    public static function profiler()
    {
        if (self::$profiler == null) {
            self::initLogProfiler();
        }
        return self::$profiler;
    }

    public static function soule()
    {
        if (self::$soule == null) {
            self::initLogSoule();
        }
        return self::$soule;
    }

    public static function quete()
    {
        if (self::$quete == null) {
            self::initLogQuete();
        }
        return self::$quete;
    }

    public static function tech()
    {
        if (self::$tech == null) {
            self::initLogTech();
        }
        return self::$tech;
    }

    public static function tour()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        if (self::$tour == null) {
            self::$tour = self::initLog(self::$config->log->repertoire . self::FICHIER_TOUR, self::$config->log->niveau->tour);
        }
        return self::$tour;
    }

    public static function viemonstres()
    {
        if (self::$viemonstres == null) {
            self::initLogViemonstres();
        }
        return self::$viemonstres;
    }

    //______________
    private function __construct()
    {
    }

    private static function getInstance()
    {
        if (self::$instance == null) {
            Zend_Loader::loadClass('Zend_Log');
            Zend_Loader::loadClass('Zend_Log_Writer_Stream');
            self::$instance = new self();
            self::$config = Zend_Registry::get('config');
            return self::$instance;
        }
    }

    private static function initLogAttaque()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        self::$config = Zend_Registry::get('config');
        self::$attaque = new Zend_Log();
        $redacteur = new Zend_Log_Writer_Stream(self::$config->log->repertoire . self::FICHIER_ATTAQUE);
        self::$attaque->addWriter($redacteur);
        $filtre = new Zend_Log_Filter_Priority((int)self::$config->log->niveau->attaque);
        self::$attaque->addFilter($filtre);
        self::$attaque->addPriority('TRACE', 8);

        if (self::$config->log->general->debug_browser == "oui") {
            $redacteur = new Zend_Log_Writer_Stream('php://output');
            self::$attaque->addWriter($redacteur);
        }
    }

    private static function initLogBatchs()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        self::$config = Zend_Registry::get('config');
        self::$batchs = new Zend_Log();
        $redacteur = new Zend_Log_Writer_Stream(self::$config->log->repertoire . self::FICHIER_BATCHS);
        self::$batchs->addWriter($redacteur);
        $filtre = new Zend_Log_Filter_Priority((int)self::$config->log->niveau->batchs);
        self::$batchs->addFilter($filtre);
        self::$batchs->addPriority('TRACE', 8);

        if (self::$config->log->general->debug_browser == "oui") {
            $redacteur = new Zend_Log_Writer_Stream('php://output');
            self::$batchs->addWriter($redacteur);
        }
    }

    private static function initLogErreur()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        self::$config = Zend_Registry::get('config');
        self::$erreur = new Zend_Log();
        $redacteur = new Zend_Log_Writer_Stream(self::$config->log->repertoire . self::FICHIER_ERREUR);
        $formateur = new Zend_Log_Formatter_Simple(date("Y-m-d H:i:s") . ' ' . $_SERVER['REMOTE_ADDR'] . ' %message%' . PHP_EOL);
        $redacteur->setFormatter($formateur);
        self::$erreur->addWriter($redacteur);
        $filtre = new Zend_Log_Filter_Priority((int)self::$config->log->niveau->erreur);
        self::$erreur->addFilter($filtre);
        self::$erreur->addPriority('TRACE', 8);

        if (self::$config->log->general == "oui") {
            $redacteur = new Zend_Log_Writer_Stream('php://output');
            self::$erreur->addWriter($redacteur);
        }
    }

    private static function initLogException()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        self::$config = Zend_Registry::get('config');
        self::$exception = new Zend_Log();
        $redacteur = new Zend_Log_Writer_Stream(self::$config->log->repertoire . self::FICHIER_EXCEPTION, 'w');
        $formatTexte = "--------> " . date("Y-m-d H:i:s") . ' ' . $_SERVER['REMOTE_ADDR'];
        $formatTexte .= ' ' . $_SERVER['SERVER_NAME'] . ' ' . $_SERVER['REQUEST_METHOD'];
        $formatTexte .= ' ' . $_SERVER['REQUEST_URI'];
        $formatTexte .= ' ' . $_SERVER['HTTP_USER_AGENT'] . ' %message%' . PHP_EOL;
        $formateur = new Zend_Log_Formatter_Simple($formatTexte);
        $redacteur->setFormatter($formateur);
        self::$exception->addWriter($redacteur);
        $filtre = new Zend_Log_Filter_Priority((int)self::$config->log->niveau->erreur);
        self::$exception->addFilter($filtre);
        self::$exception->addPriority('TRACE', 8);
    }

    private static function initLogInscription()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        self::$config = Zend_Registry::get('config');
        self::$inscription = new Zend_Log();
        $redacteur = new Zend_Log_Writer_Stream(self::$config->log->repertoire . self::FICHIER_INSCRIPTION);
        $formateur = new Zend_Log_Formatter_Simple(date("Y-m-d H:i:s") . ' ' . $_SERVER['REMOTE_ADDR'] . ' %message%' . PHP_EOL);
        $redacteur->setFormatter($formateur);
        self::$inscription->addWriter($redacteur);
        $filtre = new Zend_Log_Filter_Priority((int)self::$config->log->niveau->inscription);
        self::$inscription->addFilter($filtre);
        self::$inscription->addPriority('TRACE', 8);

        if (self::$config->log->general->debug_browser == "oui") {
            $redacteur = new Zend_Log_Writer_Stream('php://output');
            self::$inscription->addWriter($redacteur);
        }
    }

    private static function initLog($fichier, $niveau)
    {
        self::$config = Zend_Registry::get('config');
        $logger = new Zend_Log();
        $redacteur = new Zend_Log_Writer_Stream($fichier);
        $formateur = new Zend_Log_Formatter_Simple(date("Y-m-d H:i:s") . ' ' . $_SERVER['REMOTE_ADDR'] . ' %message%' . PHP_EOL);
        $redacteur->setFormatter($formateur);
        $logger->addWriter($redacteur);
        $filtre = new Zend_Log_Filter_Priority((int)$niveau);
        $logger->addFilter($filtre);
        $logger->addPriority('TRACE', 8);

        if (self::$config->log->general->debug_browser == "oui") {
            $redacteur = new Zend_Log_Writer_Stream('php://output');
            $logger->addWriter($redacteur);
        }
        return $logger;
    }

    private static function initLogPotion()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        self::$config = Zend_Registry::get('config');
        self::$potion = new Zend_Log();
        $redacteur = new Zend_Log_Writer_Stream(self::$config->log->repertoire . self::FICHIER_POTION);
        self::$potion->addWriter($redacteur);
        $filtre = new Zend_Log_Filter_Priority((int)self::$config->log->niveau->potion);
        self::$potion->addFilter($filtre);
        self::$potion->addPriority('TRACE', 8);

        if (self::$config->log->general->debug_browser == "oui") {
            $redacteur = new Zend_Log_Writer_Stream('php://output');
            self::$potion->addWriter($redacteur);
        }
    }

    private static function initLogProfiler()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        self::$config = Zend_Registry::get('config');
        self::$profiler = new Zend_Log();
        $redacteur = new Zend_Log_Writer_Stream(self::$config->log->repertoire . self::FICHIER_PROFILER);
        self::$profiler->addWriter($redacteur);
        $filtre = new Zend_Log_Filter_Priority((int)self::$config->log->niveau->profiler);
        self::$profiler->addFilter($filtre);
        self::$profiler->addPriority('TRACE', 8);

        if (self::$config->log->general->debug_browser == "oui") {
            $redacteur = new Zend_Log_Writer_Stream('php://output');
            self::$profiler->addWriter($redacteur);
        }
    }

    private static function initLogQuete()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        self::$config = Zend_Registry::get('config');
        self::$quete = new Zend_Log();
        $redacteur = new Zend_Log_Writer_Stream(self::$config->log->repertoire . self::FICHIER_QUETE);
        self::$quete->addWriter($redacteur);
        $filtre = new Zend_Log_Filter_Priority((int)self::$config->log->niveau->quete);
        self::$quete->addFilter($filtre);
        self::$quete->addPriority('TRACE', 8);

        if (self::$config->log->general->debug_browser == "oui") {
            $redacteur = new Zend_Log_Writer_Stream('php://output');
            self::$quete->addWriter($redacteur);
        }
    }

    private static function initLogSoule()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        self::$config = Zend_Registry::get('config');
        self::$soule = new Zend_Log();
        $redacteur = new Zend_Log_Writer_Stream(self::$config->log->repertoire . self::FICHIER_SOULE);
        self::$soule->addWriter($redacteur);
        $filtre = new Zend_Log_Filter_Priority((int)self::$config->log->niveau->soule);
        self::$soule->addFilter($filtre);
        self::$soule->addPriority('TRACE', 8);

        if (self::$config->log->general->debug_browser == "oui") {
            $redacteur = new Zend_Log_Writer_Stream('php://output');
            self::$soule->addWriter($redacteur);
        }
    }

    private static function initLogTech()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        self::$config = Zend_Registry::get('config');
        self::$tech = new Zend_Log();
        $redacteur = new Zend_Log_Writer_Stream(self::$config->log->repertoire . self::FICHIER_TECH);
        $formatTexte = "--------> " . date("Y-m-d H:i:s") . ' ' . $_SERVER['REMOTE_ADDR'];
        $formatTexte .= ' ' . $_SERVER['SERVER_NAME'] . ' ' . $_SERVER['REQUEST_METHOD'];
        $formatTexte .= ' ' . $_SERVER['REQUEST_URI'];
        $formatTexte .= ' ' . $_SERVER['HTTP_USER_AGENT'] . ' %message%' . PHP_EOL;
        $formateur = new Zend_Log_Formatter_Simple($formatTexte);
        $redacteur->setFormatter($formateur);
        self::$tech->addWriter($redacteur);
        $filtre = new Zend_Log_Filter_Priority((int)self::$config->log->niveau->tech);
        self::$tech->addFilter($filtre);
        self::$tech->addPriority('TRACE', 8);

        if (self::$config->log->general->debug_browser == "oui") {
            $redacteur = new Zend_Log_Writer_Stream('php://output');
            self::$tech->addWriter($redacteur);
        }
    }

    private static function initLogViemonstres()
    {
        if (self::$instance == null) {
            $instance = self::getInstance();
        }
        self::$config = Zend_Registry::get('config');
        self::$viemonstres = new Zend_Log();
        $redacteur = new Zend_Log_Writer_Stream(self::$config->log->repertoire . self::FICHIER_VIEMONSTRES);
        self::$viemonstres->addWriter($redacteur);
        $filtre = new Zend_Log_Filter_Priority((int)self::$config->log->niveau->viemonstres);
        self::$viemonstres->addFilter($filtre);
        self::$viemonstres->addPriority('TRACE', 8);

        if (self::$config->log->general->debug_browser == "oui") {
            $redacteur = new Zend_Log_Writer_Stream('php://output');
            self::$tech->addWriter($redacteur);
        }
    }
}
