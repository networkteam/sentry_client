<?php

namespace Networkteam\SentryClient;

use Networkteam\SentryClient\Service\ConfigurationService;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Client extends \Raven_Client
{

    public function __construct()
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client'])) {
            $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sentry_client']);
            if (isset($configuration['dsn']) && $configuration['dsn'] != '') {
                parent::__construct($configuration['dsn']);
            }
        }
    }

    /**
     * Log an exception to sentry
     */
    public function captureException($exception, $culprit_or_options = null, $logger = null, $vars = null)
    {
        $production = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction();

        $this->tags_context(array(
            'typo3_version' => TYPO3_version,
            'typo3_mode' => TYPO3_MODE,
            'application_context' => $production === true ? 'Production' : 'Development',
        ));

        $reportUserInformation = ConfigurationService::getReportUserInformation();
        if ($reportUserInformation !== ConfigurationService::USER_INFORMATION_NONE) {
            $userContext = [];
            if (TYPO3_MODE === 'FE' && isset($GLOBALS['TSFE']->fe_user->user['username'])) {
                $userObject = $GLOBALS['TSFE']->fe_user->user;
            } elseif (isset($GLOBALS['BE_USER']->user['username'])) {
                $userObject = $GLOBALS['BE_USER']->user;
            }

            if ($userObject) {
                $userContext['userid'] = $userObject['uid'];
                if (ConfigurationService::getReportUserInformation() === ConfigurationService::USER_INFORMATION_USERNAMEEMAIL) {
                    $userContext['username'] = $userObject['username'];
                    if (isset($userContext['email'])) {
                        $userContext['email'] = $userObject['email'];
                    }
                }
                $this->user_context($userContext);
            }
        }

        return parent::captureException($exception, $culprit_or_options, $logger, $vars);
    }

    /**
     * Send the message over http to the sentry url given.
     *
     * Overwritten to use TYPO3 HTTP settings (Proxy, etc..)
     *
     * @param string $url URL of the Sentry instance to log to
     * @param array|string $data Associative array of data to log
     * @param array $headers Associative array of headers
     */
    protected function send_http($url, $data, $headers = array())
    {
        /** @var RequestFactory $requestFactory */
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $additionalOptions = [
            'headers' => $headers,
            'body' => $data
        ];
        $requestFactory->request($url, 'POST', $additionalOptions);
    }
}