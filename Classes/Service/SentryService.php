<?php
declare(strict_types=1);
namespace Networkteam\SentryClient\Service;

use Networkteam\SentryClient\Integration\IgnoreEventIntegration;
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

        $options = [
            'dsn' => $dsn,
            'release' => ConfigurationService::getRelease(),
            'environment' => ConfigurationService::getEnvironment(),
            'in_app_include' => [Environment::getExtensionsPath()],
            'http_proxy' => $GLOBALS['TYPO3_CONF_VARS']['HTTP']['proxy']['http'] ?? null,
            'attach_stacktrace' => true,
            'before_send' => function (Event $event): Event {
                return SentryLogWriter::cleanupStacktrace($event);
            },
            'prefixes' => [
                Environment::getProjectPath()
            ],
            'default_integrations' => false,
            'error_types' => E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_USER_DEPRECATED,
            'integrations' => [
                10 => IgnoreEventIntegration::class,
                20 => RequestIntegration::class,
                30 => FrameContextifierIntegration::class,
                40 => EnvironmentIntegration::class,
                50 => FatalErrorListenerIntegration::class,
                60 => ErrorListenerIntegration::class,
                70 => Typo3Integration::class
            ]
        ];

        if (ConfigurationService::getReportUserInformation() !== ConfigurationService::USER_INFORMATION_NONE) {
            $options['integrations'][80] = UserIntegration::class;
        }

        $extConf = ConfigurationService::getExtConf();
        if (is_array($extConf['options'] ?? null)) {
            $options = array_merge($options, $extConf['options']);
        }
        if (is_callable($extConf['modifyOptions'] ?? null)) {
            $options = call_user_func($extConf['modifyOptions'], $options);
        }

        $integrations = [];
        foreach ($options['integrations'] as $className) {
            $integrations[] = new $className();
        }
        $options['integrations'] = $integrations;

        init($options);
        self::$isEnabled = true;

        return true;
    }

    public static function isEnabled(): ?bool
    {
        return self::$isEnabled;
    }
}
