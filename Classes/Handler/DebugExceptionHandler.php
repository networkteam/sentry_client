<?php
namespace Lemming\SentryClient\Handler;

class DebugExceptionHandler extends \TYPO3\CMS\Core\Error\DebugExceptionHandler {

	/**
	 * @var \Lemming\SentryClient\Client
	 */
	protected $client;

	public function __construct() {
		$this->client = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Lemming\\SentryClient\\Client');
		$errorHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Raven_ErrorHandler',$this->client, TRUE);
		$errorHandler->registerShutdownFunction();
		parent::__construct();
	}

	public function echoExceptionWeb(\Exception $exception) {
		$this->client->getIdent($this->client->captureException($exception));
		parent::echoExceptionWeb($exception);
	}

	public function echoExceptionCLI(\Exception $exception) {
		$this->client->getIdent($this->client->captureException($exception));
		parent::echoExceptionCLI($exception);
	}
}