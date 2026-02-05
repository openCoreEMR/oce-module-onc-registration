<?php

/**
 * Validates OpenEMR configuration for ONC certification requirements.
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Service;

use OpenCoreEMR\Modules\OncRegistration\GlobalConfig;

class ConfigurationValidator
{
    public function __construct(
        private readonly GlobalConfig $config
    ) {
    }

    /**
     * Validate all required OpenEMR settings for ONC certification
     *
     * @return array<string, array{
     *     setting: string,
     *     description: string,
     *     required: string,
     *     actual: string,
     *     passed: bool
     * }>
     */
    public function validateRequiredSettings(): array
    {
        $results = [];

        foreach (GlobalConfig::REQUIRED_GLOBALS as $setting => $requirement) {
            $actualValue = $this->config->getGlobalValue($setting);
            $passed = $actualValue === $requirement['required_value'];

            $results[$setting] = [
                'setting' => $setting,
                'description' => $requirement['description'],
                'required' => $requirement['required_value'],
                'actual' => $actualValue,
                'passed' => $passed,
            ];
        }

        return $results;
    }

    /**
     * Check if all required settings pass validation
     */
    public function allSettingsValid(): bool
    {
        $results = $this->validateRequiredSettings();

        foreach ($results as $result) {
            if (!$result['passed']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get count of passing and failing settings
     *
     * @return array{passed: int, failed: int, total: int}
     */
    public function getValidationSummary(): array
    {
        $results = $this->validateRequiredSettings();
        $passed = 0;
        $failed = 0;

        foreach ($results as $result) {
            if ($result['passed']) {
                $passed++;
            } else {
                $failed++;
            }
        }

        return [
            'passed' => $passed,
            'failed' => $failed,
            'total' => $passed + $failed,
        ];
    }
}
