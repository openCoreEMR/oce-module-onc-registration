<?php

/**
 * Interface for module exceptions
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Exception;

/**
 * Marker interface for all module exceptions.
 *
 * This interface identifies exceptions originating from this module without
 * imposing any specific contract. Use OncRegistrationHttpExceptionInterface for
 * exceptions that map to HTTP status codes.
 */
interface OncRegistrationExceptionInterface extends \Throwable
{
}
