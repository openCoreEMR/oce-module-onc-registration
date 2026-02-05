<?php

/**
 * Manages the configuration options for the ONC Registration module.
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration;

use OpenEMR\Services\Globals\GlobalSetting;

class GlobalConfig
{
    private readonly bool $isEnvConfigMode;

    public function __construct(
        private readonly ConfigAccessorInterface $configAccessor = new GlobalsAccessor()
    ) {
        $this->isEnvConfigMode = $configAccessor instanceof EnvironmentConfigAccessor;
    }

    // Module configuration options
    public const CONFIG_OPTION_ENABLED = 'oce_onc_registration_enabled';

    // Organization registration info
    public const CONFIG_OPTION_ORG_NAME = 'oce_onc_registration_org_name';
    public const CONFIG_OPTION_ORG_LOCATION = 'oce_onc_registration_org_location';
    public const CONFIG_OPTION_ORG_NPI = 'oce_onc_registration_org_npi';
    public const CONFIG_OPTION_FHIR_ENDPOINT = 'oce_onc_registration_fhir_endpoint';

    // Registration status tracking
    public const CONFIG_OPTION_REGISTRATION_DATE = 'oce_onc_registration_date';
    public const CONFIG_OPTION_REGISTRATION_STATUS = 'oce_onc_registration_status';

    // Required OpenEMR global settings for ONC certification
    public const REQUIRED_GLOBALS = [
        'gbl_fhir_rest_api' => [
            'required_value' => '1',
            'description' => 'Enable OpenEMR Standard FHIR REST API',
        ],
        'oauth_hash_algo' => [
            'required_value' => 'SHA512',
            'description' => 'Hash Algorithm for Authentication',
        ],
        'oauth_token_hash_algo' => [
            'required_value' => 'SHA512',
            'description' => 'Hash Algorithm for Token',
        ],
        'enable_auditlog_encryption' => [
            'required_value' => '1',
            'description' => 'Enable Audit Log Encryption',
        ],
    ];

    /**
     * Check if configuration is managed via environment variables
     */
    public function isEnvConfigMode(): bool
    {
        return $this->isEnvConfigMode;
    }

    /**
     * Check if the module is enabled
     */
    public function isEnabled(): bool
    {
        return $this->configAccessor->getBoolean(self::CONFIG_OPTION_ENABLED, false);
    }

    /**
     * Check if the module is properly configured (has required org info)
     */
    public function isConfigured(): bool
    {
        return $this->getOrgName() !== ''
            && $this->getOrgNpi() !== ''
            && $this->getFhirEndpoint() !== '';
    }

    /**
     * Get organization name
     */
    public function getOrgName(): string
    {
        return $this->configAccessor->getString(self::CONFIG_OPTION_ORG_NAME, '');
    }

    /**
     * Get organization location/address
     */
    public function getOrgLocation(): string
    {
        return $this->configAccessor->getString(self::CONFIG_OPTION_ORG_LOCATION, '');
    }

    /**
     * Get organization NPI number
     */
    public function getOrgNpi(): string
    {
        return $this->configAccessor->getString(self::CONFIG_OPTION_ORG_NPI, '');
    }

    /**
     * Get FHIR endpoint URL
     */
    public function getFhirEndpoint(): string
    {
        $configured = $this->configAccessor->getString(self::CONFIG_OPTION_FHIR_ENDPOINT, '');
        if ($configured !== '') {
            return $configured;
        }
        // Auto-detect from OpenEMR configuration
        return $this->detectFhirEndpoint();
    }

    /**
     * Get registration submission date
     */
    public function getRegistrationDate(): string
    {
        return $this->configAccessor->getString(self::CONFIG_OPTION_REGISTRATION_DATE, '');
    }

    /**
     * Get registration status
     */
    public function getRegistrationStatus(): string
    {
        return $this->configAccessor->getString(self::CONFIG_OPTION_REGISTRATION_STATUS, '');
    }

    /**
     * Get OpenEMR webroot path
     */
    public function getWebroot(): string
    {
        return $this->configAccessor->getString('webroot', '');
    }

    /**
     * Get assets static relative path
     */
    public function getAssetsStaticRelative(): string
    {
        return $this->configAccessor->getString('assets_static_relative', '');
    }

    /**
     * Detect FHIR endpoint URL from OpenEMR configuration
     */
    public function detectFhirEndpoint(): string
    {
        $siteAddr = $this->configAccessor->getString('site_addr_oath', '');
        if ($siteAddr === '') {
            return '';
        }
        return rtrim($siteAddr, '/') . '/apis/default/fhir/r4';
    }

    /**
     * Get value of an OpenEMR global setting
     */
    public function getGlobalValue(string $key): string
    {
        return $this->configAccessor->getString($key, '');
    }

    /**
     * Get the global settings section configuration for the admin UI
     *
     * @return array<string, array<string, string|bool|int|array<string, string>>>
     */
    public function getGlobalSettingSectionConfiguration(): array
    {
        return [
            self::CONFIG_OPTION_ENABLED => [
                'title' => 'Enable ONC Registration Module',
                'description' => 'Enable the ONC Registration helper module',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => false,
            ],
            self::CONFIG_OPTION_ORG_NAME => [
                'title' => 'Organization Name',
                'description' => 'Legal name of the healthcare organization',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_OPTION_ORG_LOCATION => [
                'title' => 'Organization Location',
                'description' => 'Full address of the organization',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_OPTION_ORG_NPI => [
                'title' => 'Organization NPI',
                'description' => 'National Provider Identifier (10-digit number)',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_OPTION_FHIR_ENDPOINT => [
                'title' => 'FHIR Endpoint URL',
                'description' => 'FHIR API base URL (auto-detected if empty)',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_OPTION_REGISTRATION_DATE => [
                'title' => 'Registration Date',
                'description' => 'Date registration was submitted (auto-filled)',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
            self::CONFIG_OPTION_REGISTRATION_STATUS => [
                'title' => 'Registration Status',
                'description' => 'Current registration status',
                'type' => GlobalSetting::DATA_TYPE_TEXT,
                'default' => '',
            ],
        ];
    }
}
