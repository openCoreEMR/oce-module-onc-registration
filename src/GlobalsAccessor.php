<?php

/**
 * Central accessor for OpenEMR globals
 *
 * This class provides a polyfill for OpenEMR's OEGlobalsBag which won't be
 * available until the next version of OpenEMR. It serves as a single point
 * of abstraction for globals access.
 *
 * @see https://github.com/openemr/openemr/blob/master/src/Core/OEGlobalsBag.php
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
 * Provides centralized access to OpenEMR globals.
 * This class serves as a single point of abstraction for globals access,
 * making it easier to update or refactor in the future.
 *
 * Implements ConfigAccessorInterface for database-backed configuration.
 */
class GlobalsAccessor implements ConfigAccessorInterface
{
    /**
     * Get a value from globals
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $GLOBALS[$key] ?? $default;
    }

    /**
     * Set a value in globals
     */
    public function set(string $key, mixed $value): void
    {
        $GLOBALS[$key] = $value;
    }

    /**
     * Check if a key exists in globals
     */
    public function has(string $key): bool
    {
        return isset($GLOBALS[$key]);
    }

    /**
     * Get a string value from globals
     */
    public function getString(string $key, string $default = ''): string
    {
        $value = $this->get($key, $default);
        if (is_string($value)) {
            return $value;
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        return $default;
    }

    /**
     * Get a boolean value from globals
     */
    public function getBoolean(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);
        if (is_bool($value)) {
            return $value;
        }
        // Handle string/numeric boolean values
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get an integer value from globals
     */
    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);
        if (is_int($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        return $default;
    }

    /**
     * Get all globals
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        /** @var array<string, mixed> $GLOBALS */
        return $GLOBALS;
    }

    /**
     * Get the OpenEMR Kernel instance
     */
    public function getKernel(): ?Kernel
    {
        $kernel = $this->get('kernel');
        return $kernel instanceof Kernel ? $kernel : null;
    }
}
