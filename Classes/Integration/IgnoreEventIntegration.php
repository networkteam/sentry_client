<?php

declare(strict_types=1);

namespace Networkteam\SentryClient\Integration;

use Doctrine\DBAL\Exception\ConnectionException;
use Networkteam\SentryClient\Service\ConfigurationService;
use Networkteam\SentryClient\Trait\IgnoreMessage;
use Sentry\Event;
use Sentry\Integration\IntegrationInterface;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use TYPO3\CMS\Core\Error\Http\BadRequestException;
use TYPO3\CMS\Core\Error\Http\PageNotFoundException;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Http\PropagateResponseException;

final class IgnoreEventIntegration implements IntegrationInterface
{
    use IgnoreMessage;

    public function setupOnce(): void
    {
        Scope::addGlobalEventProcessor(function (Event $event): ?Event {
            $currentHub = SentrySdk::getCurrentHub();
            $integration = $currentHub->getIntegration(self::class);
            $client = $currentHub->getClient();

            // The client bound to the current hub, if any, could not have this
            // integration enabled. If this is the case, bail out
            if (null === $integration || null === $client) {
                return $event;
            }

            return $this->processEvent($event);
        });
    }

    protected function processEvent(Event $event): ?Event
    {
        foreach ($event->getExceptions() as $exception) {
            if (in_array($exception->getType(), [
                    BadRequestException::class,
                    ImmediateResponseException::class,
                    PropagateResponseException::class,
                    PageNotFoundException::class
                ]) ||
                $this->shouldIgnoreMessage($exception->getValue()) ||
                (
                    !ConfigurationService::reportDatabaseConnectionErrors() &&
                    $exception->getType() === ConnectionException::class
                )
            ) {
                return null;
            }
        }

        return $event;
    }
}
