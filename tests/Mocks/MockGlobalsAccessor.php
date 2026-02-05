<?php

/**
 * Mock GlobalsAccessor for testing
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Tests\Mocks;

use OpenCoreEMR\Modules\OncRegistration\ConfigAccessorInterface;
use OpenCoreEMR\Modules\OncRegistration\GlobalsAccessor;
use OpenEMR\Core\Kernel;

class MockGlobalsAccessor extends GlobalsAccessor implements ConfigAccessorInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $mockData = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->mockData = $data;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->mockData[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param string $default
     */
    public function getString(string $key, string $default = ''): string
    {
        return (string)($this->mockData[$key] ?? $default);
    }

    /**
     * @param string $key
     * @param int $default
     */
    public function getInt(string $key, int $default = 0): int
    {
        return (int)($this->mockData[$key] ?? $default);
    }

    /**
     * @param string $key
     * @param bool $default
     */
    public function getBoolean(string $key, bool $default = false): bool
    {
        return (bool)($this->mockData[$key] ?? $default);
    }

    public function has(string $key): bool
    {
        return isset($this->mockData[$key]);
    }

    /**
     * Set a value for testing
     */
    public function set(string $key, mixed $value): void
    {
        $this->mockData[$key] = $value;
    }

    /**
     * Get the OpenEMR Kernel instance
     */
    public function getKernel(): ?Kernel
    {
        $kernel = $this->get('kernel');
        return $kernel instanceof Kernel ? $kernel : null;
    }
}
