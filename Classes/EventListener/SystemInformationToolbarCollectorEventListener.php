<?php

namespace Networkteam\SentryClient\EventListener;

use Networkteam\SentryClient\Service\ConfigurationService;
use Networkteam\SentryClient\Service\SentryService;
use TYPO3\CMS\Backend\Backend\Event\SystemInformationToolbarCollectorEvent;
use TYPO3\CMS\Backend\Toolbar\InformationStatus;
use TYPO3\CMS\Core\Localization\LanguageService;

class SystemInformationToolbarCollectorEventListener
{
    public function __invoke(SystemInformationToolbarCollectorEvent $event): void
    {
        $isActive = SentryService::isEnabled();
        $label = $this->getLanguageService()->sL(
            'LLL:EXT:sentry_client/Resources/Private/Language/locallang_be.xlf:systeminformation.' . ($isActive ? 'active' : 'inactive')
        );

        $event->getToolbarItem()->addSystemInformation(
            'Sentry',
            $label,
            'tx-sentryclient-sentry-glyph-light',
            $isActive ? InformationStatus::OK : InformationStatus::ERROR
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