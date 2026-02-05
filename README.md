# ONC Registration Module for OpenEMR

An OpenEMR module that helps healthcare organizations register their installation for ONC certification compliance.

## Features

- **Configuration Validation**: Verifies that required OpenEMR global settings are properly configured for ONC certification
- **Organization Info Management**: Collects and validates organization details (name, location, NPI, FHIR endpoint)
- **NPI Validation**: Validates National Provider Identifier numbers using the Luhn algorithm
- **FHIR Endpoint Detection**: Automatically detects the FHIR API endpoint from OpenEMR configuration
- **Registration Email Generation**: Creates pre-filled registration emails for the OpenEMR Foundation
- **Status Tracking**: Records when registration was submitted

## Requirements

- OpenEMR 7.0.2 or later
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

After enabling the module:

1. Go to **Administration > Globals > ONC Registration**
2. Enable the module
3. Fill in your organization details:
   - Organization Name
   - Organization Location (address)
   - Organization NPI (10-digit number)
   - FHIR Endpoint URL (auto-detected if empty)

## Usage

Access the module via **Administration > ONC Registration** in the OpenEMR menu.

The dashboard shows:

1. **Configuration Checklist**: Verifies required OpenEMR settings
   - FHIR REST API enabled
   - Hash algorithms set to SHA512
   - Audit log encryption enabled

2. **Organization Information**: Your registration details with NPI validation

3. **Submit Registration**: Tools to send registration to OpenEMR Foundation
   - One-click email generation
   - Copy/paste option for manual submission

## ONC Certification Requirements

For an OpenEMR installation to use the product's ONC certification:

1. Configure required global settings (shown in checklist)
2. Ensure FHIR endpoint is publicly accessible via HTTPS
3. Register with the OpenEMR Foundation
4. Use AES-encrypted drives on end-user devices
5. Use FIPS-compliant SSL/TLS ciphers
6. Support NTP v4 (RFC 5905) for time synchronization

See [OpenEMR ONC Certification Requirements](https://www.open-emr.org/wiki/index.php/OpenEMR_7.0.2_ONC_Certification_Requirements) for full details.

## Development

```bash
# Install dependencies
composer install

# Run code quality checks
composer check

# Run tests
composer test
```

## Support

- Issues: https://github.com/opencoreemr/oce-module-onc-registration/issues
- Email: support@opencoreemr.com

## License

GNU General Public License v3.0
