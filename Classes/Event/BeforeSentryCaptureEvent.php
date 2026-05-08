<?php

declare(strict_types=1);

namespace Networkteam\SentryClient\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Throwable;

/**
 * Event dispatched just before an exception is sent to Sentry.
 *
 * Listeners can use this event to:
 * - Modify the exception before it's sent.
 * - Add specific context/tags to Sentry scope for this exception.
 * - Prevent the exception from being sent entirely by calling stopPropagation().
 */
class BeforeSentryCaptureEvent implements StoppableEventInterface
{
    private bool $propagationStopped = false;
    private Throwable $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }

    public function setException(Throwable $exception): void
    {
        $this->exception = $exception;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}
