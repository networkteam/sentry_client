# Sentry Client for TYPO3

Exception logging with [Sentry](https://sentry.io/)

## Installation

```bash
$ composer require networkteam/sentry-client
```

It's also available in TER: http://typo3.org/extensions/repository/view/sentry_client

## Configuration

```php
// Extension Configuration
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_client']['dsn'] = 'http://public_key@your-sentry-server.com/project-id';

// LocalConfiguration.php (New since 3.0!!!)
$GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler'] = 'Networkteam\SentryClient\ProductionExceptionHandler';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler'] = 'Networkteam\SentryClient\DebugExceptionHandler';
```

The new Sentry SDK 2.x has some [environment variables](https://docs.sentry.io/error-reporting/configuration/?platform=php#dsn) which can be used, for example in a .htaccess file:

```apacheconfig
SetEnv SENTRY_DSN http://public_key@your-sentry-server.com/project-id
SetEnv SENTRY_RELEASE 1.0.7
SetEnv SENTRY_ENVIRONMENT Staging
```

## Feature Toggles

* Ignore PageNotFoundException and trigger 404 handling instead
* Ignore database connection errors (they should better be handled by a monitoring system)
* Report user information: Select one of `none` | `userid` | `usernameandemail`
* Blacklist exception message regular expression 

## How to test the connection to Sentry?

```
page = PAGE
page.20 = USER
page.20 {
  userFunc = \Networkteam\SentryClient\Client->captureException
}
```
This triggers an exception which will be reported.

## Issue tracker

This extension is managed on GitHub. Feel free to get in touch at
https://github.com/networkteam/sentry_client

## Help

There is a Slack channel #ext-sentry_client

## Changelog

### 2.0..3.0

* Use sentry/sdk:2.0
* Remove setting productionOnly
* Remove setting reportWithDevIP
* Rename setting activatePageNotFoundHandlingActive to activatePageNotFoundHandling
* Report E_ALL ^ E_NOTICE
* Strip project root
* Show event id in FE