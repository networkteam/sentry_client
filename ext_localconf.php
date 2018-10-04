<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (!function_exists('register_client')) {
    function register_client()
    {
        if (!class_exists(\Networkteam\SentryClient\Service\ConfigurationService::class)) {
            return;
        }

        if (!\Networkteam\SentryClient\Service\ConfigurationService::registerClient()) {
            return;
        }

        $autoloadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sentry_client') . 'vendor/autoload.php';
        if (file_exists($autoloadFile)) {
            require_once($autoloadFile);
        }

        \Raven_Autoloader::register();
        $GLOBALS['USER']['sentryClient'] = new \Networkteam\SentryClient\Client();
        $errorHandler = new Raven_ErrorHandler($GLOBALS['USER']['sentryClient'], true);
        $errorHandler->registerExceptionHandler();
        $errorHandler->registerShutdownFunction();

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\ContentObject\\Exception\\ProductionExceptionHandler'] = [
            'className' => 'Networkteam\\SentryClient\\Content\\ProductionExceptionHandler'
        ];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler'] = 'Networkteam\\SentryClient\\DebugExceptionHandler';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler'] = 'Networkteam\\SentryClient\\ProductionExceptionHandler';
    }
}

register_client();
