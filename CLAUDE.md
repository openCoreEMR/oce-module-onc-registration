# OpenEMR Module Development Guide for AI Agents

This document describes the architectural patterns and conventions for OpenEMR modules developed by OpenCoreEMR. Follow these patterns when working on **any** OpenEMR module in this organization.

## About This Template

**This is a template repository.** When creating a new module:

### Quick Start (Recommended)
1. Copy this repository
2. Run `composer install`
3. Run `./bin/setup` - Interactive wizard that:
   - Asks if internal (OpenCoreEMR/oce-) or external (community/oe-)
   - Prompts for module name
   - Replaces all placeholders automatically
   - Optionally removes setup files when done
4. Start building your module
5. See `GETTING_STARTED.md` for detailed checklist

### Manual Setup (Alternative)
1. Copy this repository
2. Replace placeholder values throughout the codebase (see Placeholder Reference below)
3. Update `composer.json`, `version.php`, `phpcs.xml`, and documentation files
4. See `GETTING_STARTED.md` for step-by-step checklist

**For AI agents:** When a user asks you to "create a new OpenEMR module" or work on module code, you should follow the patterns documented here. If working on an existing module, verify it follows these patterns and refactor if necessary to maintain consistency.

## Placeholder Reference

This template uses the following placeholders that should be replaced when creating a new module:

| Placeholder | Description | Internal Example | External Example |
|-------------|-------------|------------------|------------------|
| `OpenCoreEMR` | Vendor name (PascalCase) | `OpenCoreEMR` | `MyOrg` or `OpenEMR` |
| `opencoreemr` | Vendor name (lowercase) | `opencoreemr` | `myorg` or `openemr` |
| `oce` | Module prefix | `oce` | `oe` |
| `onc-registration` | Module name (lowercase-with-hyphens) | `lab-integration` | `lab-integration` |
| `OncRegistration` | Module name (PascalCase) | `LabIntegration` | `LabIntegration` |

**Usage Examples:**

**For OpenCoreEMR Internal Use:**
- Repository: `oce-module-lab-integration`
- Package: `opencoreemr/oce-module-lab-integration`
- Namespace: `OpenCoreEMR\Modules\LabIntegration`
- Module ID: `oce-module-lab-integration`

**For External/Community Use:**
- Repository: `oe-module-lab-integration`
- Package: `myorg/oe-module-lab-integration` or `openemr/oe-module-lab-integration`
- Namespace: `MyOrg\Modules\LabIntegration` or `OpenEMR\Modules\LabIntegration`
- Module ID: `oe-module-lab-integration`

## Naming Conventions

When creating a new module from this template, use consistent naming:

| Context | Format | Internal Example | External Example |
|---------|--------|------------------|------------------|
| Repository name | `oce-module-{name}` | `oce-module-lab-integration` | `oe-module-lab-integration` |
| Composer package | `opencoreemr/oce-module-{name}` | `opencoreemr/oce-module-lab-integration` | `openemr/oe-module-lab-integration` |
| Namespace | `OpenCoreEMR\Modules\{PascalCase}` | `OpenCoreEMR\Modules\LabIntegration` | `OpenEMR\Modules\LabIntegration` |
| Exception prefix | `{PascalCase}Exception` | `LabIntegrationNotFoundException` | `LabIntegrationNotFoundException` |
| Bootstrap constant | `oce-module-{name}` | `oce-module-lab-integration` | `oe-module-lab-integration` |
| File names | PascalCase for classes | `LabResultController.php` | `LabResultController.php` |
| Directory names | lowercase | `lab-results/` | `lab-results/` |

**Module name rules:**
- Use lowercase with hyphens for repositories and URLs
- Use PascalCase for PHP namespaces and class names
- Keep names concise but descriptive
- Avoid redundant words like "openemr" or "module" in the functional name
- Internal modules use `oce-` prefix, external/community modules use `oe-` prefix

## Module Architecture Overview

