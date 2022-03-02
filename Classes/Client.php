<?php
declare(strict_types=1);
namespace Networkteam\SentryClient;

use Networkteam\SentryClient\Service\ConfigurationService;
use Networkteam\SentryClient\Service\ExceptionBlacklistService;
use Sentry\Event;
use Sentry\EventId;
use Sentry\Severity;
use Sentry\Stacktrace;
use Sentry\State\Scope;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\IpAnonymizationUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use function Sentry\captureException;
use function Sentry\captureMessage;
use function Sentry\configureScope;
use function Sentry\init;

class Client implements SingletonInterface
{
    /**
     * @var bool
     */
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
            $options['in_app_include'] = [Environment::getExtensionsPath()];
            if (isset($GLOBALS['TYPO3_CONF_VARS']['HTTP']['proxy']) && $GLOBALS['TYPO3_CONF_VARS']['HTTP']['proxy'] !== '') {
                $options['http_proxy'] = $GLOBALS['TYPO3_CONF_VARS']['HTTP']['proxy'];
            }

            // Enrich LogWriter messages with stackstrace to have a better event grouping. Log messages often contain
            // dynamic variables like file names and thus they are not groupable by message.
            $options['attach_stacktrace'] = true;
            $options['before_send'] = function (Event $event): Event {
                return SentryLogWriter::cleanupStacktrace($event);
            };

            init($options);

            self::setUserContext();
            self::setTagsContext();
            self::$initialized = true;

            return true;
        }

        return false;
    }

    public static function captureException(\Throwable $exception): ?EventId
    {
        if (self::init() && ExceptionBlacklistService::shouldHandleException($exception)) {
            $eventId = captureException($exception);
            return $eventId;
        }

        return null;
    }

    protected static function setUserContext(): void
    {
        configureScope(
            function (Scope $scope): void {
                $userContext['ip_address'] = self::getIpAddress();
                $reportUserInformation = ConfigurationService::getReportUserInformation();
                if ($reportUserInformation !== ConfigurationService::USER_INFORMATION_NONE && isset($GLOBALS['TYPO3_REQUEST'])) {
                    $context = GeneralUtility::makeInstance(Context::class);
                    if (
                        class_exists(ApplicationType::class) && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()
                        || TYPO3_MODE === 'FE'
                    ) {
                        $frontendUserAspect = $context->getAspect('frontend.user');
                        if ($frontendUserAspect->isLoggedIn()) {
                            $userObject = $GLOBALS['TSFE']->fe_user->user;
                        }
                    } else {
                        $backendUserAspect = $context->getAspect('backend.user');
                        if ($backendUserAspect->isLoggedIn()) {
                            $userObject = $GLOBALS['BE_USER']->user;
                        }
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
            }
        );
    }

    protected static function setTagsContext(): void
    {
        configureScope(
            function (Scope $scope): void {
                if (isset($GLOBALS['TYPO3_REQUEST']) && class_exists(ApplicationType::class)) {
                    $mode = ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend() ? 'FE' : 'BE';
                } elseif (defined('TYPO3_MODE')) {
                    // deprecated in TYPO3 v11
                    $mode = TYPO3_MODE;
                }
                $scope->setTags(
                    [
                        'typo3_version' => GeneralUtility::makeInstance(Typo3Version::class)->getVersion(),
                        'typo3_mode' => $mode ?? '',
                    ]
                );
            }
        );
    }

    public static function captureMessage(string $message, string $loglevel = 'info'): ?EventId
    {
        return captureMessage($message, self::createSeverity($loglevel));
    }

    protected static function createSeverity(string $loglevel): Severity
    {
        switch ($loglevel) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
                $severityValue = Severity::FATAL;
                break;
            case LogLevel::ERROR:
                $severityValue = Severity::ERROR;
                break;
            case LogLevel::WARNING:
                $severityValue = Severity::WARNING;
                break;
            default:
                $severityValue = Severity::INFO;
        }
        return new Severity($severityValue);
    }

    public static function isInitialized(): bool
    {
        return self::$initialized;
    }

    protected static function getIpAddress(): string
    {
        $ipAddress = GeneralUtility::getIndpEnv('REMOTE_ADDR');

        if (!$ipAddress) {
            return '127.0.0.1';
        }

        $ipMask = ConfigurationService::getIpMask();
        return IpAnonymizationUtility::anonymizeIp($ipAddress, $ipMask);
    }
}
