<?php

defined('TYPO3') or die();

if (\Networkteam\SentryClient\Service\SentryService::isEnabled()) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Frontend\ContentObject\Exception\ProductionExceptionHandler::class] = [
        'className' => \Networkteam\SentryClient\Content\ProductionExceptionHandler::class
    ];
}
