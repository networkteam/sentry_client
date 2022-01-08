<?php

namespace Networkteam\SentryClient\EventListener;

use Networkteam\SentryClient\Client;
use Networkteam\SentryClient\Service\ConfigurationService;
use TYPO3\CMS\Backend\Backend\Event\SystemInformationToolbarCollectorEvent;
use TYPO3\CMS\Backend\Toolbar\Enumeration\InformationStatus;
use TYPO3\CMS\Core\Localization\LanguageService;

class SystemInformationToolbarCollectorEventListener
{
    /**
     * @var LanguageService
     */
    protected $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function __invoke(SystemInformationToolbarCollectorEvent $event): void
    {
        $isActive = Client::isInitialized();
        $label = $this->languageService->sL(
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
}