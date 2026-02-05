<?php

/**
 * Environment-based configuration accessor
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration;

use OpenEMR\Core\Kernel;

/**
 * Reads module configuration from environment variables.
 *
 * For module-specific config keys (defined in KEY_MAP), this accessor checks
 * environment variables first. For OpenEMR system values (webroot, site_addr_oath,
 * etc.), it delegates to GlobalsAccessor.
 *
 * Environment variables take precedence over database-backed globals for module
 * config, allowing container-friendly deployments to override settings.
 *
 * @internal Use ConfigFactory::createConfigAccessor() instead of instantiating directly
 */
class EnvironmentConfigAccessor implements ConfigAccessorInterface
{
    /**
     * Maps module config keys to environment variable names.
     *
     * @var array<string, string>
     */
    private const KEY_MAP = [
        GlobalConfig::CONFIG_PREVIEW_MODE => 'OCE_ONC_REGISTRATION_PREVIEW',
    ];

    public function __construct(private readonly GlobalsAccessor $globalsAccessor = new GlobalsAccessor())
    {
    }

    /**
     * Get the environment variable name for a config key, if mapped
     */
    private function getEnvVar(string $key): ?string
    {
        return self::KEY_MAP[$key] ?? null;
    }

    /**
     * Check environment variable for a module config key
     *
     * @return array{found: bool, value: string}
     */
    private function checkEnvVar(string $key): array
    {
        $envVar = $this->getEnvVar($key);
        if ($envVar === null) {
            return ['found' => false, 'value' => ''];
        }

        $value = getenv($envVar);
        if ($value === false) {
            return ['found' => false, 'value' => ''];
        }

        return ['found' => true, 'value' => $value];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $env = $this->checkEnvVar($key);
        if ($env['found']) {
            return $env['value'];
        }

        return $this->globalsAccessor->get($key, $default);
    }

    public function getString(string $key, string $default = ''): string
    {
        $env = $this->checkEnvVar($key);
        if ($env['found']) {
            return $env['value'];
        }

        return $this->globalsAccessor->getString($key, $default);
    }

    public function getBoolean(string $key, bool $default = false): bool
    {
        $env = $this->checkEnvVar($key);
        if ($env['found']) {
            return filter_var($env['value'], FILTER_VALIDATE_BOOLEAN);
        }

        return $this->globalsAccessor->getBoolean($key, $default);
    }

    public function getInt(string $key, int $default = 0): int
    {
        $env = $this->checkEnvVar($key);
        if ($env['found']) {
            return is_numeric($env['value']) ? (int) $env['value'] : $default;
        }

        return $this->globalsAccessor->getInt($key, $default);
    }

    public function has(string $key): bool
    {
        $env = $this->checkEnvVar($key);
        if ($env['found']) {
            return true;
        }

        return $this->globalsAccessor->has($key);
    }

    /**
     * Get the OpenEMR Kernel instance
     *
     * Delegates to GlobalsAccessor since Kernel is always from OpenEMR globals.
     */
    public function getKernel(): ?Kernel
    {
        return $this->globalsAccessor->getKernel();
    }
}
