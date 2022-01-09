<?php

namespace Networkteam\SentryClient\EventListener;

use Networkteam\SentryClient\Client;
use Networkteam\SentryClient\Service\ConfigurationService;
use TYPO3\CMS\Backend\Backend\Event\SystemInformationToolbarCollectorEvent;
use TYPO3\CMS\Backend\Toolbar\Enumeration\InformationStatus;
use TYPO3\CMS\Core\Localization\LanguageService;

class SystemInformationToolbarCollectorEventListener
{
    public function __invoke(SystemInformationToolbarCollectorEvent $event): void
    {
        $isActive = Client::isInitialized();
        $label = $this->getLanguageService()->sL(
            'LLL:EXT:sentry_client/Resources/Private/Language/locallang_be.xlf:' . ($isActive ? 'systeminformation.active' : 'systeminformation.inactive')
        );

        $event->getToolbarItem()->addSystemInformation(
            'Sentry',
            $label,
            'tx-sentryclient-sentry-glyph-light',
            $isActive ? InformationStatus::STATUS_OK : InformationStatus::STATUS_ERROR
        );

        if ($isActive) {
            $release = ConfigurationService::getRelease();
            if (!empty($release)) {
                $event->getToolbarItem()->addSystemInformation(
                    'Sentry Release',
                    $release,
                    'tx-sentryclient-sentry-glyph-light'
                );
            }
        }
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}