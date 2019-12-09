<?php
declare(strict_types = 1);
namespace Networkteam\SentryClient;

use Networkteam\SentryClient\Service\ConfigurationService;
use Networkteam\SentryClient\Service\ExceptionBlacklistService;
use Sentry\State\Scope;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use function Sentry\captureException;
use function Sentry\configureScope;
use function Sentry\init;

class Client implements SingletonInterface
{
    /**
     * Log an exception to sentry
     */
    public static function captureException(\Throwable $exception)
    {
        $dsn = ConfigurationService::getDsn();
        if (!empty($dsn) && ExceptionBlacklistService::shouldHandleException($exception)) {

            $options['dsn'] = $dsn;
            $options['release'] = ConfigurationService::getRelease();
            $options['environment'] = ConfigurationService::getEnvironment();
            $options['error_types'] = E_ALL ^ E_NOTICE;
            $options['project_root'] = ConfigurationService::getProjectRoot();
            init($options);

            self::setUserContext();
            self::setTagsContext();
            captureException($exception);
        }
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
                        if (isset($userContext['email'])) {
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
}
