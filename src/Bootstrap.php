<?php

/**
 * Bootstrap class for the ONC Registration module
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration;

use OpenCoreEMR\Modules\OncRegistration\Controller\DashboardController;
use OpenCoreEMR\Modules\OncRegistration\Service\ConfigurationValidator;
use OpenCoreEMR\Modules\OncRegistration\Service\NpiValidator;
use OpenCoreEMR\Modules\OncRegistration\Service\RegistrationService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Services\Globals\GlobalSetting;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

class Bootstrap
{
    public const MODULE_NAME = 'oce-module-onc-registration';

    private readonly GlobalConfig $globalsConfig;
    private readonly \Twig\Environment $twig;
    private readonly SystemLogger $logger;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Kernel $kernel = new Kernel(),
        ?ConfigAccessorInterface $configAccessor = null
    ) {
        $configAccessor ??= ConfigFactory::createConfigAccessor();
        $this->globalsConfig = new GlobalConfig($configAccessor);

        $templatePath = \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
        $twig = new TwigContainer($templatePath, $this->kernel);
        $this->twig = $twig->getTwig();

        $this->logger = new SystemLogger();
        $this->logger->debug('ONC Registration module Bootstrap constructed');
    }

    /**
     * Subscribe to OpenEMR events
     */
    public function subscribeToEvents(): void
    {
        $this->addGlobalSettings();
        $this->addMenuItems();

        if (!$this->globalsConfig->isConfigured()) {
            $this->logger->debug('ONC Registration module is not configured.');
            return;
        }

        if (!$this->globalsConfig->isEnabled()) {
            $this->logger->debug('ONC Registration module is disabled.');
            return;
        }

        $this->logger->debug('ONC Registration module is enabled and configured');
    }

    /**
     * Register global settings for the module
     */
    public function addGlobalSettings(): void
    {
        $this->eventDispatcher->addListener(
            GlobalsInitializedEvent::EVENT_HANDLE,
            $this->addGlobalSettingsSection(...)
        );
    }

    /**
     * Add global settings section to OpenEMR administration
     */
    public function addGlobalSettingsSection(GlobalsInitializedEvent $event): void
    {
        $service = $event->getGlobalsService();
        $section = xlt('ONC Registration');
        $service->createSection($section);

        if ($this->globalsConfig->isEnvConfigMode()) {
            $setting = new GlobalSetting(
                xlt('Configuration Managed Externally'),
                GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION,
                '',
                '',
                false
            );
            $setting->addFieldOption(
                GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK,
                static fn() => xlt(
                    'This module is configured via environment variables by deployment administrators.'
                )
            );
            $service->appendToSection($section, 'oce_onc_registration_env_config_notice', $setting);
            return;
        }

        $settings = $this->globalsConfig->getGlobalSettingSectionConfiguration();

        foreach ($settings as $key => $config) {
            $service->appendToSection(
                $section,
                $key,
                new GlobalSetting(
                    xlt($config['title']),
                    $config['type'],
                    $config['default'],
                    xlt($config['description']),
                    true
                )
            );
        }
    }

    /**
     * Register menu items for the module
     */
    public function addMenuItems(): void
    {
        $this->eventDispatcher->addListener(
            MenuEvent::MENU_UPDATE,
            $this->addModuleMenuItem(...)
        );
    }

    /**
     * Add module menu item to OpenEMR menu
     */
    public function addModuleMenuItem(MenuEvent $event): void
    {
        if (!$this->globalsConfig->isEnabled()) {
            return;
        }

        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'onc-registration';
        $menuItem->menu_id = 'onc-registration';
        $menuItem->label = xlt('ONC Registration');
        $menuItem->url = '/interface/modules/custom_modules/' . self::MODULE_NAME . '/public/index.php';
        $menuItem->icon = 'fa-certificate';
        $menuItem->children = [];
        $menuItem->acl_req = ['admin', 'super'];

        foreach ($menu as $item) {
            if (!is_object($item) || !isset($item->menu_id, $item->children)) {
                continue;
            }
            if (!is_array($item->children)) {
                continue;
            }
            if ($item->menu_id === 'admimg') {
                $item->children[] = $menuItem;
                break;
            }
        }
    }

    /**
     * Get OpenEMR webroot path
     */
    public function getWebroot(): string
    {
        return $this->globalsConfig->getWebroot();
    }

    /**
     * Build a generic error response
     */
    public static function createErrorResponse(
        int $statusCode,
        Kernel $kernel,
        string $webroot = ''
    ): Response {
        $templatePath = \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
        $twigContainer = new TwigContainer($templatePath, $kernel);
        $twig = $twigContainer->getTwig();
        $content = $twig->render('error.html.twig', [
            'status_code' => $statusCode,
            'title' => $statusCode >= 500 ? 'Server Error' : 'Error',
            'webroot' => $webroot,
        ]);
        return new Response($content, $statusCode);
    }

    /**
     * Get DashboardController instance
     */
    public function getDashboardController(): DashboardController
    {
        return new DashboardController(
            $this->globalsConfig,
            new ConfigurationValidator($this->globalsConfig),
            new RegistrationService($this->globalsConfig),
            new NpiValidator(),
            $this->twig
        );
    }
}
