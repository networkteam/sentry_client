<?php

declare(strict_types=1);

namespace Networkteam\SentryClient\Integration;

use Psr\Http\Message\ServerRequestInterface;
use Sentry\Event;
use Sentry\Integration\IntegrationInterface;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Information\Typo3Version;

final class Typo3Integration implements IntegrationInterface
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
        $request = $this->getServerRequest();
        if ($request) {
            if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
                $event->setTag('request_type', 'frontend');
            } elseif (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
                $event->setTag('request_type', 'backend');
            }
        }

        $requestId = $_SERVER['X-REQUEST-ID'] ?? $_SERVER['HTTP_X_REQUEST_ID'] ?? false;
        if ($requestId) {
            $event->setTag('request_id', $requestId);
        }

        $event->setTag('typo3_version', (new Typo3Version())->getVersion());
    }

    protected function getServerRequest(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }
}
