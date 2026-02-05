<?php

/**
 * Exception thrown when user is not authenticated
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Exception;

class OncRegistrationUnauthorizedException extends OncRegistrationException
{
    public function getStatusCode(): int
    {
        return 401;
    }
}