OpenEMR modules follow a **Symfony-inspired MVC architecture** with:
- **Controllers** in `src/Controller/` handling business logic
- **Twig templates** in `templates/` for all HTML rendering
- **Services** in `src/Service/` for business operations
- **Minimal public entry points** in `public/` that bootstrap and dispatch

## File Structure Convention

```
oce-module-onc-registration/
├── bin/
│   └── setup              # Setup wizard (removed after initial setup)
├── public/
│   ├── index.php          # Main entry point (25-35 lines)
│   ├── {feature}.php      # Feature entry points (25-35 lines)
│   └── assets/            # Static assets (CSS, JS, images)
├── src/
│   ├── Bootstrap.php      # Module initialization and DI
│   ├── ConfigAccessorInterface.php  # Configuration access abstraction
│   ├── ConfigFactory.php            # Factory for config accessor selection
│   ├── EnvironmentConfigAccessor.php # Env var config (for containers)
│   ├── GlobalsAccessor.php          # Database-backed config (OpenEMR globals)
│   ├── GlobalConfig.php             # Centralized configuration wrapper
│   ├── ModuleAccessGuard.php        # Entry point access guard
│   ├── Command/           # Console commands (removed after setup)
│   │   └── SetupCommand.php
│   ├── Controller/        # Request handlers
│   │   ├── {Feature}Controller.php
│   │   └── ...
│   ├── Service/           # Business logic
│   │   ├── {Feature}Service.php
│   │   └── ...
│   └── Exception/         # Custom exception types
│       ├── OncRegistrationExceptionInterface.php
│       ├── OncRegistrationException.php
│       ├── OncRegistrationNotFoundException.php
│       ├── OncRegistrationUnauthorizedException.php
│       ├── OncRegistrationAccessDeniedException.php
│       ├── OncRegistrationValidationException.php
│       ├── OncRegistrationConfigurationException.php
│       └── OncRegistrationApiException.php
├── templates/
│   └── {feature}/
│       ├── {view}.html.twig
│       └── partials/
│           └── _{component}.html.twig
├── composer.json
└── openemr.bootstrap.php  # Module loader
```

## Configuration Abstraction Layer

The template includes a flexible configuration system that supports both database-backed (OpenEMR globals) and environment variable configurations:

### Key Components

| File | Purpose |
|------|---------|
| `ConfigAccessorInterface` | Common interface for all config accessors |
| `GlobalsAccessor` | Reads config from OpenEMR database globals |
| `EnvironmentConfigAccessor` | Reads config from environment variables |
| `ConfigFactory` | Selects the appropriate accessor based on environment |
| `GlobalConfig` | Centralized wrapper providing typed access to all module config |

### Usage Pattern

```php
// In Bootstrap or entry points - factory determines config source
$configAccessor = ConfigFactory::createConfigAccessor();
$config = new GlobalConfig($configAccessor);

// Use typed getters
$isEnabled = $config->isEnabled();      // bool
$apiKey = $config->getApiKey();         // string (decrypted in DB mode)
```

### Environment Variable Mode

Set `{VENDOR_PREFIX}_{MODULENAME}_ENV_CONFIG=1` to use environment variables instead of database:

```bash
# Enable env config mode
export {VENDOR_PREFIX}_{MODULENAME}_ENV_CONFIG=1

# Module configuration
export {VENDOR_PREFIX}_{MODULENAME}_ENABLED=true
export {VENDOR_PREFIX}_{MODULENAME}_API_KEY=your-api-key
```

Benefits:
- Container-friendly deployments (no database config needed)
- Secrets can be injected via environment
- Config is immutable (no admin UI editing)

### Adding New Config Options

1. Add constant in `GlobalConfig`:
```php
public const CONFIG_OPTION_API_KEY = '{vendor_prefix}_onc-registration_api_key';
```

2. Add env var mapping in `EnvironmentConfigAccessor`:
```php
private const KEY_MAP = [
    GlobalConfig::CONFIG_OPTION_API_KEY => '{VENDOR_PREFIX}_{MODULENAME}_API_KEY',
];
```

