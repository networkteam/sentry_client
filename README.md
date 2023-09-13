# Sentry Client for TYPO3

![ci](https://github.com/networkteam/sentry_client/actions/workflows/ci.yml/badge.svg)
![Latest release on GitHub](https://img.shields.io/github/v/release/networkteam/sentry_client?logo=github)
![Downloads per month](https://img.shields.io/packagist/dm/networkteam/sentry-client?style=plastic)

TYPO3 logs error messages and exceptions to logfiles and the backend log module. This extension sends them to [Sentry](https://sentry.io/),
a SaaS/self-hosted application which aggregates them and informs you by mail. In Sentry you see a error messages with
additional information like stacktrace, HTTP headers and submitted request/form data.

## Technical decisions

Exceptions through database outages (imagine a mysql server restart) should not be reported, so the db connection is checked
before. Exceptions may be excluded via regexp on their message (won't fix this error => exclude it).
TYPO3 throws a lot of PHP Notices and they are not really interesting in production, they are excluded by default.

## Installation

```bash
$ composer require networkteam/sentry-client
```

The [TER version](https://typo3.org/extensions/repository/view/sentry_client) will not receive updates anymore. Feel free
to send us a crate of beer and we will make a new TER release.

## Configuration

**File: system/additional.php**
```php
if (TYPO3\CMS\Core\Core\Environment::getContext()->isProduction()) {
    // Register exception handler
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler'] = Networkteam\SentryClient\ProductionExceptionHandler::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler'] = Networkteam\SentryClient\ProductionExceptionHandler::class;
    // Forward log messages to Sentry
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'] = [
        \TYPO3\CMS\Core\Log\LogLevel::ERROR => [
            \Networkteam\SentryClient\SentryLogWriter::class => [],
        ],
    ];
    // Set sentry/sentry options
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sentry_client']['options']['server_name'] = 'web3';
}
```

### Environment variables

Since Sentry SDK 2.x there are [environment variables](https://docs.sentry.io/platforms/php/configuration/options/#common-options) which can be used, for example in a .htaccess file:

```apacheconfig
SetEnv SENTRY_DSN http://public_key@your-sentry-server.com/project-id
SetEnv SENTRY_RELEASE 1.0.7
SetEnv SENTRY_ENVIRONMENT Staging
```

### Feature Toggles

* Ignore database connection errors (they should better be handled by a monitoring system)
* Report user information: Select one of `none` | `userid`
* Ignore exception message regular expression
* Ignore LogWriter Components

### Request ID

If the web server has set a request ID header `X-Request-Id`, this is transmitted as a tag to trace errors to logs.

## How to test if the extension works?

```typescript
page = PAGE
page.20 = USER
page.20 {
  userFunc = Networkteam\SentryClient\Client->captureException
}
```
This triggers an error that will be reported.

## Issue tracker

This extension is managed on GitHub. Feel free to get in touch at
https://github.com/networkteam/sentry_client

## Help

There is a Slack channel #ext-sentry_client

## Changelog

### 4.2.0

* Add log message interpolation (Thanks to @sascha-egerer)
* Add Fingerprint to log messages
* Deprecated: Usage of DebugExceptionHandler

### 4.1.0

* Client IP is anonymized with `IpAnonymizationUtility::anonymizeIp()`. Thanks to @extcode
* Add `X-Request-Id` as tag. Thanks to @bergo
* Small code optimizations. Thanks to @tlueder and @LeoniePhiline

### 4.0.0

* Add stacktrace to LogWriter messages for message grouping in Sentry
* Add LogWriter component ignorelist
* Add v11.5 support
* Drop v9.5 support

### 3.0..3.1

* Add experimental LogWriter
* Remove setting activatePageNotFoundHandling
* Ignore PageNotFoundException by default
* Support TYPO3 proxy setting
* Use sentry/sdk:3.1

### 2.0..3.0

* Use sentry/sdk:2.0
* Remove setting productionOnly
* Remove setting reportWithDevIP
* Rename setting activatePageNotFoundHandlingActive to activatePageNotFoundHandling
* Report E_ALL ^ E_NOTICE
* Strip project root
* Show event id in FE
