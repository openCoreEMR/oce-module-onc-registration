# Getting Started with Your OpenEMR Module

This quick start guide will help you create a new OpenEMR module from this template.

## Step 1: Copy the Template

```bash
git clone https://github.com/opencoreemr/oce-module-template.git oce-module-onc-registration
cd oce-module-onc-registration
rm -rf .git
git init
git add .
git commit -m "Initial commit from template"
```

## Step 2: Install Dependencies

```bash
composer install
```

## Step 3: Run the Automated Setup (Recommended)

The easiest way to configure your module is using the automated setup wizard:

```bash
./bin/setup
```

This interactive command will:
- Ask if this is for **internal OpenCoreEMR use** (oce- prefix) or **external/community use** (oe- prefix)
- Prompt for your module name (e.g., "lab-integration")
- Ask for vendor name if external use (e.g., "MyOrg" or "OpenEMR")
- Replace all placeholders throughout the codebase:
  - `OpenCoreEMR` → Your vendor name
  - `oce` → `oce` or `oe`
  - `onc-registration` → Your module name (lowercase-with-hyphens)
  - `OncRegistration` → Your module name (PascalCase)
  - `opencoreemr` → Vendor name (lowercase)
- Show a summary and ask for confirmation
- Optionally remove setup files when complete

**For OpenCoreEMR Internal Use:**
- Namespace: `OpenCoreEMR\Modules\OncRegistration`
- Package: `opencoreemr/oce-module-onc-registration`
- Module ID: `oce-module-onc-registration`

**For External/Community Use:**
- Namespace: `YourVendor\Modules\OncRegistration` (or `OpenEMR\Modules\OncRegistration`)
- Package: `yourvendor/oe-module-onc-registration`
- Module ID: `oe-module-onc-registration`

## Step 3b: Manual Setup (Alternative)

If you prefer manual setup, use your text editor's find-and-replace feature:

| Find | Replace With | Example |
|------|--------------|---------|
| `OpenCoreEMR` | Vendor name (PascalCase) | `OpenCoreEMR` or `MyOrg` |
| `opencoreemr` | Vendor name (lowercase) | `opencoreemr` or `myorg` |
| `oce` | Module prefix | `oce` (internal) or `oe` (external) |
| `onc-registration` | Module name (lowercase-with-hyphens) | `lab-integration` |
| `OncRegistration` | Module name (PascalCase) | `LabIntegration` |

Files that contain placeholders:
- `composer.json`
- `version.php`
- `phpcs.xml`
- `openemr.bootstrap.php`
- `src/Bootstrap.php`
- `src/GlobalsAccessor.php`
- `README.md`
- `CLAUDE.md`

## Step 4: Set Up Pre-Commit Hooks

```bash
pip install pre-commit
pre-commit install
```

## Step 5: Create Your Module Structure

After running the setup wizard, your placeholders will be replaced. Now you can start building your module.

The template already provides:
- ✅ `src/Bootstrap.php` - Module initialization (configured with your namespace)
- ✅ `src/GlobalsAccessor.php` - Globals access wrapper (configured with your namespace)
- ✅ `openemr.bootstrap.php` - OpenEMR module loader (configured with your namespace)

You need to create:

### 1. GlobalConfig Class

Create `src/GlobalConfig.php` to manage your module's configuration - see `DEVELOPMENT.md` for examples.

### 2. Exception Hierarchy

Create in `src/Exception/`:
- `OncRegistrationExceptionInterface.php` - Base exception interface
- `OncRegistrationException.php` - Base exception class
- `OncRegistrationNotFoundException.php` - For 404 errors
- `OncRegistrationAccessDeniedException.php` - For 403 errors
- `OncRegistrationValidationException.php` - For 400 errors

See `CLAUDE.md` for exception patterns.

### 3. Controllers

Create controllers in `src/Controller/`:
- Example: `{Feature}Controller.php`

See `CLAUDE.md` for controller patterns.

### 4. Services

Create services in `src/Service/`:
- Example: `{Feature}Service.php`

See `CLAUDE.md` for service patterns.

### 5. Public Entry Points

Create entry points in `public/`:
- `index.php` - Main entry point
- `{feature}.php` - Feature-specific entry points

See `CLAUDE.md` for the minimal 25-35 line pattern.

### 6. Twig Templates

Create templates in `templates/`:
- `{feature}/list.html.twig`
- `{feature}/view.html.twig`
- `{feature}/partials/_form.html.twig`

See `CLAUDE.md` for template patterns.

## Step 6: Test Your Module

```bash
# Run code quality checks
composer code-quality

# Copy to OpenEMR for testing
cp -r . /path/to/openemr/interface/modules/custom_modules/oce-module-onc-registration/
```

Then in OpenEMR:
1. Navigate to **Administration > Modules > Manage Modules**
2. Click **Register**
3. Click **Install**
4. Click **Enable**

## Step 7: Version Control

```bash
git add .
git commit -m "Initial module structure"
```

## Need Help?

- Read `CLAUDE.md` for comprehensive architectural patterns
- Read `DEVELOPMENT.md` for detailed development workflow
- Check existing modules for examples
- Ask on OpenEMR forums or create an issue

## Checklist

- [ ] Ran `composer install`
- [ ] Ran `./bin/setup` to replace all placeholders OR manually replaced them
- [ ] Set up pre-commit hooks (`pip install pre-commit && pre-commit install`)
- [ ] Created GlobalConfig class
- [ ] Created exception hierarchy (interface, base class, specific exceptions)
- [ ] Created at least one controller
- [ ] Created at least one service
- [ ] Created at least one public entry point
- [ ] Created Twig templates
- [ ] All pre-commit checks pass (`pre-commit run -a`)
- [ ] Module registers and enables in OpenEMR
- [ ] Deleted `.gitkeep` files from empty directories
- [ ] Optionally deleted setup files (`bin/setup`, `src/Command/SetupCommand.php`)
- [ ] Optionally deleted this `GETTING_STARTED.md` file

Happy coding!
