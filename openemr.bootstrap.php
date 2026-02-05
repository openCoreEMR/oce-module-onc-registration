<?php

/**
 * Initializes the OpenEMR module
 *
 * This file is automatically loaded by OpenEMR when the module is enabled.
 * Update the namespace and class references to match your module name.
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\OncRegistration;

/**
 * @var \OpenEMR\Core\ModulesClassLoader $classLoader Injected by the OpenEMR module loader
 */
$classLoader->registerNamespaceIfNotExists(
    'OpenCoreEMR\\Modules\\OncRegistration\\',
    __DIR__ . DIRECTORY_SEPARATOR . 'src'
);

/**
 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
 *      Injected by the OpenEMR module loader
 */
$bootstrap = new Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();
