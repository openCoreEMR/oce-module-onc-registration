<?php

/**
 * Handles ONC registration submission and verification.
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Service;

use OpenCoreEMR\Modules\OncRegistration\GlobalConfig;
use OpenEMR\Common\Logging\SystemLogger;

class RegistrationService
{
    public const REGISTRATION_EMAIL = 'hello@open-emr.org';
    public const REGISTRATION_SUBJECT = 'ONC registration';

    /** Cache TTL in seconds (5 minutes) */
    private const CACHE_TTL = 300;

    /** @var array{registered: bool, error: ?string}|null Cached verification result */
    private ?array $verificationCache = null;

    /** @var int|null Timestamp when cache was populated */
    private ?int $cacheTime = null;

    private readonly SystemLogger $logger;

    public function __construct(
        private readonly GlobalConfig $config
    ) {
        $this->logger = new SystemLogger();
    }

    /**
     * Check if this installation's FHIR endpoint is registered on the published URLs page
     *
     * @return array{registered: bool, error: ?string}
     */
    public function verifyRegistration(): array
    {
        // Return cached result if still valid
        if ($this->verificationCache !== null && $this->isCacheValid()) {
            return $this->verificationCache;
        }

        $fhirEndpoint = $this->config->getFhirEndpoint();
        if ($fhirEndpoint === '') {
            return $this->cacheResult(false, 'FHIR endpoint not configured');
        }

        $pageContent = $this->fetchPublishedUrlsPage();
        if ($pageContent === null) {
            return $this->cacheResult(false, 'Unable to fetch published URLs page');
        }

        // Check if the FHIR endpoint appears on the page
        // The endpoint might be listed with or without trailing slash
        $endpointNormalized = rtrim($fhirEndpoint, '/');
        $isRegistered = str_contains($pageContent, $endpointNormalized)
            || str_contains($pageContent, $endpointNormalized . '/');

        return $this->cacheResult($isRegistered, null);
    }

    /**
     * Cache and return a verification result
     *
     * @return array{registered: bool, error: ?string}
     */
    private function cacheResult(bool $registered, ?string $error): array
    {
        $this->verificationCache = [
            'registered' => $registered,
            'error' => $error,
        ];
        $this->cacheTime = time();
        return $this->verificationCache;
    }

    /**
     * Check if the verification cache is still valid
     */
    private function isCacheValid(): bool
    {
        if ($this->cacheTime === null) {
            return false;
        }
        return (time() - $this->cacheTime) < self::CACHE_TTL;
    }

    /**
     * Clear the verification cache (useful for testing or forced refresh)
     */
    public function clearCache(): void
    {
        $this->verificationCache = null;
        $this->cacheTime = null;
    }

    /**
     * Fetch the published Service Base URLs page content
     */
    private function fetchPublishedUrlsPage(): ?string
    {
        $url = $this->getPublishedUrlsPage();

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'OpenEMR ONC Registration Module',
            ],
        ]);

        $content = @file_get_contents($url, false, $context);
        if ($content === false) {
            $this->logger->error('Failed to fetch published URLs page', ['url' => $url]);
            return null;
        }

        return $content;
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
        $version = $this->config->getOpenEmrWikiVersion();
        return "https://www.open-emr.org/wiki/index.php/OpenEMR_{$version}_API#Service_Base_URLs";
    }

    /**
     * Get the ONC Certification Requirements page URL
     */
    public function getOncRequirementsPage(): string
    {
        $version = $this->config->getOpenEmrWikiVersion();
        return "https://www.open-emr.org/wiki/index.php/"
            . "OpenEMR_{$version}_ONC_Ambulatory_EHR_Certification_Requirements";
    }

    /**
     * Check if registration info is complete
     *
     * All organization info is auto-detected from the primary business entity
     * facility and site_addr_oath. If info is missing, point users to the
     * appropriate configuration location.
     *
     * @return array{complete: bool, missing: array<string>}
     */
    public function checkRegistrationInfo(): array
    {
        $missing = [];

        if ($this->config->getOrgName() === '') {
            $missing[] = 'Organization Name (set in Admin > Facilities on your primary facility)';
        }

        if ($this->config->getOrgLocation() === '') {
            $missing[] = 'Organization Location (set address in Admin > Facilities)';
        }

        if ($this->config->getOrgNpi() === '') {
            $missing[] = 'Organization NPI (set Facility NPI in Admin > Facilities)';
        }

        if ($this->config->getFhirEndpoint() === '') {
            $missing[] = 'FHIR Endpoint (configure site_addr_oath in Globals > Connectors)';
        }

        return [
            'complete' => count($missing) === 0,
            'missing' => $missing,
        ];
    }
}
