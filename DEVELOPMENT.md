# OpenEMR Module Development Guide

## Overview

This template provides a starting point for developing OpenEMR modules using OpenCoreEMR's Symfony-inspired MVC architecture.

## Development Workflow

### Initial Setup

1. **Clone and customize the template**
   ```bash
   git clone https://github.com/opencoreemr/oce-module-template.git oce-module-yourname
   cd oce-module-yourname
   ```

2. **Update metadata** (see README.md)
   - `composer.json`
   - `version.php`
   - `phpcs.xml`

3. **Install dependencies**
   ```bash
   composer install
   ```

4. **Set up pre-commit hooks**
   ```bash
   pip install pre-commit
   pre-commit install
   ```

### Creating Your Module Structure

#### 1. Create Bootstrap Class

`src/Bootstrap.php` - Module initialization and dependency injection:

```php
<?php

namespace OpenCoreEMR\Modules\YourModule;

use OpenEMR\Core\Kernel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    public const MODULE_NAME = "oce-module-yourmodule";

    private readonly GlobalConfig $globalsConfig;
    private readonly \Twig\Environment $twig;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Kernel $kernel = new Kernel()
    ) {
        $this->globalsConfig = new GlobalConfig();

        $templatePath = \dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
        $twig = new TwigContainer($templatePath, $this->kernel);
        $this->twig = $twig->getTwig();
    }

    /**
     * Get YourFeatureController instance
     */
    public function getYourFeatureController(): YourFeatureController
    {
        return new YourFeatureController(
            $this->globalsConfig,
            new YourFeatureService($this->globalsConfig),
            $this->twig
        );
    }
}
```

#### 2. Create GlobalConfig Class

`src/GlobalConfig.php` - Configuration management:

```php
<?php

namespace OpenCoreEMR\Modules\YourModule;

class GlobalConfig
{
    /**
     * Get a global configuration value
     */
    public function getGlobalSetting(string $key, mixed $default = null): mixed
    {
        return $GLOBALS[$key] ?? $default;
    }

    /**
     * Example: Get module-specific setting
     */
    public function isModuleEnabled(): bool
    {
        return (bool) $this->getGlobalSetting('yourmodule_enabled', false);
    }
}
```

#### 3. Create Custom Exceptions

`src/Exception/YourModuleExceptionInterface.php`:

```php
<?php

namespace OpenCoreEMR\Modules\YourModule\Exception;

interface YourModuleExceptionInterface extends \Throwable
{
    public function getStatusCode(): int;
}
```

`src/Exception/YourModuleException.php`:

```php
<?php

namespace OpenCoreEMR\Modules\YourModule\Exception;

abstract class YourModuleException extends \RuntimeException implements YourModuleExceptionInterface
{
    abstract public function getStatusCode(): int;
}
```

Create specific exceptions:
- `YourModuleNotFoundException` (404)
- `YourModuleAccessDeniedException` (403)
- `YourModuleValidationException` (400)

#### 4. Create Controllers

`src/Controller/YourFeatureController.php`:

```php
<?php

namespace OpenCoreEMR\Modules\YourModule\Controller;

use OpenCoreEMR\Modules\YourModule\GlobalConfig;
use OpenCoreEMR\Modules\YourModule\Service\YourFeatureService;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class YourFeatureController
{
    public function __construct(
        private readonly GlobalConfig $config,
        private readonly YourFeatureService $service,
        private readonly Environment $twig
    ) {}

    public function dispatch(string $action, array $params): Response
    {
        return match ($action) {
            'list' => $this->showList($params),
            default => $this->showList($params),
        };
    }

    private function showList(array $params): Response
    {
        $items = $this->service->getItems();

        $content = $this->twig->render('feature/list.html.twig', [
            'items' => $items,
        ]);

        return new Response($content);
    }
}
```

#### 5. Create Services

`src/Service/YourFeatureService.php`:

```php
<?php

namespace OpenCoreEMR\Modules\YourModule\Service;

use OpenCoreEMR\Modules\YourModule\GlobalConfig;

class YourFeatureService
{
    public function __construct(
        private readonly GlobalConfig $config
    ) {}

    /**
     * Get items
     *
     * @return array<int, array<string, mixed>>
     */
    public function getItems(): array
    {
        // Business logic here
        return [];
    }
}
```

#### 6. Create Public Entry Points

`public/index.php` (25-35 lines):