3. Add getter in `GlobalConfig`:
```php
public function getApiKey(): string
{
    return $this->configAccessor->getString(self::CONFIG_OPTION_API_KEY, '');
}
```

4. Add to `getGlobalSettingSectionConfiguration()` for admin UI.

## Module Access Guard

The `ModuleAccessGuard` prevents access to module endpoints when:
1. Module is not registered in OpenEMR
2. Module is disabled in module management
3. Module's own 'enabled' setting is off

```php
// At top of public entry points. Use return (not exit) to stay consistent with "no exit/die" rules.
$guardResponse = ModuleAccessGuard::check(Bootstrap::MODULE_NAME);
if ($guardResponse instanceof Response) {
    $guardResponse->send();
    return;
}
run();  // Rest of entry logic in a function so the guard can return instead of exit
```

Returns 404 (not 403) to avoid leaking module presence. Wrapping the rest of the entry point in a function (e.g. `run()`) allows the guard to use `return` instead of `exit`, keeps the template testable, and avoids any exception for "exit in guard only" in the coding standards.

### Authentication and ACL

Entry points load `globals.php`, which typically starts the OpenEMR session. For sensitive operations (create, update, delete, or viewing sensitive data), call OpenEMR's ACL (e.g. `AclMain::aclCheckCore('section', 'action')`) and throw `OncRegistrationUnauthorizedException` or `OncRegistrationAccessDeniedException` if the check fails. The menu item's `acl_req` controls visibility; controllers must enforce the same (or stricter) ACL before performing the action.

## Public Entry Point Pattern

Public PHP files should be short! Just dispatch a controller and send a response. Follow this pattern:

```php
<?php
/**
 * [Description of endpoint]
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    [Author Name] <email@example.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   GNU General Public License 3
 */

$sessionAllowWrite = true;

// Load module autoloader before globals.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../../../../globals.php';

use OpenCoreEMR\Modules\OncRegistration\Bootstrap;
use OpenCoreEMR\Modules\OncRegistration\ConfigFactory;
use OpenCoreEMR\Modules\OncRegistration\Exception\OncRegistrationExceptionInterface;
use OpenCoreEMR\Modules\OncRegistration\GlobalsAccessor;
use OpenCoreEMR\Modules\OncRegistration\ModuleAccessGuard;
use Symfony\Component\HttpFoundation\Response;

// Check if module is installed and enabled - return 404 if not
$guardResponse = ModuleAccessGuard::check(Bootstrap::MODULE_NAME);
if ($guardResponse instanceof Response) {
    $guardResponse->send();
    return;
}
run();

function run(): void {
    // Get kernel and bootstrap module
    $globalsAccessor = new GlobalsAccessor();
    $kernel = $globalsAccessor->get('kernel');
    // ... bootstrap, get controller, dispatch ...
    try {
        $response = $controller->dispatch($action);
        $response->send();
    } catch (OncRegistrationExceptionInterface $e) {
        error_log("Module error: " . $e->getMessage());
        // Use a generic Twig error page; do not show exception messages to users
        $response = createErrorResponse($e->getStatusCode(), $kernel, $bootstrap->getWebroot());
        $response->send();
    } catch (\Throwable $e) {
        error_log("Unexpected error: " . $e->getMessage());
        $response = createErrorResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $kernel, $bootstrap->getWebroot());
        $response->send();
    }
}
```

In exception handlers, render a generic error template (e.g. `templates/error.html.twig`) instead of echoing exception messages, to avoid leaking implementation details. Log the real message with `error_log()`.

## Controller Pattern

Controllers should:
- Be in `src/Controller/`
- Use **constructor dependency injection**
- Return **Symfony Response objects** (never void)
- Have a `dispatch()` method that routes actions
- Throw **custom exceptions** (never die/exit)

