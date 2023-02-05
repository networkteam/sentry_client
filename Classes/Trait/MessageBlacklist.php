<?php
declare(strict_types=1);
namespace Networkteam\SentryClient\Trait;

use Networkteam\SentryClient\Service\ConfigurationService;

trait MessageBlacklist
{
    protected function isMessageBlacklisted(string $message): bool
    {
        $regex = ConfigurationService::getMessageBlacklistRegex();
        if (!empty($regex) && !empty($message)) {
            return preg_match($regex, $message) === 1;
        }

        return false;
    }
}