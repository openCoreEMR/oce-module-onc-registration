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

## Development Workflow

Use the **Taskfile** for Docker and module operations. Install: `brew install go-task`

```bash
task dev:start          # Start Docker environment
task module:install     # Install and enable module in OpenEMR
task dev:browse         # Open OpenEMR in browser
task dev:port           # Show assigned ports
task dev:logs           # View OpenEMR logs
task dev:shell          # Shell into OpenEMR container
task dev:stop           # Stop Docker (keeps data)
```

## Code Quality

Run before committing:

```bash
pre-commit run -a   # Run all checks (PHPCS, PHPStan, Rector, etc.)
composer test       # Run PHPUnit tests
```

### CRITICAL: Handling Errors and Warnings

**NEVER ignore any error or warning from a check.** Make every effort to fix them properly.

**NEVER add entries to bypass files without explicit user approval:**

- `.composer-require-checker.json` `symbol-whitelist` - Do not add symbols here without asking. Fix the root cause instead (add dependency to composer.json, or verify the symbol is actually needed).
- PHPStan baseline files - Do not add errors to baselines without asking. Fix the code instead.
- Any other ignore/suppress mechanism - Always fix the issue rather than suppressing it.

**When you encounter a check failure:**

1. **Understand the error** - Read what's actually wrong
2. **Fix the root cause** - Don't work around it
3. **If you believe suppression is truly necessary** - Ask the user first and explain why

This applies to ALL static analysis, linting, and validation tools.

## CI Checks

### Conventional Commit Titles

PR titles must follow conventional commits format with **lowercase subject**:

```
type: lowercase description
```

Examples:
- `fix: resolve phpstan errors` (correct)
- `fix: Resolve PHPStan errors` (WRONG - uppercase)
- `feat: add preview mode` (correct)

### Composer Require Checker

CI runs `composer-require-checker` to verify all used symbols are declared as dependencies.

**When using PHP extensions** (like `ctype_digit`), add to `composer.json`:

```json
{
  "require": {
    "ext-ctype": "*"
  }
}
```

**OpenEMR classes** used by the module are already whitelisted in `.composer-require-checker.json` since OpenEMR is not a Composer dependency at runtime (it's the host application). If you need to use a new OpenEMR class, ask the user before adding it to the whitelist.

## Security Checklist

- Validate CSRF tokens on POST requests
- Check ACL before sensitive operations
- Sanitize all output with Twig filters
- Never expose detailed error messages to users
- Log security events with structured context