```php
<?php

namespace OpenCoreEMR\Modules\OncRegistration\Controller;

use OpenCoreEMR\Modules\OncRegistration\Exception\OncRegistrationAccessDeniedException;
use OpenCoreEMR\Modules\OncRegistration\Exception\OncRegistrationNotFoundException;
use OpenCoreEMR\Modules\OncRegistration\Exception\OncRegistrationValidationException;
use OpenCoreEMR\Modules\OncRegistration\GlobalConfig;
use OpenCoreEMR\Modules\OncRegistration\Service\{Feature}Service;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class {Feature}Controller
{
    private readonly SystemLogger $logger;

    public function __construct(
        private readonly GlobalConfig $config,
        private readonly {Feature}Service $service,
        private readonly Environment $twig
    ) {
        $this->logger = new SystemLogger();
    }

    /**
     * Dispatch action to appropriate method
     *
     * @param array<string, mixed> $params
     */
    public function dispatch(string $action, array $params): Response
    {
        return match ($action) {
            'create' => $this->handleCreate($params),
            'view' => $this->showView($params),
            'list' => $this->showList($params),
            default => $this->showList($params),
        };
    }

    /**
     * @param array<string, mixed> $params
     */
    private function showList(array $params): Response
    {
        // Business logic here

        $content = $this->twig->render('{feature}/list.html.twig', [
            'items' => $items,
            'csrf_token' => CsrfUtils::collectCsrfToken(),
        ]);

        return new Response($content);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function handleCreate(array $params): Response
    {
        // Validate CSRF
        if (!CsrfUtils::verifyCsrfToken($params['csrf_token'] ?? '')) {
            throw new OncRegistrationAccessDeniedException("CSRF token verification failed");
        }

        // Validate input (use explicit check; avoid empty() which treats "0" as missing)
        $required = trim($params['required_field'] ?? '');
        if ($required === '') {
            throw new OncRegistrationValidationException("Required field is missing");
        }

        // Process request
        try {
            $this->service->create($params);
            return new RedirectResponse($_SERVER['PHP_SELF']);
        } catch (\Exception $e) {
            $this->logger->error("Error creating item: " . $e->getMessage());
            throw new OncRegistrationException("Error creating item: " . $e->getMessage());
        }
    }
}
```

## Exception Handling Pattern

### Define Custom Exception Hierarchy

All modules should have their own exception types in `src/Exception/`:

```php
<?php
// src/Exception/OncRegistrationExceptionInterface.php

namespace OpenCoreEMR\Modules\OncRegistration\Exception;

interface OncRegistrationExceptionInterface extends \Throwable
{
    /**
     * Get the HTTP status code for this exception
     */
    public function getStatusCode(): int;
}
```

```php
<?php
// src/Exception/OncRegistrationException.php

namespace OpenCoreEMR\Modules\OncRegistration\Exception;

abstract class OncRegistrationException extends \RuntimeException implements OncRegistrationExceptionInterface
{
    abstract public function getStatusCode(): int;
}
```

```php
<?php
// src/Exception/OncRegistrationNotFoundException.php

namespace OpenCoreEMR\Modules\OncRegistration\Exception;

class OncRegistrationNotFoundException extends OncRegistrationException
{
    public function getStatusCode(): int
    {
        return 404;
    }
}
```

### Common Exception Types to Implement

- `OncRegistrationNotFoundException` (404) - Resource not found
- `OncRegistrationUnauthorizedException` (401) - User not authenticated
- `OncRegistrationAccessDeniedException` (403) - CSRF failed, insufficient permissions
- `OncRegistrationValidationException` (400) - Invalid input data
- `OncRegistrationConfigurationException` (500) - Configuration errors

### Exception Handling in Public Files

```php
try {
    $response = $controller->dispatch($action, $_REQUEST);
    $response->send();
} catch (OncRegistrationExceptionInterface $e) {
    error_log("Error: " . $e->getMessage());

    $response = new Response(
        "Error: " . htmlspecialchars($e->getMessage()),
        $e->getStatusCode()
    );
    $response->send();
} catch (\Exception $e) {
    error_log("Unexpected error: " . $e->getMessage());

    $response = new Response(
        "Error: An unexpected error occurred",
        500
    );
    $response->send();
}
```

