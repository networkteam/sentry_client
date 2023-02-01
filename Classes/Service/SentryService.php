<?php
declare(strict_types=1);
namespace Networkteam\SentryClient\Service;

use Networkteam\SentryClient\Integration\Typo3Integration;
use Networkteam\SentryClient\Integration\UserIntegration;
use Networkteam\SentryClient\SentryLogWriter;
use Sentry\Event;
use Sentry\Integration\EnvironmentIntegration;
use Sentry\Integration\ErrorListenerIntegration;
use Sentry\Integration\FatalErrorListenerIntegration;
use Sentry\Integration\FrameContextifierIntegration;
use Sentry\Integration\RequestIntegration;
use TYPO3\CMS\Core\Core\Environment;
use function Sentry\init;

class SentryService
{
    protected static ?bool $isEnabled = null;

    public static function inititalize(): bool
    {
        if (!is_null(self::$isEnabled)) {
            return self::$isEnabled;
        }

        $dsn = ConfigurationService::getDsn();
        if (!$dsn) {
            self::$isEnabled = false;
            return false;
        }

        $reportUserInformation = ConfigurationService::getReportUserInformation() !== ConfigurationService::USER_INFORMATION_NONE;

        $options = [
            'dsn' => ConfigurationService::getDsn(),
            'release' => ConfigurationService::getRelease(),
            'environment' => ConfigurationService::getEnvironment(),
            'in_app_include' => [Environment::getExtensionsPath()],
            'http_proxy' => $GLOBALS['TYPO3_CONF_VARS']['HTTP']['proxy'] ?? null,
            'attach_stacktrace' => true,
            'before_send' => function (Event $event): Event {
                return SentryLogWriter::cleanupStacktrace($event);
            },
            'default_integrations' => false,
            'error_types' => E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_USER_DEPRECATED
        ];

        $extConf = ConfigurationService::getExtConf() ?? [];
        $integrations = $extConf['integrations'] ?? [];
        unset($extConf['integrations']);

        $options = array_merge($options, $extConf);

        $integrations = array_merge([
            RequestIntegration::class => ['enabled' => true],
            FrameContextifierIntegration::class => ['enabled' => true],
            EnvironmentIntegration::class => ['enabled' => true],
            FatalErrorListenerIntegration::class => ['enabled' => true],
            ErrorListenerIntegration::class => ['enabled' => true],
            Typo3Integration::class => ['enabled' => true],
            UserIntegration::class => ['enabled' => $reportUserInformation]
        ], $integrations);

        foreach ($integrations as $className => $integrationConfiguration) {
            if (($integrationConfiguration['enabled'] ?? false)
                && class_exists($className)
            ) {
                $options['integrations'][] = new $className();
            }
        }

        init($options);
        self::$isEnabled = true;

        return true;
    }

    public static function isEnabled(): ?bool
    {
        return self::$isEnabled;
    }
}
