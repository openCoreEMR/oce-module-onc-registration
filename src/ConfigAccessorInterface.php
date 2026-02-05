<?php

/**
 * Interface for typed configuration access
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration;

use OpenEMR\Core\Kernel;

/**
 * Abstraction for configuration access, matching Symfony ParameterBag's typed accessor methods.
 * Allows configuration to be read from either OpenEMR globals (database) or environment variables.
 */
interface ConfigAccessorInterface
{
    /**
     * Get the OpenEMR Kernel instance
     */
    public function getKernel(): ?Kernel;

    /**
     * Get a configuration value
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Get a string configuration value
     */
    public function getString(string $key, string $default = ''): string;

    /**
     * Get a boolean configuration value
     */
    public function getBoolean(string $key, bool $default = false): bool;

    /**
     * Get an integer configuration value
     */
    public function getInt(string $key, int $default = 0): int;

    /**
     * Check if a configuration key exists
     */
    public function has(string $key): bool;
}
