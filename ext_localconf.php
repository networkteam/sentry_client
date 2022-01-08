<?php

if (!defined('TYPO3_MODE') && !defined('TYPO3')) {
    die('Access denied.');
}

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

    if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getNumericTypo3Version(), '11', '<')) {
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconRegistry::class
        );
        $iconRegistry->registerIcon(
            'tx-sentryclient-sentry-glyph-light',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:sentry_client/Resources/Public/Icons/sentry-glyph-light.svg']
        );
    }
});
