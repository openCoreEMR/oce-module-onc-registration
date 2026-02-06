<?php

/**
 * Factory for creating configuration accessors
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration;

/**
 * Factory for creating the appropriate configuration accessor.
 *
 * When OCE_ONC_REGISTRATION_ENV_CONFIG=1 is set, configuration is read from
 * environment variables instead of the database-backed OpenEMR globals.
 *
 * This pattern allows modules to be configured via environment variables in
 * containerized deployments while still supporting traditional database configuration.
 */
class ConfigFactory
{
    /**
     * Environment variable that toggles environment-based configuration.
     * Set to "1" or "true" to enable environment variable configuration mode.
     */
    public const ENV_CONFIG_VAR = 'OCE_ONC_REGISTRATION_ENV_CONFIG';

    /**
     * Check if environment-only config mode is enabled
     */
    public static function isEnvConfigMode(): bool
    {
        $value = getenv(self::ENV_CONFIG_VAR);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Create the appropriate config accessor based on environment
     *
     * Returns EnvironmentConfigAccessor when ENV_CONFIG_VAR is set to true,
     * otherwise returns GlobalsAccessor for database-backed configuration.
     */
    public static function createConfigAccessor(): ConfigAccessorInterface
    {
        if (self::isEnvConfigMode()) {
            return new EnvironmentConfigAccessor();
        }
        return new GlobalsAccessor();
    }
}
