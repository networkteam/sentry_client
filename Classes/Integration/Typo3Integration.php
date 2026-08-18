<?php

declare(strict_types=1);

namespace Networkteam\SentryClient\Integration;

use Psr\Http\Message\ServerRequestInterface;
use Sentry\Event;
use Sentry\Integration\IntegrationInterface;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
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
        if ($request instanceof ServerRequestInterface) {
            $this->setUrl($event, $request);
            $event->setTag('request_type', $this->getRequestType($request));
        } else {
            $event->setTag('request_type', 'cli');
        }

        $requestId = $_SERVER['X-REQUEST-ID'] ?? $_SERVER['HTTP_X_REQUEST_ID'] ?? false;
        if ($requestId) {
            $event->setTag('request_id', $requestId);
        }

        $event->setTag('typo3_version', (new Typo3Version())->getVersion());
    }

    protected function setUrl(Event $event, ServerRequestInterface $request): void
    {
        $requestData = $event->getRequest();
        $requestData['url'] = $request->getUri()->__toString();
        $event->setRequest($requestData);
    }

    protected function getServerRequest(): ?ServerRequestInterface
    {
        if (Environment::isCli()) {
            return null;
        }
        return $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
    }

    private function getRequestType(ServerRequestInterface $request): string
    {
        try {
            return ApplicationType::fromRequest($request)->value;
        } catch (\RuntimeException $e) {
            // ignore missing application type
            return 'request';
        }
    }
}
