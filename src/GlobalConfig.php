<?php

/**
 * Provides access to OpenEMR configuration for ONC Registration.
 *
 * All organization info is auto-detected from the primary business entity
 * facility and OpenEMR globals. No module-specific configuration is needed.
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration;

use OpenEMR\Services\FacilityService;

class GlobalConfig
{
    // Module config keys (stored in OpenEMR globals)
    public const CONFIG_PREVIEW_MODE = 'oce_onc_registration_preview';

    private readonly bool $isEnvConfigMode;

    /** @var array<string, mixed>|null Cached primary business entity */
    private ?array $primaryFacility = null;

    /** @var bool Whether we've queried for the primary facility yet */
    private bool $primaryFacilityQueried = false;

    public function __construct(
        private readonly ConfigAccessorInterface $configAccessor = new GlobalsAccessor()
    ) {
        $this->isEnvConfigMode = ConfigFactory::isEnvConfigMode();
    }

    /**
     * Check if configuration is managed via environment variables
     */
    public function isEnvConfigMode(): bool
    {
        return $this->isEnvConfigMode;
    }

    // Required OpenEMR global settings for ONC certification
    public const REQUIRED_GLOBALS = [
        'rest_fhir_api' => [
            'required_value' => '1',
            'description' => 'Enable OpenEMR Standard FHIR REST API',
        ],
        'gbl_auth_hash_algo' => [
            'required_value' => 'SHA512',
            'description' => 'Hash Algorithm for Authentication',
        ],
        'enable_auditlog_encryption' => [
            'required_value' => '1',
            'description' => 'Enable Audit Log Encryption',
        ],
    ];

    /**
     * Check if preview mode is enabled (shows mock data for UI testing)
     */
    public function isPreviewMode(): bool
    {
        return $this->configAccessor->getBoolean(self::CONFIG_PREVIEW_MODE, false);
    }

    /**
     * Check if organization info is complete (has required org info)
     */
    public function isConfigured(): bool
    {
        return $this->getOrgName() !== ''
            && $this->getOrgNpi() !== '';
    }

    /**
     * Get organization name (from primary business entity facility)
     */
    public function getOrgName(): string
    {
        $facility = $this->getPrimaryFacility();
        $name = $facility['name'] ?? '';
        return is_string($name) ? $name : '';
    }

    /**
     * Get organization location/address (from primary business entity facility)
     */
    public function getOrgLocation(): string
    {
        $facility = $this->getPrimaryFacility();
        if ($facility === null) {
            return '';
        }

        $street = $facility['street'] ?? '';
        $city = $facility['city'] ?? '';
        $state = $facility['state'] ?? '';
        $postalCode = $facility['postal_code'] ?? '';

        $parts = array_filter([
            is_string($street) ? $street : '',
            is_string($city) ? $city : '',
            is_string($state) ? $state : '',
            is_string($postalCode) ? $postalCode : '',
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get organization NPI number (from primary business entity facility)
     */
    public function getOrgNpi(): string
    {
        $facility = $this->getPrimaryFacility();
        $npi = $facility['facility_npi'] ?? '';
        return is_string($npi) ? $npi : '';
    }

    /**
     * Get the primary business entity facility
     *
     * @return array<string, mixed>|null
     */
    private function getPrimaryFacility(): ?array
    {
        if ($this->primaryFacilityQueried) {
            return $this->primaryFacility;
        }

        $this->primaryFacilityQueried = true;

        $facilityService = new FacilityService();
        $facility = $facilityService->getPrimaryBusinessEntity();

        if (!is_array($facility)) {
            return null;
        }

        /** @var array<string, mixed> $facility */
        $this->primaryFacility = $facility;

        return $this->primaryFacility;
    }

    /**
     * Get FHIR endpoint URL (auto-detected from site_addr_oath)
     */
    public function getFhirEndpoint(): string
    {
        $siteAddr = $this->configAccessor->getString('site_addr_oath', '');
        if ($siteAddr === '') {
            return '';
        }
        return rtrim($siteAddr, '/') . '/apis/default/fhir/r4';
    }

    /**
     * Get OpenEMR webroot path
     */
    public function getWebroot(): string
    {
        return $this->configAccessor->getString('webroot', '');
    }

    /**
     * Get the module's public assets path (relative to webroot)
     */
    public function getModuleAssetsPath(): string
    {
        return '/interface/modules/custom_modules/oce-module-onc-registration/public/assets';
    }

    /**
     * Get assets static relative path
     */
    public function getAssetsStaticRelative(): string
    {
        return $this->configAccessor->getString('assets_static_relative', '');
    }

    /**
     * Get value of an OpenEMR global setting
     */
    public function getGlobalValue(string $key): string
    {
        return $this->configAccessor->getString($key, '');
    }

    /**
     * Get OpenEMR version as "major.minor.patch" (e.g., "7.0.3")
     *
     * Falls back to "7.0.3" if version cannot be determined.
     */
    public function getOpenEmrVersion(): string
    {
        global $v_major, $v_minor, $v_patch;

        if (
            isset($v_major, $v_minor, $v_patch)
            && is_scalar($v_major)
            && is_scalar($v_minor)
            && is_scalar($v_patch)
        ) {
            return $v_major . '.' . $v_minor . '.' . $v_patch;
        }

        // Fallback for testing or if globals not loaded
        return '7.0.3';
    }

    /**
     * Get OpenEMR major.minor version for wiki URLs (e.g., "7.0.3" or "8.0.0")
     *
     * Normalizes patch versions (7.0.3.1 -> 7.0.3) for wiki page URLs.
     */
    public function getOpenEmrWikiVersion(): string
    {
        $version = $this->getOpenEmrVersion();

        // Extract major.minor.patch (ignore any additional segments like 7.0.3.1)
        if (preg_match('/^(\d+\.\d+\.\d+)/', $version, $matches)) {
            return $matches[1];
        }

        return $version;
    }
}
