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

        // Get configuration validation results
        $configValidation = $this->configValidator->validateRequiredSettings();
        $validationSummary = $this->configValidator->getValidationSummary();

        // Get registration info status
        $registrationInfo = $this->registrationService->checkRegistrationInfo();

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

            // Registration info
            'org_name' => $this->config->getOrgName(),
            'org_location' => $this->config->getOrgLocation(),
            'org_npi' => $npi,
            'fhir_endpoint' => $this->config->getFhirEndpoint(),
            'detected_fhir_endpoint' => $this->config->detectFhirEndpoint(),
            'registration_info' => $registrationInfo,
            'npi_validation' => $npiValidation,

            // Registration status
            'registration_date' => $this->config->getRegistrationDate(),
            'registration_status' => $this->config->getRegistrationStatus(),

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
