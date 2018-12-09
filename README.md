# Sentry Client for TYPO3

Exception logging with sentry, see http://www.getsentry.com

The extension is a wrapper for https://github.com/getsentry/sentry-php

## Installation

```bash
$ composer require networkteam/sentry-client
```

After installation via composer the configuration setup has to be run.

```bash
$ vendor/bin/typo3cms extension:setup sentry_client
```

It's also available in TER: http://typo3.org/extensions/repository/view/sentry_client

## Configuration

Set the dsn (http://public_key:secret_key@your-sentry-server.com/project-id) in the Extension Manager and you are done.

## Feature Toggles

* Report exceptions in production context only
* Report exceptions when `$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']` matches client ip
* Ignore PageNotFoundException and trigger 404 handling instead
* Report user information: Select one of `none` | `userid` | `usernameandemail`
* Blacklist exception message regular expression

## Development

You can use `$GLOBALS['USER']['sentryClient']` which is an instance of [\Raven_Client](https://github.com/getsentry/sentry-php/blob/master/lib/Raven/Client.php) to add your own tags or log messages.

**Example: Add a custom tag**

`$GLOBALS['USER']['sentryClient']->tags_context(['release' => '201802072115']);`

## Support / Improvements / Issues

This extension is managed on GitHub. Feel free to get in touch at
https://github.com/networkteam/sentry_client