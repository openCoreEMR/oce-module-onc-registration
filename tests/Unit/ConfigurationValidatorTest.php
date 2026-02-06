<?php

/**
 * Unit tests for ConfigurationValidator
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Tests\Unit;

use OpenCoreEMR\Modules\OncRegistration\GlobalConfig;
use OpenCoreEMR\Modules\OncRegistration\Service\ConfigurationValidator;
use OpenCoreEMR\Modules\OncRegistration\Tests\Mocks\MockGlobalsAccessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationValidator::class)]
class ConfigurationValidatorTest extends TestCase
{
    private MockGlobalsAccessor $mockAccessor;

    protected function setUp(): void
    {
        $this->mockAccessor = new MockGlobalsAccessor();
    }

    public function testAllSettingsValidWhenAllRequiredSettingsMatch(): void
    {
        // Set all required globals to their required values
        $this->mockAccessor->set('rest_fhir_api', '1');
        $this->mockAccessor->set('gbl_auth_hash_algo', 'SHA512');
        $this->mockAccessor->set('enable_auditlog_encryption', '1');

        $config = new GlobalConfig($this->mockAccessor);
        $validator = new ConfigurationValidator($config);

        self::assertTrue($validator->allSettingsValid());
    }

    public function testAllSettingsInvalidWhenNoSettingsConfigured(): void
    {
        $config = new GlobalConfig($this->mockAccessor);
        $validator = new ConfigurationValidator($config);

        self::assertFalse($validator->allSettingsValid());
    }

    public function testAllSettingsInvalidWhenOneSettingMissing(): void
    {
        // Set 2 of 3 required settings
        $this->mockAccessor->set('rest_fhir_api', '1');
        $this->mockAccessor->set('gbl_auth_hash_algo', 'SHA512');
        // Missing: enable_auditlog_encryption

        $config = new GlobalConfig($this->mockAccessor);
        $validator = new ConfigurationValidator($config);

        self::assertFalse($validator->allSettingsValid());
    }

    public function testAllSettingsInvalidWhenSettingHasWrongValue(): void
    {
        $this->mockAccessor->set('rest_fhir_api', '1');
        $this->mockAccessor->set('gbl_auth_hash_algo', 'SHA256'); // Wrong value
        $this->mockAccessor->set('enable_auditlog_encryption', '1');

        $config = new GlobalConfig($this->mockAccessor);
        $validator = new ConfigurationValidator($config);

        self::assertFalse($validator->allSettingsValid());
    }

    public function testValidateRequiredSettingsReturnsCorrectStructure(): void
    {
        $this->mockAccessor->set('rest_fhir_api', '1');

        $config = new GlobalConfig($this->mockAccessor);
        $validator = new ConfigurationValidator($config);

        $results = $validator->validateRequiredSettings();

        self::assertArrayHasKey('rest_fhir_api', $results);
        self::assertArrayHasKey('gbl_auth_hash_algo', $results);
        self::assertArrayHasKey('enable_auditlog_encryption', $results);

        // Check structure of a single result
        $fhirResult = $results['rest_fhir_api'];
        self::assertArrayHasKey('setting', $fhirResult);
        self::assertArrayHasKey('description', $fhirResult);
        self::assertArrayHasKey('required', $fhirResult);
        self::assertArrayHasKey('actual', $fhirResult);
        self::assertArrayHasKey('passed', $fhirResult);
    }

    public function testValidateRequiredSettingsShowsPassedAndFailed(): void
    {
        // One setting correct, others missing
        $this->mockAccessor->set('rest_fhir_api', '1');

        $config = new GlobalConfig($this->mockAccessor);
        $validator = new ConfigurationValidator($config);

        $results = $validator->validateRequiredSettings();

        // FHIR API should pass
        self::assertTrue($results['rest_fhir_api']['passed']);
        self::assertSame('1', $results['rest_fhir_api']['actual']);

        // Others should fail
        self::assertFalse($results['gbl_auth_hash_algo']['passed']);
        self::assertSame('', $results['gbl_auth_hash_algo']['actual']);
    }

    public function testGetValidationSummaryCountsCorrectly(): void
    {
        // Set 2 of 3 required settings correctly
        $this->mockAccessor->set('rest_fhir_api', '1');
        $this->mockAccessor->set('gbl_auth_hash_algo', 'SHA512');

        $config = new GlobalConfig($this->mockAccessor);
        $validator = new ConfigurationValidator($config);

        $summary = $validator->getValidationSummary();

        self::assertSame(2, $summary['passed']);
        self::assertSame(1, $summary['failed']);
        self::assertSame(3, $summary['total']);
    }

    public function testGetValidationSummaryAllPassed(): void
    {
        $this->mockAccessor->set('rest_fhir_api', '1');
        $this->mockAccessor->set('gbl_auth_hash_algo', 'SHA512');
        $this->mockAccessor->set('enable_auditlog_encryption', '1');

        $config = new GlobalConfig($this->mockAccessor);
        $validator = new ConfigurationValidator($config);

        $summary = $validator->getValidationSummary();

        self::assertSame(3, $summary['passed']);
        self::assertSame(0, $summary['failed']);
        self::assertSame(3, $summary['total']);
    }

    public function testGetValidationSummaryAllFailed(): void
    {
        $config = new GlobalConfig($this->mockAccessor);
        $validator = new ConfigurationValidator($config);

        $summary = $validator->getValidationSummary();

        self::assertSame(0, $summary['passed']);
        self::assertSame(3, $summary['failed']);
        self::assertSame(3, $summary['total']);
    }
}
