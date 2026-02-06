<?php

/**
 * Validates National Provider Identifier (NPI) numbers.
 *
 * NPI is a 10-digit number with a Luhn check digit.
 *
 * Note: Replace with OpenEMR\Common\Utils\ValidationUtils::validateNPI() when
 * minimum OpenEMR version includes it (introduced in master, not yet in a release).
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Service;

class NpiValidator
{
    /**
     * Validate an NPI number
     *
     * @return array{valid: bool, error: string|null}
     */
    public function validate(string $npi): array
    {
        // Remove any spaces or dashes (cast to string for PHPStan; null only occurs on PCRE error)
        $npi = (string) preg_replace('/[\s\-]/', '', $npi);

        // Check length
        if (strlen($npi) !== 10) {
            return ['valid' => false, 'error' => 'NPI must be exactly 10 digits'];
        }

        // Check all digits
        if (!ctype_digit($npi)) {
            return ['valid' => false, 'error' => 'NPI must contain only digits'];
        }

        // Validate Luhn check digit
        // For NPI, the prefix "80840" is prepended before calculating
        if (!$this->validateLuhn('80840' . $npi)) {
            return ['valid' => false, 'error' => 'Invalid NPI check digit'];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Validate using Luhn algorithm
     */
    private function validateLuhn(string $number): bool
    {
        $sum = 0;
        $length = strlen($number);
        $parity = $length % 2;

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];

            if (($i % 2) === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return ($sum % 10) === 0;
    }
}
