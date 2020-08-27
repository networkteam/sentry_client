<?php
declare(strict_types=1);
namespace Networkteam\SentryClient;

use Networkteam\SentryClient\Service\ConfigurationService;
use Networkteam\SentryClient\Service\ExceptionBlacklistService;
use Sentry\Severity;
use Sentry\State\Scope;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use function Sentry\captureException;
use function Sentry\captureMessage;
use function Sentry\configureScope;
use function Sentry\init;

class Client implements SingletonInterface
{
    protected static $initialized = false;

    public static function init(): bool
    {
        if (self::$initialized) {
            return true;
        }

        $dsn = ConfigurationService::getDsn();
        if (!empty($dsn)) {
            $options['dsn'] = $dsn;
            if (ConfigurationService::getRelease()) {
                $options['release'] = ConfigurationService::getRelease();
            }
            $options['environment'] = ConfigurationService::getEnvironment();
            $options['error_types'] = E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_USER_DEPRECATED;
            $options['project_root'] = ConfigurationService::getProjectRoot();
            init($options);

            self::setUserContext();
            self::setTagsContext();
            self::$initialized = true;

            return true;
        }

        return false;
    }

    /**
     * Log an exception to sentry
     */
    public static function captureException(\Throwable $exception): ?string
    {
        if (self::init() && ExceptionBlacklistService::shouldHandleException($exception)) {
            $eventId = captureException($exception);
            return $eventId;
        }

        return null;
    }

    protected static function setUserContext(): void
    {
        configureScope(function (Scope $scope): void {
            $userContext['ip_address'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
            $reportUserInformation = ConfigurationService::getReportUserInformation();
            if ($reportUserInformation !== ConfigurationService::USER_INFORMATION_NONE) {
                if (TYPO3_MODE === 'FE' && isset($GLOBALS['TSFE']->fe_user->user['username'])) {
                    $userObject = $GLOBALS['TSFE']->fe_user->user;
                } elseif (isset($GLOBALS['BE_USER']->user['username'])) {
                    $userObject = $GLOBALS['BE_USER']->user;
                }

                if (isset($userObject)) {
                    $userContext['id'] = $userObject['uid'];
                    if (ConfigurationService::getReportUserInformation() === ConfigurationService::USER_INFORMATION_USERNAMEEMAIL) {
                        $userContext['username'] = $userObject['username'];
                        if (isset($userObject['email'])) {
                            $userContext['email'] = $userObject['email'];
                        }
                    }
                }
            }
            $scope->setUser($userContext);
        });
    }

    protected static function setTagsContext(): void
    {
        configureScope(function (Scope $scope): void {
            $scope->setTags([
                'typo3_version' => TYPO3_version,
                'typo3_mode' => TYPO3_MODE
            ]);
        });
    }

    public static function captureMessage($message, $loglevel)
    {
        captureMessage($message, self::createSeverity($loglevel));
    }

    protected static function createSeverity($loglevel): Severity
    {
        switch ($loglevel) {
            case LogLevel::EMERGENCY:
                $severityValue = Severity::FATAL;
                break;
            case LogLevel::ALERT:
                $severityValue = Severity::FATAL;
                break;
            case LogLevel::CRITICAL:
                $severityValue = Severity::FATAL;
                break;
            case LogLevel::ERROR:
                $severityValue = Severity::ERROR;
                break;
            case LogLevel::WARNING:
                $severityValue = Severity::WARNING;
                break;
            case LogLevel::INFO:
                $severityValue = Severity::INFO;
                break;
            case LogLevel::NOTICE:
                $severityValue = Severity::INFO;
        }
        return new Severity($severityValue);
    }
}
