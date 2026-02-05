<?php

/**
 * Guards module web entry points from access when module is not installed/enabled
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration;

use OpenEMR\Common\Database\QueryUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prevents access to module web endpoints when the module is not properly installed.
 *
 * This guard checks:
 * 1. Module is registered in OpenEMR's modules table
 * 2. Module is enabled in module management (mod_active = 1)
 *
 * If any condition fails, returns a 404 response to avoid leaking information
 * about the module's presence.
 */
class ModuleAccessGuard
{
    /**
     * Check if the module is accessible and return a 404 response if not.
     *
     * @param string $moduleDirectory The module directory name (e.g., 'oce-module-onc-registration')
     * @param (callable(string): bool)|null $moduleActiveChecker Optional callable for testing
     * @return Response|null Returns 404 Response if access denied, null if access allowed
     */
    public static function check(
        string $moduleDirectory,
        ?callable $moduleActiveChecker = null
    ): ?Response {
        // Check module registration and enabled status in modules table
        $isActive = $moduleActiveChecker !== null
            ? $moduleActiveChecker($moduleDirectory)
            : self::isModuleActive($moduleDirectory);

        if (!$isActive) {
            return self::createNotFoundResponse();
        }

        return null;
    }

    /**
     * Check if module is registered and active in OpenEMR's modules table
     */
    private static function isModuleActive(string $moduleDirectory): bool
    {
        try {
            $sql = "SELECT mod_active FROM modules WHERE mod_directory = ?";
            $result = QueryUtils::fetchSingleValue($sql, 'mod_active', [$moduleDirectory]);

            // Module not found or not active
            if ($result === null || $result === false) {
                return false;
            }

            // Check if active (mod_active = 1)
            if (is_numeric($result)) {
                return (int) $result === 1;
            }
            return false;
        } catch (\Throwable) {
            // Database error - fail closed (deny access)
            return false;
        }
    }

    /**
     * Create a 404 Not Found response
     */
    private static function createNotFoundResponse(): Response
    {
        return new Response('Not Found', Response::HTTP_NOT_FOUND, [
            'Content-Type' => 'text/plain'
        ]);
    }
}
