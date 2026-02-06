<?php

/**
 * Unit tests for NpiValidator
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Tests\Unit;

use OpenCoreEMR\Modules\OncRegistration\Service\NpiValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NpiValidator::class)]
class NpiValidatorTest extends TestCase
{
    private NpiValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new NpiValidator();
    }

    public function testValidNpiReturnsValid(): void
    {
        // 1234567893 is a valid NPI (passes Luhn check with 80840 prefix)
        $result = $this->validator->validate('1234567893');

        self::assertTrue($result['valid']);
        self::assertNull($result['error']);
    }

    public function testValidNpiWithSpacesReturnsValid(): void
    {
        $result = $this->validator->validate('123 456 7893');

        self::assertTrue($result['valid']);
        self::assertNull($result['error']);
    }

    public function testValidNpiWithDashesReturnsValid(): void
    {
        $result = $this->validator->validate('123-456-7893');

        self::assertTrue($result['valid']);
        self::assertNull($result['error']);
    }

    public function testValidNpiWithMixedWhitespaceReturnsValid(): void
    {
        $result = $this->validator->validate('  123-456 7893  ');

        self::assertTrue($result['valid']);
        self::assertNull($result['error']);
    }

    public function testTooShortNpiReturnsError(): void
    {
        $result = $this->validator->validate('123456789');

        self::assertFalse($result['valid']);
        self::assertSame('NPI must be exactly 10 digits', $result['error']);
    }

    public function testTooLongNpiReturnsError(): void
    {
        $result = $this->validator->validate('12345678901');

        self::assertFalse($result['valid']);
        self::assertSame('NPI must be exactly 10 digits', $result['error']);
    }

    public function testEmptyNpiReturnsError(): void
    {
        $result = $this->validator->validate('');

        self::assertFalse($result['valid']);
        self::assertSame('NPI must be exactly 10 digits', $result['error']);
    }

    public function testNpiWithLettersReturnsError(): void
    {
        $result = $this->validator->validate('123456789A');

        self::assertFalse($result['valid']);
        self::assertSame('NPI must contain only digits', $result['error']);
    }

    public function testNpiWithSpecialCharactersReturnsError(): void
    {
        // Note: dashes and spaces are stripped, but other special chars remain
        $result = $this->validator->validate('123456789!');

        self::assertFalse($result['valid']);
        self::assertSame('NPI must contain only digits', $result['error']);
    }

    public function testInvalidCheckDigitReturnsError(): void
    {
        // 1234567890 has an invalid check digit
        $result = $this->validator->validate('1234567890');

        self::assertFalse($result['valid']);
        self::assertSame('Invalid NPI check digit', $result['error']);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validNpiProvider(): array
    {
        // These NPIs pass the Luhn check with prefix 80840
        return [
            'standard valid NPI' => ['1234567893'],
            'valid with spaces' => ['1234 5678 93'],
            'valid with dashes' => ['123-456-7893'],
        ];
    }

    #[DataProvider('validNpiProvider')]
    public function testValidNpisPassValidation(string $npi): void
    {
        $result = $this->validator->validate($npi);

        self::assertTrue($result['valid'], "Expected NPI {$npi} to be valid");
        self::assertNull($result['error']);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidNpiProvider(): array
    {
        return [
            'too short' => ['123456789', 'NPI must be exactly 10 digits'],
            'too long' => ['12345678901', 'NPI must be exactly 10 digits'],
            'contains letters' => ['123456789A', 'NPI must contain only digits'],
            'all letters' => ['ABCDEFGHIJ', 'NPI must contain only digits'],
            'bad check digit' => ['1234567890', 'Invalid NPI check digit'],
            'all zeros' => ['0000000000', 'Invalid NPI check digit'],
            'all ones' => ['1111111111', 'Invalid NPI check digit'],
        ];
    }

    #[DataProvider('invalidNpiProvider')]
    public function testInvalidNpisFailValidation(string $npi, string $expectedError): void
    {
        $result = $this->validator->validate($npi);

        self::assertFalse($result['valid'], "Expected NPI {$npi} to be invalid");
        self::assertSame($expectedError, $result['error']);
    }
}
