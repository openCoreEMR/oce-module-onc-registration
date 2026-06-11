# Changelog

## [0.2.0](https://github.com/openCoreEMR/oce-module-onc-registration/compare/0.1.0...0.2.0) (2026-06-11)


### ⚠ BREAKING CHANGES

* PHP 8.2/8.3/8.4 are no longer supported.

### Features

* Add preview mode, registration verification, and collapsible dashboard ([#2](https://github.com/openCoreEMR/oce-module-onc-registration/issues/2)) ([94f0ec6](https://github.com/openCoreEMR/oce-module-onc-registration/commit/94f0ec6a32f835c06a791d56a3fe20f03bf55b36))
* drop support for PHP &lt; 8.5 ([#12](https://github.com/openCoreEMR/oce-module-onc-registration/issues/12)) ([216466f](https://github.com/openCoreEMR/oce-module-onc-registration/commit/216466fa3ee53b4a2cf6e724b5575ea309bb7ac2))
* Initial ONC Registration module implementation ([64e5991](https://github.com/openCoreEMR/oce-module-onc-registration/commit/64e5991d9c2e10f393a2d85698217c66feab37bd))


### Bug Fixes

* add declare(strict_types=1) to satisfy Rector ([#11](https://github.com/openCoreEMR/oce-module-onc-registration/issues/11)) ([6ec207f](https://github.com/openCoreEMR/oce-module-onc-registration/commit/6ec207fa088291bf85d88a0844047c2e8f99a9db))
* **bootstrap:** resolve Kernel projectDir throw on oce-810 ([#47](https://github.com/openCoreEMR/oce-module-onc-registration/issues/47)) ([c32c714](https://github.com/openCoreEMR/oce-module-onc-registration/commit/c32c714c708f417196a99510fb30d43d59de2a4f))
* **csrf:** pass SessionInterface to CsrfUtils for oce-810 ([#48](https://github.com/openCoreEMR/oce-module-onc-registration/issues/48)) ([b9bd749](https://github.com/openCoreEMR/oce-module-onc-registration/commit/b9bd749e7040987b44e9d2800aac76ed37a601b7))
* export-ignore tests/ and namespace global Document mock ([#129](https://github.com/openCoreEMR/oce-module-onc-registration/issues/129)) ([#44](https://github.com/openCoreEMR/oce-module-onc-registration/issues/44)) ([7f2d4f7](https://github.com/openCoreEMR/oce-module-onc-registration/commit/7f2d4f754a24279f7a79192d303abad560b4fda9))
* FHIR endpoint is auto-detected, not required input ([ad903d9](https://github.com/openCoreEMR/oce-module-onc-registration/commit/ad903d9a86d3d0f43c00757e1f661031878ae0a3))
* resolve code review issues ([#4](https://github.com/openCoreEMR/oce-module-onc-registration/issues/4)) ([abf6875](https://github.com/openCoreEMR/oce-module-onc-registration/commit/abf687552af8b8767f0cf6abbfefd2eb3ed562c9))
* resolve PHPCS and PHPStan check failures ([#3](https://github.com/openCoreEMR/oce-module-onc-registration/issues/3)) ([ef2c716](https://github.com/openCoreEMR/oce-module-onc-registration/commit/ef2c71671b068c4843884edd1bcc142e28d81eed))


### Documentation

* add info.txt, versioning, and error handling rules ([#5](https://github.com/openCoreEMR/oce-module-onc-registration/issues/5)) ([b3aab31](https://github.com/openCoreEMR/oce-module-onc-registration/commit/b3aab31b995e3faecd0031088341ebb0d2dbfefd))
* Add TODO to replace NpiValidator with OpenEMR ValidationUtils ([87fc9c1](https://github.com/openCoreEMR/oce-module-onc-registration/commit/87fc9c11e6c61d11292f62cfe1a6d3092618008a))


### Dependencies

* **deps-dev:** update phpunit/phpunit requirement from ^11.0 to ^13.1 ([#19](https://github.com/openCoreEMR/oce-module-onc-registration/issues/19)) ([3256e27](https://github.com/openCoreEMR/oce-module-onc-registration/commit/3256e27b5c05ac62c2aaa2a23dec98e0920389a0))
* **deps:** bump actions/upload-artifact from 6 to 7 ([#7](https://github.com/openCoreEMR/oce-module-onc-registration/issues/7)) ([48f7ddb](https://github.com/openCoreEMR/oce-module-onc-registration/commit/48f7ddbf54e56d6600c1c159b7f4c753164a4b26))
* **deps:** bump googleapis/release-please-action from 4 to 5 ([#9](https://github.com/openCoreEMR/oce-module-onc-registration/issues/9)) ([dee25bd](https://github.com/openCoreEMR/oce-module-onc-registration/commit/dee25bdee763129b7100a7add1fdeb154fe6724c))
* **deps:** bump openCoreEMR/github-workflows-public/.github/workflows/actionlint.yml ([#32](https://github.com/openCoreEMR/oce-module-onc-registration/issues/32)) ([91c34b3](https://github.com/openCoreEMR/oce-module-onc-registration/commit/91c34b30aae1b388ce7a69f6de90f983e4f68c54))
* **deps:** bump openCoreEMR/github-workflows-public/.github/workflows/conventional-pr-title.yml ([#23](https://github.com/openCoreEMR/oce-module-onc-registration/issues/23)) ([aebffdd](https://github.com/openCoreEMR/oce-module-onc-registration/commit/aebffdd8c7b7125552cdc6ca7d77aaa1ee49382d))
* **deps:** bump openCoreEMR/github-workflows-public/.github/workflows/conventional-pr-title.yml ([#28](https://github.com/openCoreEMR/oce-module-onc-registration/issues/28)) ([42136d6](https://github.com/openCoreEMR/oce-module-onc-registration/commit/42136d69b53879e66539694dffb38ef9bc6c67b6))
* **deps:** bump openCoreEMR/github-workflows-public/.github/workflows/dclint.yml ([#22](https://github.com/openCoreEMR/oce-module-onc-registration/issues/22)) ([fef669a](https://github.com/openCoreEMR/oce-module-onc-registration/commit/fef669af107c8f029c75c5c4f139360f8ad2016a))
* **deps:** bump openCoreEMR/github-workflows-public/.github/workflows/dclint.yml ([#31](https://github.com/openCoreEMR/oce-module-onc-registration/issues/31)) ([b8927c4](https://github.com/openCoreEMR/oce-module-onc-registration/commit/b8927c4ac8163f9ade3554c22cad543f197583ec))
* **deps:** bump openCoreEMR/github-workflows-public/.github/workflows/hadolint.yml ([#25](https://github.com/openCoreEMR/oce-module-onc-registration/issues/25)) ([32eb1cf](https://github.com/openCoreEMR/oce-module-onc-registration/commit/32eb1cf886997e1f532018cccc8f25726f481cf0))
* **deps:** bump openCoreEMR/github-workflows-public/.github/workflows/hadolint.yml ([#30](https://github.com/openCoreEMR/oce-module-onc-registration/issues/30)) ([8ff0fcd](https://github.com/openCoreEMR/oce-module-onc-registration/commit/8ff0fcd594a486791862820a0229efb2f7d64624))
* **deps:** bump openCoreEMR/github-workflows-public/.github/workflows/php-composer-script.yml ([#24](https://github.com/openCoreEMR/oce-module-onc-registration/issues/24)) ([27958b6](https://github.com/openCoreEMR/oce-module-onc-registration/commit/27958b69f6685f5d9655b737e96239edc0031c8d))
* **deps:** bump openCoreEMR/github-workflows-public/.github/workflows/php-tests.yml ([#29](https://github.com/openCoreEMR/oce-module-onc-registration/issues/29)) ([313ae43](https://github.com/openCoreEMR/oce-module-onc-registration/commit/313ae433100ec6f397d0797da622d6334135b59a))
* **deps:** bump opencoreemr/github-workflows-public/.github/workflows/release-please-reusable.yml ([#15](https://github.com/openCoreEMR/oce-module-onc-registration/issues/15)) ([c14863f](https://github.com/openCoreEMR/oce-module-onc-registration/commit/c14863f66d9af22b7989254af0cc783eb55235d4))
* **deps:** bump opencoreemr/github-workflows-public/.github/workflows/release-please-reusable.yml ([#26](https://github.com/openCoreEMR/oce-module-onc-registration/issues/26)) ([9c854b2](https://github.com/openCoreEMR/oce-module-onc-registration/commit/9c854b2bab79756ac0219b940fe30b09d3f71a61))
* **deps:** bump rhysd/actionlint from 1.7.10 to 1.7.11 ([#6](https://github.com/openCoreEMR/oce-module-onc-registration/issues/6)) ([6edd416](https://github.com/openCoreEMR/oce-module-onc-registration/commit/6edd4160735767ff4a1b386e7fabd763ff29de5d))
* **deps:** bump rhysd/actionlint from 1.7.11 to 1.7.12 ([#8](https://github.com/openCoreEMR/oce-module-onc-registration/issues/8)) ([c5d8cce](https://github.com/openCoreEMR/oce-module-onc-registration/commit/c5d8cce25f0311b273eb49d7314046bf4dc83685))
