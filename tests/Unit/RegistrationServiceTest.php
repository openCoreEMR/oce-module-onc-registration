<?php

/**
 * Unit tests for RegistrationService
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Tests\Unit;

use OpenCoreEMR\Modules\OncRegistration\GlobalConfig;
use OpenCoreEMR\Modules\OncRegistration\Service\RegistrationService;
use OpenCoreEMR\Modules\OncRegistration\Tests\Mocks\MockGlobalsAccessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegistrationService::class)]
class RegistrationServiceTest extends TestCase
{
    private MockGlobalsAccessor $mockAccessor;

    protected function setUp(): void
    {
        $this->mockAccessor = new MockGlobalsAccessor();
    }

    public function testGenerateEmailBodyIncludesAllOrganizationInfo(): void
    {
        $this->mockAccessor->set('site_addr_oath', 'https://emr.example.com');

        $config = $this->createMockGlobalConfig(
            'Acme Medical Center',
            '123 Main St, Springfield, IL 62701',
            '1234567893',
            'https://emr.example.com/apis/default/fhir/r4'
        );

        $service = new RegistrationService($config);
        $emailBody = $service->generateEmailBody();

        self::assertStringContainsString('Organization Name: Acme Medical Center', $emailBody);
        self::assertStringContainsString('Organization Location: 123 Main St, Springfield, IL 62701', $emailBody);
        self::assertStringContainsString('Organization NPI: 1234567893', $emailBody);
        self::assertStringContainsString('FHIR Endpoint URL: https://emr.example.com/apis/default/fhir/r4', $emailBody);
        self::assertStringContainsString('Submitted via ONC Registration Module', $emailBody);
    }

    public function testGenerateEmailBodyWithEmptyValues(): void
    {
        $config = $this->createMockGlobalConfig('', '', '', '');

        $service = new RegistrationService($config);
        $emailBody = $service->generateEmailBody();

        self::assertStringContainsString('Organization Name: ', $emailBody);
        self::assertStringContainsString('Organization Location: ', $emailBody);
        self::assertStringContainsString('Organization NPI: ', $emailBody);
        self::assertStringContainsString('FHIR Endpoint URL: ', $emailBody);
    }

    public function testGenerateMailtoLinkContainsCorrectEmail(): void
    {
        $config = $this->createMockGlobalConfig('Test Org', 'Test Location', '1234567893', 'https://test.com/fhir');

        $service = new RegistrationService($config);
        $mailtoLink = $service->generateMailtoLink();

        self::assertStringStartsWith('mailto:hello@open-emr.org', $mailtoLink);
        self::assertStringContainsString('subject=', $mailtoLink);
        self::assertStringContainsString('body=', $mailtoLink);
    }

    public function testGenerateMailtoLinkUrlEncodesSubject(): void
    {
        $config = $this->createMockGlobalConfig('Test Org', 'Test Location', '1234567893', 'https://test.com/fhir');

        $service = new RegistrationService($config);
        $mailtoLink = $service->generateMailtoLink();

        // Subject should be URL encoded
        self::assertStringContainsString('subject=ONC%20registration', $mailtoLink);
    }

    public function testCheckRegistrationInfoCompleteWhenAllFieldsPresent(): void
    {
        $config = $this->createMockGlobalConfig(
            'Acme Medical',
            '123 Main St',
            '1234567893',
            'https://emr.example.com/fhir'
        );

        $service = new RegistrationService($config);
        $result = $service->checkRegistrationInfo();

        self::assertTrue($result['complete']);
        self::assertEmpty($result['missing']);
    }

    public function testCheckRegistrationInfoIncompleteWhenOrgNameMissing(): void
    {
        $config = $this->createMockGlobalConfig(
            '', // Missing org name
            '123 Main St',
            '1234567893',
            'https://emr.example.com/fhir'
        );

        $service = new RegistrationService($config);
        $result = $service->checkRegistrationInfo();

        self::assertFalse($result['complete']);
        self::assertCount(1, $result['missing']);
        self::assertStringContainsString('Organization Name', $result['missing'][0]);
    }

    public function testCheckRegistrationInfoIncompleteWhenOrgLocationMissing(): void
    {
        $config = $this->createMockGlobalConfig(
            'Acme Medical',
            '', // Missing location
            '1234567893',
            'https://emr.example.com/fhir'
        );

        $service = new RegistrationService($config);
        $result = $service->checkRegistrationInfo();

        self::assertFalse($result['complete']);
        self::assertCount(1, $result['missing']);
        self::assertStringContainsString('Organization Location', $result['missing'][0]);
    }

    public function testCheckRegistrationInfoIncompleteWhenNpiMissing(): void
    {
        $config = $this->createMockGlobalConfig(
            'Acme Medical',
            '123 Main St',
            '', // Missing NPI
            'https://emr.example.com/fhir'
        );

        $service = new RegistrationService($config);
        $result = $service->checkRegistrationInfo();

        self::assertFalse($result['complete']);
        self::assertCount(1, $result['missing']);
        self::assertStringContainsString('Organization NPI', $result['missing'][0]);
    }

    public function testCheckRegistrationInfoIncompleteWhenFhirEndpointMissing(): void
    {
        $config = $this->createMockGlobalConfig(
            'Acme Medical',
            '123 Main St',
            '1234567893',
            '' // Missing FHIR endpoint
        );

        $service = new RegistrationService($config);
        $result = $service->checkRegistrationInfo();

        self::assertFalse($result['complete']);
        self::assertCount(1, $result['missing']);
        self::assertStringContainsString('FHIR Endpoint', $result['missing'][0]);
    }

    public function testCheckRegistrationInfoIncompleteWhenMultipleFieldsMissing(): void
    {
        $config = $this->createMockGlobalConfig('', '', '', '');

        $service = new RegistrationService($config);
        $result = $service->checkRegistrationInfo();

        self::assertFalse($result['complete']);
        self::assertCount(4, $result['missing']);
    }

    public function testGetPublishedUrlsPageReturnsUrl(): void
    {
        $config = $this->createMockGlobalConfig('', '', '', '');

        $service = new RegistrationService($config);
        $url = $service->getPublishedUrlsPage();

        self::assertStringStartsWith('https://', $url);
        self::assertStringContainsString('open-emr.org', $url);
    }

    public function testRegistrationEmailConstant(): void
    {
        self::assertSame('hello@open-emr.org', RegistrationService::REGISTRATION_EMAIL);
    }

    public function testRegistrationSubjectConstant(): void
    {
        self::assertSame('ONC registration', RegistrationService::REGISTRATION_SUBJECT);
    }

    public function testClearCacheResetsVerificationCache(): void
    {
        $config = $this->createMockGlobalConfig('Test', 'Location', '1234567893', '');

        $service = new RegistrationService($config);

        // First call caches the result
        $result1 = $service->verifyRegistration();
        self::assertSame('FHIR endpoint not configured', $result1['error']);

        // Clear cache
        $service->clearCache();

        // Second call should re-execute (still same result since config unchanged)
        $result2 = $service->verifyRegistration();
        self::assertSame('FHIR endpoint not configured', $result2['error']);
    }

    public function testVerifyRegistrationReturnsErrorWhenFhirEndpointNotConfigured(): void
    {
        $config = $this->createMockGlobalConfig('Test', 'Location', '1234567893', '');

        $service = new RegistrationService($config);
        $result = $service->verifyRegistration();

        self::assertFalse($result['registered']);
        self::assertSame('FHIR endpoint not configured', $result['error']);
    }

    /**
     * Create a mock GlobalConfig with specified values
     */
    private function createMockGlobalConfig(
        string $orgName,
        string $orgLocation,
        string $orgNpi,
        string $fhirEndpoint
    ): GlobalConfig {
        return new class ($orgName, $orgLocation, $orgNpi, $fhirEndpoint) extends GlobalConfig {
            public function __construct(
                private readonly string $mockOrgName,
                private readonly string $mockOrgLocation,
                private readonly string $mockOrgNpi,
                private readonly string $mockFhirEndpoint
            ) {
                // Don't call parent constructor to avoid database access
            }

            public function getOrgName(): string
            {
                return $this->mockOrgName;
            }

            public function getOrgLocation(): string
            {
                return $this->mockOrgLocation;
            }

            public function getOrgNpi(): string
            {
                return $this->mockOrgNpi;
            }

            public function getFhirEndpoint(): string
            {
                return $this->mockFhirEndpoint;
            }
        };
    }
}
