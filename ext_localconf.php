<?php

defined('TYPO3') or die();

call_user_func(function() {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Frontend\ContentObject\Exception\ProductionExceptionHandler::class] = [
        'className' => \Networkteam\SentryClient\Content\ProductionExceptionHandler::class
    ];

    $sentryLogWriterLevel = \Networkteam\SentryClient\Service\ConfigurationService::getLogWriterLevel();
    if (!empty($sentryLogWriterLevel)) {
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'] = [
            $sentryLogWriterLevel => [
                \Networkteam\SentryClient\SentryLogWriter::class => [],
            ],
        ];
    }
});
