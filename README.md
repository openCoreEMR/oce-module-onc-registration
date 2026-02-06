# ONC Registration Module for OpenEMR

An OpenEMR module that helps healthcare organizations register their installation for ONC certification compliance.

## Features

- **Registration Status Verification**: Automatically checks if your FHIR endpoint is listed on the OpenEMR Foundation's published Service Base URLs page
- **Configuration Validation**: Verifies that required OpenEMR global settings are properly configured for ONC certification
- **Organization Info Management**: Collects and validates organization details (name, location, NPI, FHIR endpoint)
- **NPI Validation**: Validates National Provider Identifier numbers using the Luhn algorithm
- **FHIR Endpoint Detection**: Automatically detects the FHIR API endpoint from OpenEMR configuration
- **Registration Email Generation**: Creates pre-filled registration emails for the OpenEMR Foundation
- **Preview Mode**: View the dashboard with mock data showing a "registration complete" state for UI testing
- **Collapsible Dashboard**: All sections are collapsible; cards auto-collapse when registration is verified

## Requirements

- OpenEMR 7.0.3.4 or later
- PHP 8.2 or later

## Installation

### Via Composer (Recommended)

```bash
cd /path/to/openemr
composer require opencoreemr/oce-module-onc-registration
```

### Manual Installation

1. Copy this module to `interface/modules/custom_modules/oce-module-onc-registration/`
2. Navigate to **Administration > Modules > Manage Modules**
3. Click **Register**, then **Install**, then **Enable**

## Configuration

### Via Admin UI

After enabling the module:

1. Go to **Administration > Globals > ONC Registration**
2. Configure module settings:
   - **Preview Mode**: Enable to show mock data for UI testing

Organization details are configured in **Administration > Globals > Features**:
- Organization Name
- Organization Location (address)
- Organization NPI (10-digit number)

The FHIR endpoint is auto-detected from the Site Address setting.

### Via Environment Variables

For container deployments, configure via environment variables:

```bash
# Enable environment config mode (disables admin UI editing)
export OCE_ONC_REGISTRATION_ENV_CONFIG=1

# Module settings
export OCE_ONC_REGISTRATION_PREVIEW=true   # Enable preview mode
```

When `OCE_ONC_REGISTRATION_ENV_CONFIG=1` is set, the admin UI displays "This module is configured via environment variables" instead of editable fields.

## Usage

Access the module via **Administration > ONC Registration** in the OpenEMR menu.

The dashboard displays five collapsible cards:

1. **Registration Status**: Shows whether your installation is registered
   - Verified: Your FHIR endpoint appears on the published URLs page
   - Ready to Submit: All requirements met, ready to register
   - Not Ready: Configuration incomplete

2. **About ONC Certification**: Overview of certification requirements

3. **Configuration Checklist**: Verifies required OpenEMR settings
   - FHIR REST API enabled
   - Hash algorithms set to SHA512
   - Audit log encryption enabled

4. **Organization Information**: Your registration details with NPI validation

5. **Submit Registration**: Tools to send registration to OpenEMR Foundation
   - One-click email generation
   - Copy/paste option for manual submission

When registration is verified, all cards except Registration Status auto-collapse.

## ONC Certification Requirements

For an OpenEMR installation to use the product's ONC certification:

1. Configure required global settings (shown in checklist)
2. Ensure FHIR endpoint is publicly accessible via HTTPS
3. Register with the OpenEMR Foundation
4. Use AES-encrypted drives on end-user devices
5. Use FIPS-compliant SSL/TLS ciphers
6. Support NTP v4 (RFC 5905) for time synchronization

See the [OpenEMR ONC Certification Requirements](https://www.open-emr.org/wiki/index.php/OpenEMR_7.0.3_ONC_Ambulatory_EHR_Certification_Requirements) wiki page for full details. The module automatically detects your OpenEMR version and links to the appropriate documentation.

## Development

```bash
# Install dependencies
composer install

# Run code quality checks
composer check

# Run PHPStan at level 10
composer phpstan

# Run tests
composer test
```

## Support

- Issues: https://github.com/opencoreemr/oce-module-onc-registration/issues
- Email: support@opencoreemr.com

## License

GNU General Public License v3.0
