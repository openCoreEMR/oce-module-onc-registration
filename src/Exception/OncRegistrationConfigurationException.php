<?php

/**
 * Exception thrown when module configuration is invalid or missing
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Exception;

class OncRegistrationConfigurationException extends OncRegistrationException
{
    public function getStatusCode(): int
    {
        return 500;
    }
}
