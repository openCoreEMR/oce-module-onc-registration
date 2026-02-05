# ONC Registration Module - Development Guide

This document describes the architecture and conventions for the ONC Registration module.

## Module Overview

This module helps healthcare organizations register their OpenEMR installation for ONC certification compliance. It validates configuration, collects organization information, verifies registration status, and generates registration emails.

## Architecture

The module follows a **Symfony-inspired MVC architecture**:

- **Controllers** in `src/Controller/` handle request dispatching
- **Services** in `src/Service/` contain business logic
- **Twig templates** in `templates/` render all HTML
- **Minimal entry points** in `public/` bootstrap and dispatch

## File Structure

```
oce-module-onc-registration/
├── public/
│   └── index.php              # Main entry point
├── src/
│   ├── Bootstrap.php          # Module initialization
│   ├── ConfigAccessorInterface.php
│   ├── ConfigFactory.php
│   ├── EnvironmentConfigAccessor.php
│   ├── GlobalsAccessor.php
│   ├── GlobalConfig.php       # Centralized config with typed getters
│   ├── ModuleAccessGuard.php
│   ├── Controller/
│   │   └── DashboardController.php
│   ├── Service/
│   │   ├── ConfigurationValidator.php
│   │   ├── NpiValidator.php
│   │   └── RegistrationService.php
│   └── Exception/
│       └── ...                # Custom exception types
├── templates/
│   └── dashboard/
│       └── index.html.twig
└── openemr.bootstrap.php
```

## Configuration Abstraction

All config settings are accessible via both environment variables AND OpenEMR globals. Environment variables take precedence.

### Adding a New Config Option

1. Add constant in `GlobalConfig`:
```php
public const CONFIG_MY_SETTING = 'oce_onc_registration_my_setting';
```

2. Map env var in `EnvironmentConfigAccessor::KEY_MAP`:
```php
private const KEY_MAP = [
    GlobalConfig::CONFIG_MY_SETTING => 'OCE_ONC_REGISTRATION_MY_SETTING',
];
```

3. Add typed getter in `GlobalConfig`:
```php
public function getMySetting(): string
{
    return $this->configAccessor->getString(self::CONFIG_MY_SETTING, '');
}
```

4. Register in `Bootstrap::addGlobalSettingsSection()` for admin UI.

### Environment Config Mode

When `OCE_ONC_REGISTRATION_ENV_CONFIG=1`, the admin UI shows "This module is configured via environment variables" instead of editable fields.

## Key Patterns

### Entry Point Guard

```php
$guardResponse = ModuleAccessGuard::check(Bootstrap::MODULE_NAME);
if ($guardResponse instanceof Response) {
    $guardResponse->send();
    return;
}
```

### Controller Dispatch

Controllers return `Response` objects, never void. Use exceptions for errors:

```php
public function dispatch(string $action, array $params): Response
{
    return match ($action) {
        'dashboard' => $this->showDashboard($params),
        default => $this->showDashboard($params),
    };
}
```

### Twig Filters

- `xlt` - Translate text
- `text` - Sanitize for HTML output
- `attr` - Sanitize for HTML attributes
- `xlj` - Translate and JSON-encode for JavaScript

## Code Quality

Run before committing:

```bash
composer check      # All quality checks
composer phpstan    # PHPStan at level 10
composer test       # PHPUnit tests
```

## Security Checklist

- Validate CSRF tokens on POST requests
- Check ACL before sensitive operations
- Sanitize all output with Twig filters
- Never expose detailed error messages to users
- Log security events with structured context
