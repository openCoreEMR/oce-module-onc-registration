<?php

/**
 * Mock EnvironmentConfigAccessor for testing
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Tests\Mocks;

use OpenCoreEMR\Modules\OncRegistration\ConfigAccessorInterface;
use OpenEMR\Core\Kernel;

/**
 * Mock implementation of ConfigAccessorInterface for testing environment config mode
 */
class MockEnvironmentConfigAccessor implements ConfigAccessorInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function getString(string $key, string $default = ''): string
    {
        $value = $this->data[$key] ?? $default;
        return is_string($value) ? $value : (is_scalar($value) ? (string)$value : $default);
    }

    public function getBoolean(string $key, bool $default = false): bool
    {
        $value = $this->data[$key] ?? $default;
        if (is_bool($value)) {
            return $value;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->data[$key] ?? $default;
        return is_numeric($value) ? (int)$value : $default;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Set a value for testing
     */
    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Get the OpenEMR Kernel instance
     *
     * Returns null since environment config mode doesn't provide a real Kernel.
     * Tests that need a Kernel should mock it via the data array.
     */
    public function getKernel(): ?Kernel
    {
        $kernel = $this->get('kernel');
        return $kernel instanceof Kernel ? $kernel : null;
    }
}
