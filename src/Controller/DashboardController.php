<?php

/**
 * Dashboard controller for ONC Registration module
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration\Controller;

use OpenCoreEMR\Modules\OncRegistration\Exception\OncRegistrationAccessDeniedException;
use OpenCoreEMR\Modules\OncRegistration\Exception\OncRegistrationValidationException;
use OpenCoreEMR\Modules\OncRegistration\GlobalConfig;
use OpenCoreEMR\Modules\OncRegistration\Service\ConfigurationValidator;
use OpenCoreEMR\Modules\OncRegistration\Service\NpiValidator;
use OpenCoreEMR\Modules\OncRegistration\Service\RegistrationService;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class DashboardController
{
    private readonly SystemLogger $logger;

    public function __construct(
        private readonly GlobalConfig $config,
        private readonly ConfigurationValidator $configValidator,
        private readonly RegistrationService $registrationService,
        private readonly NpiValidator $npiValidator,
        private readonly Environment $twig
    ) {
        $this->logger = new SystemLogger();
    }

    /**
     * Dispatch action to appropriate method
     *
     * @param array<string, mixed> $params Request parameters
     */
    public function dispatch(string $action, array $params = []): Response
    {
        return match ($action) {
            'validate-npi' => $this->validateNpi($params),
            default => $this->showDashboard(),
        };
    }

    /**
     * Show the main dashboard
     */
    private function showDashboard(): Response
    {
        $this->logger->debug('Showing ONC Registration dashboard');

        // Preview mode shows mock data for UI testing
        if ($this->config->isPreviewMode()) {
            return $this->showPreviewDashboard();
        }

        // Get configuration validation results
        $configValidation = $this->configValidator->validateRequiredSettings();
        $validationSummary = $this->configValidator->getValidationSummary();

        // Get registration info status
        $registrationInfo = $this->registrationService->checkRegistrationInfo();

        // Check if already registered on published URLs page
        $registrationVerification = $this->registrationService->verifyRegistration();

        // Validate NPI if provided
        $npiValidation = null;
        $npi = $this->config->getOrgNpi();
        if ($npi !== '') {
            $npiValidation = $this->npiValidator->validate($npi);
        }

        $content = $this->twig->render('dashboard/index.html.twig', [
            'title' => 'ONC Registration',
            'csrf_token' => CsrfUtils::collectCsrfToken(),
            'webroot' => $this->config->getWebroot(),

            // Configuration validation
            'config_validation' => $configValidation,
            'validation_summary' => $validationSummary,
            'all_settings_valid' => $this->configValidator->allSettingsValid(),

            // Organization info (auto-detected)
            'org_name' => $this->config->getOrgName(),
            'org_location' => $this->config->getOrgLocation(),
            'org_npi' => $npi,
            'fhir_endpoint' => $this->config->getFhirEndpoint(),
            'registration_info' => $registrationInfo,
            'registration_verification' => $registrationVerification,
            'npi_validation' => $npiValidation,

            // Email generation
            'mailto_link' => $this->registrationService->generateMailtoLink(),
            'email_body' => $this->registrationService->generateEmailBody(),
            'registration_email' => RegistrationService::REGISTRATION_EMAIL,
            'registration_subject' => RegistrationService::REGISTRATION_SUBJECT,
            'published_urls_page' => $this->registrationService->getPublishedUrlsPage(),
        ]);

        return new Response($content);
    }

    /**
     * Show dashboard with mock data for UI preview
     */
    private function showPreviewDashboard(): Response
    {
        $this->logger->debug('Showing ONC Registration dashboard in preview mode');

        $mockNpi = '1234567893';
        $mockFhirEndpoint = 'https://emr.example.com/apis/default/fhir/r4';

        // Mock all settings as valid
        $configValidation = [];
        foreach (GlobalConfig::REQUIRED_GLOBALS as $key => $setting) {
            $configValidation[$key] = [
                'description' => $setting['description'],
                'required' => $setting['required_value'],
                'actual' => $setting['required_value'],
                'passed' => true,
            ];
        }

        $mockEmailBody = <<<EMAIL
Organization Name: Acme Medical Center

Organization Location: 123 Healthcare Blvd, Springfield, IL 62701

Organization NPI: {$mockNpi}

FHIR Endpoint URL: {$mockFhirEndpoint}

---
Submitted via ONC Registration Module
EMAIL;

        $content = $this->twig->render('dashboard/index.html.twig', [
            'title' => 'ONC Registration (Preview)',
            'csrf_token' => CsrfUtils::collectCsrfToken(),
            'webroot' => $this->config->getWebroot(),

            // All config valid
            'config_validation' => $configValidation,
            'validation_summary' => ['total' => 4, 'passed' => 4, 'failed' => 0],
            'all_settings_valid' => true,

            // Mock organization info
            'org_name' => 'Acme Medical Center',
            'org_location' => '123 Healthcare Blvd, Springfield, IL 62701',
            'org_npi' => $mockNpi,
            'fhir_endpoint' => $mockFhirEndpoint,
            'registration_info' => ['complete' => true, 'missing' => []],
            'registration_verification' => ['registered' => true, 'error' => null],
            'npi_validation' => ['valid' => true, 'npi' => $mockNpi],

            // Mock email
            'mailto_link' => 'mailto:' . RegistrationService::REGISTRATION_EMAIL
                . '?subject=' . rawurlencode(RegistrationService::REGISTRATION_SUBJECT)
                . '&body=' . rawurlencode($mockEmailBody),
            'email_body' => $mockEmailBody,
            'registration_email' => RegistrationService::REGISTRATION_EMAIL,
            'registration_subject' => RegistrationService::REGISTRATION_SUBJECT,
            'published_urls_page' => $this->registrationService->getPublishedUrlsPage(),
        ]);

        return new Response($content);
    }

    /**
     * Validate NPI via AJAX
     *
     * @param array<string, mixed> $params
     */
    private function validateNpi(array $params): Response
    {
        $csrfToken = $params['csrf_token'] ?? '';
        if (!CsrfUtils::verifyCsrfToken($csrfToken)) {
            throw new OncRegistrationAccessDeniedException('CSRF token verification failed');
        }

        $npi = $params['npi'] ?? '';
        if (!is_string($npi) || $npi === '') {
            throw new OncRegistrationValidationException('NPI is required');
        }

        $result = $this->npiValidator->validate($npi);

        return new Response(
            json_encode($result, JSON_THROW_ON_ERROR),
            200,
            ['Content-Type' => 'application/json']
        );
    }
}
