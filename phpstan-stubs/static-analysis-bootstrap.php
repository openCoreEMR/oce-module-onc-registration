<?php

/**
 * PHPStan bootstrap: flag the OpenEMR core as running under static analysis.
 *
 * Several OpenEMR core files execute database queries at include time (e.g.
 * custom/code_types.inc.php, required by Services/BaseService.php). When
 * PHPStan reflects through an OpenEMR service the module uses (FacilityService
 * extends BaseService), the autoloader includes those files and the include-
 * time sqlStatement() call fatals because no DB connection exists during
 * analysis. The core guards every such call behind OPENEMR_STATIC_ANALYSIS;
 * defining it here (before tools/openemr's autoloader runs) takes that path.
 */

declare(strict_types=1);

if (!defined('OPENEMR_STATIC_ANALYSIS')) {
    define('OPENEMR_STATIC_ANALYSIS', true);
}
