# Sentry Client for TYPO3

![ci](https://github.com/networkteam/sentry_client/actions/workflows/ci.yml/badge.svg)
![Latest release on GitHub](https://img.shields.io/github/v/release/networkteam/sentry_client?logo=github)
![Latest TER release](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/sentry_client/version/shields)
![Downloads per month](https://img.shields.io/packagist/dm/networkteam/sentry-client?style=plastic)

TYPO3 logs error messages and exceptions to logfiles and the backend log module. This extension sends them to [Sentry](https://sentry.io/),
a SaaS/self-hosted application which aggregates them and informs you by mail. In Sentry you see a enriched error messages with
stacktrace, HTTP headers and submitted request/form data.

## Technical decisions

Exceptions through database outages (imagine a mysql server restart) should not be reported, so the db connection is checked
before. Exceptions may be excluded via regexp on their message (won't fix this error => exclude it).
TYPO3 throws a lot of PHP Notices and they are not really interesting in production, they are excluded by default.

## Installation

The preferred way is with Composer:

```bash
$ composer require networkteam/sentry-client
```

The [TER version](http://typo3.org/extensions/repository/view/sentry_client) includes some composer dependencies locally,
which may lead to problems in the future (one package with multiple version in the project).

## Configuration

```php
// Extension Configuration
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sentry_client']['dsn'] = 'http://public_key@your-sentry-server.com/project-id';

// LocalConfiguration.php (New since 3.0!!!)
$GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler'] = 'Networkteam\SentryClient\ProductionExceptionHandler';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler'] = 'Networkteam\SentryClient\DebugExceptionHandler';
```

### Environment variables

Since Sentry SDK 2.x there are [environment variables](https://docs.sentry.io/error-reporting/configuration/?platform=php#dsn) which can be used, for example in a .htaccess file:

```apacheconfig
SetEnv SENTRY_DSN http://public_key@your-sentry-server.com/project-id
SetEnv SENTRY_RELEASE 1.0.7
SetEnv SENTRY_ENVIRONMENT Staging

SetEnv SENTRY_IP_MASK 0
```

For masking/anonymizing the IP address \TYPO3\CMS\Core\Utility\IpAnonymizationUtility::anonymizeIp()
is used (see: [Feature: #84053 - API to anonymize IP addresses](https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/7.6.x/Feature-84053-APIToAnonymizeIPAddresses.html)).
Possible values are null, 0, 1, 2. For compatibility reasons the default value here is 0.

### LogWriter

The extension comes with a LogWriter which forwards messages to Sentry which normally are just logged.
You can enable it in EM or configure it for specific components:

```php
$GLOBALS['TYPO3_CONF_VARS']['LOG']['YourVendor]['YourExtension]['Controller']['writerConfiguration'] = [
    \TYPO3\CMS\Core\Log\LogLevel::ERROR => [
        \Networkteam\SentryClient\SentryLogWriter::class => [],
    ]
];
```

### Feature Toggles

* Ignore database connection errors (they should better be handled by a monitoring system)
* Report user information: Select one of `none` | `userid` | `usernameandemail`
* Blacklist exception message regular expression
* LogWriter Loglevel: If set, log messages are reported to Sentry
* LogWriter Component blacklist

## How to test if the extension works?

```typescript
page = PAGE
page.20 = USER
page.20 {
  userFunc = Networkteam\SentryClient\Client->captureException
}
```
This triggers an exception which will be reported.

## Issue tracker

This extension is managed on GitHub. Feel free to get in touch at
https://github.com/networkteam/sentry_client

## Help

There is a Slack channel #ext-sentry_client

## Changelog

### 4.0.0

* Add stacktrace to LogWriter messages for message grouping in Sentry
* Add LogWriter component blacklist
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
