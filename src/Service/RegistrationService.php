<?php

/**
 * Handles ONC registration submission.
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Service;

use OpenCoreEMR\Modules\OncRegistration\GlobalConfig;

class RegistrationService
{
    public const REGISTRATION_EMAIL = 'hello@open-emr.org';
    public const REGISTRATION_SUBJECT = 'ONC registration';

    public function __construct(
        private readonly GlobalConfig $config
    ) {
    }

    /**
     * Generate the registration email body
     */
    public function generateEmailBody(): string
    {
        $orgName = $this->config->getOrgName();
        $orgLocation = $this->config->getOrgLocation();
        $orgNpi = $this->config->getOrgNpi();
        $fhirEndpoint = $this->config->getFhirEndpoint();

        return <<<EMAIL
Organization Name: {$orgName}

Organization Location: {$orgLocation}

Organization NPI: {$orgNpi}

FHIR Endpoint URL: {$fhirEndpoint}

---
Submitted via ONC Registration Module
EMAIL;
    }

    /**
     * Generate a mailto link for the registration
     */
    public function generateMailtoLink(): string
    {
        $subject = rawurlencode(self::REGISTRATION_SUBJECT);
        $body = rawurlencode($this->generateEmailBody());

        return sprintf(
            'mailto:%s?subject=%s&body=%s',
            self::REGISTRATION_EMAIL,
            $subject,
            $body
        );
    }

    /**
     * Get the published Service Base URLs page URL
     */
    public function getPublishedUrlsPage(): string
    {
        // This URL may change with OpenEMR versions
        return 'https://www.open-emr.org/wiki/index.php/OpenEMR_7.0.2_API_Service_Base_URLs';
    }

    /**
     * Check if registration info is complete
     *
     * @return array{complete: bool, missing: array<string>}
     */
    public function checkRegistrationInfo(): array
    {
        $missing = [];

        if ($this->config->getOrgName() === '') {
            $missing[] = 'Organization Name';
        }

        if ($this->config->getOrgLocation() === '') {
            $missing[] = 'Organization Location';
        }

        if ($this->config->getOrgNpi() === '') {
            $missing[] = 'Organization NPI';
        }

        if ($this->config->getFhirEndpoint() === '') {
            $missing[] = 'FHIR Endpoint URL';
        }

        return [
            'complete' => count($missing) === 0,
            'missing' => $missing,
        ];
    }
}
