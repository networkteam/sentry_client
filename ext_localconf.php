<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (!\TYPO3\CMS\Core\Core\Environment::isComposerMode()) {
    $autoloadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sentry_client') . 'vendor/autoload.php';
    require_once($autoloadFile);
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Frontend\ContentObject\Exception\ProductionExceptionHandler::class] = [
    'className' => \Networkteam\SentryClient\Content\ProductionExceptionHandler::class
];
