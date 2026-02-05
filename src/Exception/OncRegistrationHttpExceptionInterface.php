<?php

/**
 * Interface for HTTP-aware module exceptions
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Exception;

/**
 * Interface for module exceptions that map to HTTP status codes.
 *
 * Implement this interface for exceptions that should result in specific
 * HTTP responses (e.g., 404 Not Found, 403 Forbidden).
 */
interface OncRegistrationHttpExceptionInterface extends OncRegistrationExceptionInterface
{
    /**
     * Get the HTTP status code for this exception
     */
    public function getStatusCode(): int;
}
