# TrackBridge

TrackBridge is a CakePHP 5 application for managing embroidery work from scheduling and operator assignment through QC review and production completion. Operators are a single authorization role with distinct work types such as digitizing, programming, and data entry.

## Project Guide

The maintained project reference is [docs/WORKFLOW.md](docs/WORKFLOW.md). It describes setup, roles and permissions, the job lifecycle, attachment rules, security configuration, troubleshooting, and the change log. Keep that file as the source of truth when behavior changes.

## Local Development

Requirements: PHP 8.1+, Composer, and PostgreSQL. Configure `config/app_local.php` or environment variables from `config/.env.example`; use a least-privilege database account and a unique `SECURITY_SALT` outside local development. The local config and `config/.env` are ignored by Git.

Under XAMPP, the app may be available at `http://localhost/embroidery_app`. The CakePHP development server can be started with:

```powershell
php bin/cake server -p 8765
```

Run tests with:

```powershell
php vendor/bin/phpunit
composer check
```

![Build Status](https://github.com/cakephp/app/actions/workflows/ci.yml/badge.svg?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/cakephp/app.svg?style=flat-square)](https://packagist.org/packages/cakephp/app)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%207-brightgreen.svg?style=flat-square)](https://github.com/phpstan/phpstan)

A skeleton for creating applications with [CakePHP](https://cakephp.org) 5.x.

The framework source code can be found here: [cakephp/cakephp](https://github.com/cakephp/cakephp).

## Installation

1. Download [Composer](https://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
2. Run `php composer.phar create-project --prefer-dist cakephp/app [app_name]`.

If Composer is installed globally, run

```bash
composer create-project --prefer-dist cakephp/app
```

In case you want to use a custom app dir name (e.g. `/myapp/`):

```bash
composer create-project --prefer-dist cakephp/app myapp
```

You can now either use your machine's webserver to view the default home page, or start
up the built-in webserver with:

```bash
bin/cake server -p 8765
```

Then visit `http://localhost:8765` to see the welcome page.

## Update

Since this skeleton is a starting point for your application and various files
would have been modified as per your needs, there isn't a way to provide
automated upgrades, so you have to do any updates manually.

## Configuration

Read and edit the environment specific `config/app_local.php` and set up the
`'Datasources'` and any other configuration relevant for your application.
Other environment agnostic settings can be changed in `config/app.php`.

## Layout

The app skeleton uses [Milligram](https://milligram.io/) (v1.3) minimalist CSS
framework by default. You can, however, replace it with any other library or
custom styles.