## Response Handling - CRITICAL RULES

### ✅ ALWAYS DO:
- Controllers return `Response`, `JsonResponse`, `RedirectResponse`, or `BinaryFileResponse`
- Use Symfony HTTP Foundation components
- Call `$response->send()` in public entry points
- Use `Response` constants: `Response::HTTP_OK`, `Response::HTTP_NOT_FOUND`, etc.
- Throw exceptions with proper types (never with status codes in constructor)

### ❌ NEVER DO:
- ~~`header('Location: ...')`~~ → Use `RedirectResponse`
- ~~`http_response_code(404)`~~ → Use `new Response($content, 404)` or exceptions
- ~~`echo json_encode($data)`~~ → Use `JsonResponse`
- ~~`readfile($path)`~~ → Use `BinaryFileResponse`
- ~~`die()` or `exit`~~ → Throw exceptions
- ~~Controllers returning `void`~~ → Return `Response` objects

### Example: Correct Response Handling

```php
// JSON Response
return new JsonResponse(['status' => 'success'], Response::HTTP_OK);

// Redirect
return new RedirectResponse('/path/to/redirect');

// File Download
$response = new BinaryFileResponse($filePath);
$response->setContentDisposition(
    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
    'filename.pdf'
);
return $response;

// HTML Response
$content = $this->twig->render('template.html.twig', $data);
return new Response($content);
```

## Bootstrap Pattern

The `Bootstrap.php` class should provide factory methods for controllers and accept an optional `ConfigAccessorInterface`:

```php
<?php

namespace OpenCoreEMR\Modules\OncRegistration;

use OpenCoreEMR\Modules\OncRegistration\Controller\{Feature}Controller;
use OpenCoreEMR\Modules\OncRegistration\Service\{Feature}Service;
use OpenEMR\Core\Kernel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    public const MODULE_NAME = "oce-module-onc-registration";

    private readonly GlobalConfig $globalsConfig;
    private readonly \Twig\Environment $twig;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Kernel $kernel = new Kernel(),
        ?ConfigAccessorInterface $configAccessor = null
    ) {
        // Use factory to determine config source if not provided
        $configAccessor ??= ConfigFactory::createConfigAccessor();
        $this->globalsConfig = new GlobalConfig($configAccessor);

        $templatePath = \dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
        $twig = new TwigContainer($templatePath, $this->kernel);
        $this->twig = $twig->getTwig();
    }

    /**
     * Get {Feature}Controller instance
     */
    public function get{Feature}Controller(): {Feature}Controller
    {
        return new {Feature}Controller(
            $this->globalsConfig,
            new {Feature}Service($this->globalsConfig),
            $this->twig
        );
    }
}
```

### Environment Config Mode in Admin UI

When env config mode is enabled, the global settings section displays an informational message instead of editable fields:

```php
// In addGlobalSettingsSection()
if ($this->globalsConfig->isEnvConfigMode()) {
    $setting = new GlobalSetting(
        xlt('Configuration Managed Externally'),
        GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION,
        '', '', false
    );
    $setting->addFieldOption(
        GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK,
        static fn() => xlt('This module is configured via environment variables.')
    );
    $service->appendToSection($section, '{vendor_prefix}_onc-registration_env_config_notice', $setting);
    return;
}
```

## Twig Template Pattern

Templates should use OpenEMR's translation and sanitization filters:

```twig
{# templates/{feature}/view.html.twig #}

{% extends "base.html.twig" %}

{% block content %}
<div class="container">
    <h1>{{ 'Page Title'|xlt }}</h1>

    {% if error_message %}
        <div class="alert alert-danger">
            {{ error_message|text }}
        </div>
    {% endif %}

    <form method="post" action="{{ action_url|attr }}">
        <input type="hidden" name="csrf_token" value="{{ csrf_token|attr }}">

        <div class="form-group">
            <label>{{ 'Field Label'|xlt }}</label>
            <input type="text" name="field_name" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">
            {{ 'Submit'|xlt }}
        </button>
    </form>
</div>
{% endblock %}
```

