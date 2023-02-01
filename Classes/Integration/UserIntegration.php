<?php

declare(strict_types=1);

namespace Networkteam\SentryClient\Integration;

use Psr\Http\Message\ServerRequestInterface;
use Sentry\Event;
use Sentry\Integration\IntegrationInterface;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use Sentry\UserDataBag;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\IpAnonymizationUtility;

final class UserIntegration implements IntegrationInterface
{
    public function setupOnce(): void
    {
        Scope::addGlobalEventProcessor(function (Event $event): Event {
            $currentHub = SentrySdk::getCurrentHub();
            $integration = $currentHub->getIntegration(self::class);
            $client = $currentHub->getClient();

            // The client bound to the current hub, if any, could not have this
            // integration enabled. If this is the case, bail out
            if (null === $integration || null === $client) {
                return $event;
            }

            $this->processEvent($event);

            return $event;
        });
    }

    private function processEvent(Event $event): void
    {
        $userData = [];
        $ipAddress = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        if (!empty($ipAddress)) {
            $userData['ip_address'] = IpAnonymizationUtility::anonymizeIp($ipAddress);
        }

        try {
            $context = GeneralUtility::makeInstance(Context::class);
            if ($context->hasAspect('backend.user')) {
                $backendUserAspect = $context->getAspect('backend.user');
                if ($backendUserAspect->isLoggedIn()) {
                    $userData['Backend user'] = $backendUserAspect->get('id');
                }
            }
            if ($context->hasAspect('frontend.user')) {
                $frontendUserAspect = $context->getAspect('frontend.user');
                if ($frontendUserAspect->isLoggedIn()) {
                    $userData['Frontend user'] = $frontendUserAspect->get('id');
                }
            }
        } catch (\Throwable) {}

        $event->setUser(UserDataBag::createFromArray($userData));
    }

    protected function getServerRequest(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }
}