```php
<?php

/**
 * Main entry point for Your Module
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <email@example.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

require_once __DIR__ . '/../../../../globals.php';

use OpenCoreEMR\Modules\YourModule\Bootstrap;
use OpenCoreEMR\Modules\YourModule\Exception\YourModuleExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

$kernel = $GLOBALS['kernel'];
$bootstrap = new Bootstrap($kernel->getEventDispatcher(), $kernel);

$controller = $bootstrap->getYourFeatureController();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    $response = $controller->dispatch($action, $_REQUEST);
    $response->send();
} catch (YourModuleExceptionInterface $e) {
    error_log("YourModule Error: " . $e->getMessage());
    $response = new Response("Error: " . htmlspecialchars($e->getMessage()), $e->getStatusCode());
    $response->send();
} catch (\Exception $e) {
    error_log("Unexpected error: " . $e->getMessage());
    $response = new Response("Error: An unexpected error occurred", 500);
    $response->send();
}
```

#### 7. Create Twig Templates

`templates/feature/list.html.twig`:

```twig
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ 'Your Feature'|xlt }}</title>
</head>
<body>
    <h1>{{ 'Your Feature List'|xlt }}</h1>

    <ul>
    {% for item in items %}
        <li>{{ item.name|text }}</li>
    {% endfor %}
    </ul>
</body>
</html>
```

### Code Quality

#### Pre-Commit Hooks

The template includes pre-commit hooks that automatically run on `git commit`:

- **PHP Syntax Check** - Validates PHP syntax
- **PHP CodeSniffer** - Enforces PSR-12 coding standards
- **PHPStan** - Static analysis (level 8)
- **Rector** - PHP 8.2+ compatibility checks
- **Composer Require Checker** - Validates dependencies

#### Manual Checks

```bash
# Run all checks
pre-commit run --all-files

# Or run individual checks
composer phpcs              # Code style
composer phpstan            # Static analysis
composer rector             # Compatibility check (dry-run)
composer rector-fix         # Apply Rector fixes
composer require-checker    # Check dependencies
```

#### Fixing Issues

```bash
# Auto-fix code style issues
composer phpcbf

# Apply Rector fixes
composer rector-fix
```

### Testing in OpenEMR

#### Manual Installation

1. Copy module to OpenEMR:
   ```bash
   cp -r . /path/to/openemr/interface/modules/custom_modules/oce-module-yourname/
   ```

2. In OpenEMR:
   - Navigate to **Administration > Modules > Manage Modules**
   - Click **Register**
   - Click **Install**
   - Click **Enable**

#### Development Iteration

When making changes:

1. Edit your code
2. Run code quality checks: `composer code-quality`
3. Copy to OpenEMR directory (or use symlink during development)
4. Test in browser
5. Commit when ready (pre-commit hooks run automatically)

### Database Schema

If your module needs database tables, create `table.sql`:

```sql
CREATE TABLE IF NOT EXISTS `oce_yourmodule_data` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

And `cleanup.sql` for uninstallation:

```sql
DROP TABLE IF EXISTS `oce_yourmodule_data`;
```

### Module Registration

Create `openemr.bootstrap.php` for OpenEMR to discover your module:

```php
<?php

/**
 * OpenEMR bootstrap file for Your Module
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <email@example.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

use OpenCoreEMR\Modules\YourModule\Bootstrap;
use OpenEMR\Menu\MenuEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @var EventDispatcherInterface $eventDispatcher
 * @var \OpenEMR\Core\Kernel $kernel
 */

$bootstrap = new Bootstrap($eventDispatcher, $kernel);

// Add menu item
$eventDispatcher->addListener(MenuEvent::MENU_UPDATE, function (MenuEvent $event) {
    $menu = $event->getMenu();

    $menuItem = [
        'requirement' => 0,
        'target' => 'mod',
        'menu_id' => 'mod0',
        'label' => xl('Your Module'),
        'url' => '/interface/modules/custom_modules/oce-module-yourname/public/index.php',
        'children' => [],
        'acl_req' => ['admin', 'super']
    ];

    $menu->insertAfter('modimg', 'Your Module', 'mod0', $menuItem);
});
```

## Architecture Reference

For detailed architectural patterns and conventions, see `CLAUDE.md`.

## Contributing

1. Create a feature branch
2. Make your changes
3. Ensure all code quality checks pass
4. Submit a pull request

## Support

- Issues: https://github.com/opencoreemr/oce-module-{yourname}/issues
- Email: support@opencoreemr.com

## License

GNU General Public License v3.0 or later
