<?php

defined('TYPO3') or die();

call_user_func(function() {
    if (\Networkteam\SentryClient\Service\ConfigurationService::isDisabled()) {
        return;
    }

    if (!\TYPO3\CMS\Core\Core\Environment::isComposerMode()) {
        $autoloadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sentry_client') . 'vendor/autoload.php';
        require_once($autoloadFile);
    }

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

call_user_func(function() {
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
