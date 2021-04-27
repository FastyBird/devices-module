# FastyBird devices module

[![Build Status](https://badgen.net/github/checks/FastyBird/application-events/master?cache=300&style=flast-square)](https://github.com/FastyBird/devices-module/actions)
[![Code coverage](https://badgen.net/coveralls/c/github/FastyBird/devices-module?cache=300&style=flast-square)](https://coveralls.io/r/FastyBird/devices-module)
![PHP](https://badgen.net/packagist/php/FastyBird/devices-module?cache=300&style=flast-square)
[![Licence](https://badgen.net/packagist/license/FastyBird/devices-module?cache=300&style=flast-square)](https://packagist.org/packages/FastyBird/devices-module)
[![Downloads total](https://badgen.net/packagist/dt/FastyBird/devices-module?cache=300&style=flast-square)](https://packagist.org/packages/FastyBird/devices-module)
[![Latest stable](https://badgen.net/packagist/v/FastyBird/devices-module/latest?cache=300&style=flast-square)](https://packagist.org/packages/FastyBird/devices-module)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat-square)](https://github.com/phpstan/phpstan)

## What is FastyBird devices module?

Devices module is a [Nette framework](https://nette.org) extension for managing connected devices and their basic logic and exposing them for clients.

[FastyBird](https://www.fastybird.com) devices module is an [Apache2 licensed](http://www.apache.org/licenses/LICENSE-2.0) distributed extension, developed in [PHP](https://www.php.net) with [Nette framework](https://nette.org).

## Requirements

[FastyBird](https://www.fastybird.com) devices module is tested against PHP 7.4 and [ReactPHP http](https://github.com/reactphp/http) 0.8 event-driven, streaming plaintext HTTP server and [Nette framework](https://nette.org/en/) 3.0 PHP framework for real programmers

## Getting started

The best way to install **fastybird/devices-module** is using [Composer](https://getcomposer.org/). If you don't have Composer yet, [download it](https://getcomposer.org/download/) following the instructions.
Then use command:

```sh
$ composer create-project --no-dev fastybird/devices-module path/to/install
$ cd path/to/install
```

Everything required will be then installed in the provided folder `path/to/install`

Or if you already have created project you could use command:

```sh
$ composer require fastybird/devices-module
```

## Configuration

This module is dependent on other Nette extensions. All this extensions have to enabled and configured in NEON configuration file.

Example configuration could be found [here](https://github.com/FastyBird/devices-module/blob/master/config/example.neon)

## Initialization

This module is using database, and need some initial data to be inserted into it. This could be done via shell command:

```sh
$ vendor/bin/fb-console fb:devices-module:initialize
```

This console command is interactive and will ask for all required information.

## HTTP server

This module has built-in web server for serving module api to clients. This web server could be started with command:
```sh
$ vendor/bin/fb-console fb:web-server:start
```

After successful start, app is listening for incoming http api request messages from clients.

## Feedback

Use the [issue tracker](https://github.com/FastyBird/devices-module/issues) for bugs or [mail](mailto:code@fastybird.com) or [Tweet](https://twitter.com/fastybird) us for any idea that can improve the project.

Thank you for testing, reporting and contributing.

## Changelog

For release info check [release page](https://github.com/FastyBird/devices-module/releases)

## Maintainers

<table>
	<tbody>
		<tr>
			<td align="center">
				<a href="https://github.com/akadlec">
					<img width="80" height="80" src="https://avatars3.githubusercontent.com/u/1866672?s=460&amp;v=4">
				</a>
				<br>
				<a href="https://github.com/akadlec">Adam Kadlec</a>
			</td>
		</tr>
	</tbody>
</table>

***
Homepage [https://www.fastybird.com](https://www.fastybird.com) and repository [https://github.com/fastybird/devices-module](https://github.com/fastybird/devices-module).