### Twig Filter Reference
- `xlt` - Translate text
- `text` - Sanitize text for HTML output
- `attr` - Sanitize for HTML attributes
- `xlj` - Translate and JSON-encode for JavaScript

## Code Quality Standards

All code must pass these checks:

```bash
pre-commit run -a
```

This runs:
- ✅ PHP Syntax Check
- ✅ PHP_CodeSniffer (PHPCS)
- ✅ PHPStan Static Analysis
- ✅ Rector
- ✅ Composer Require Checker

### Common Quality Issues to Avoid

**Line Length:**
- Maximum 120 characters per line
- Split long constructors across multiple lines

**Type Hints:**
- Add PHPDoc for array parameters: `@param array<string, mixed> $params`
- Use proper return types on all methods

**Unused Code:**
- Never suppress warnings with `@SuppressWarnings`
- If a parameter is unused, either use it or remove it
- Remove commented-out code

## Dependencies

Always include these in `composer.json`:

```json
{
  "require": {
    "php": ">=8.2",
    "symfony/event-dispatcher": "^6.0 || ^7.0",
    "symfony/http-foundation": "^6.0 || ^7.0",
    "twig/twig": "^3.0"
  }
}
```

## Composer Require Checker Configuration

Update `.composer-require-checker.json` to whitelist OpenEMR symbols:

```json
{
  "symbol-whitelist": [
    "OpenEMR\\Common\\Csrf\\CsrfUtils",
    "OpenEMR\\Common\\Database\\QueryUtils",
    "OpenEMR\\Common\\Logging\\SystemLogger",
    "OpenEMR\\Core\\Kernel",
    "RuntimeException",
    "session_start",
    "session_status",
    "PHP_SESSION_NONE",
    "sqlStatement",
    "sqlQuery",
    "xlt",
    "text",
    "attr"
  ],
  "php-core-extensions": [
    "Core",
    "standard",
    "curl",
    "json",
    "session",
    "SPL"
  ]
}
```

## Security Checklist

- ✅ Always validate CSRF tokens on POST requests
- ✅ Require POST (or correct method) for write actions; only merge POST into params when method is POST
- ✅ Check user authentication and ACL before sensitive operations (e.g. `AclMain::aclCheckCore()`); throw UnauthorizedException or AccessDeniedException if denied
- ✅ Use `realpath()` and path validation to prevent directory traversal
- ✅ Sanitize all user input in templates (`text`, `attr` filters)
- ✅ Log security events (failed auth, path traversal attempts); pass user/sensitive data as structured context (e.g. `['name' => $name]`), not interpolated into the message, to avoid log injection and parsing issues
- ✅ Never expose detailed error messages to users
- ✅ Use explicit checks (e.g. `=== ''`, `=== null`) instead of `empty()` for string/ID validation so values like `"0"` are not rejected
- ✅ Do not redirect to user-supplied URLs; use a server-derived value (e.g. `$_SERVER['PHP_SELF']`) or an allowlist of allowed targets

## Summary - Quick Checklist

Before considering work complete:

- [ ] Public entry points are 25-35 lines max
- [ ] Controllers return Response objects (never void)
- [ ] No `header()`, `http_response_code()`, `die()`, or `exit` calls
- [ ] Custom exception hierarchy with interface and getStatusCode()
- [ ] Twig templates for all HTML (no inline HTML in PHP)
- [ ] CSRF validation on all POST requests
- [ ] Write actions require POST; entry point only merges POST params when method is POST
- [ ] ACL enforced in controller (or entry point) for sensitive actions; no redirect to user-supplied URLs
- [ ] All pre-commit checks passing
- [ ] PHPDoc comments with proper type hints
- [ ] Symfony HTTP Foundation components used throughout
