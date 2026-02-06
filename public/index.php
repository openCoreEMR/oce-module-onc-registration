<?php

// phpcs:disable PSR1.Files.SideEffects

/**
 * Main interface for the ONC Registration module
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

$sessionAllowWrite = true;

// Load module autoloader before globals.php so our classes are available
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../../../../globals.php';

use OpenCoreEMR\Modules\OncRegistration\Bootstrap;
use OpenCoreEMR\Modules\OncRegistration\ConfigFactory;
use OpenCoreEMR\Modules\OncRegistration\Exception\OncRegistrationHttpExceptionInterface;
use OpenCoreEMR\Modules\OncRegistration\GlobalsAccessor;
use OpenCoreEMR\Modules\OncRegistration\ModuleAccessGuard;
use OpenEMR\Common\Logging\SystemLogger;
use Symfony\Component\HttpFoundation\Response;

// Check if module is installed and enabled - return 404 if not
$guardResponse = ModuleAccessGuard::check(Bootstrap::MODULE_NAME);
if ($guardResponse instanceof Response) {
    $guardResponse->send();
    return;
}

run();

/**
 * Main entry logic
 */
function run(): void
{
    $globalsAccessor = new GlobalsAccessor();
    $kernel = $globalsAccessor->get('kernel');
    if (!$kernel instanceof \OpenEMR\Core\Kernel) {
        throw new \RuntimeException('OpenEMR Kernel not available');
    }
    $configAccessor = ConfigFactory::createConfigAccessor();
    $bootstrap = new Bootstrap($kernel->getEventDispatcher(), $kernel, $configAccessor);

    $controller = $bootstrap->getDashboardController();

    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $actionParam = $_GET['action'] ?? ($requestMethod === 'POST' ? ($_POST['action'] ?? 'dashboard') : 'dashboard');
    $action = is_string($actionParam) ? $actionParam : 'dashboard';

    /** @var array<string, mixed> $params */
    $params = $requestMethod === 'POST' ? array_merge($_GET, $_POST) : $_GET;
    $params['_self'] = $_SERVER['PHP_SELF'] ?? '/';

    $logger = new SystemLogger();

    try {
        $response = $controller->dispatch($action, $params);
        $response->send();
    } catch (OncRegistrationHttpExceptionInterface $e) {
        $logger->error('ONC Registration module error', ['exception' => $e]);
        $response = Bootstrap::createErrorResponse($e->getStatusCode(), $kernel, $bootstrap->getWebroot());
        $response->send();
    } catch (\Throwable $e) {
        $logger->error('ONC Registration unexpected error', ['exception' => $e]);
        $response = Bootstrap::createErrorResponse(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $kernel,
            $bootstrap->getWebroot()
        );
        $response->send();
    }
}
