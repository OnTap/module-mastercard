# Changelog
All notable changes to this project will be documented in this file.

## [103.7.0] - 2021-10-19
### Changed
- Add support for the "Enforce Unique Order Reference" and "Enforce Unique Merchant Transaction Reference" gateway features
- Increased HostedCheckout API version from 58 to 61

## [103.6.0] - 2021-05-21
### Changed
- Adds Automated Clearing House (ACH) payments as an option

### Fixed
- Fixes a bug in value serialisation

## [103.5.0] - 2021-03-30
### Changed
- EMV 3D Secure 2.0 support for both Payment Methods
- Upgraded the supported API version
- Updated the deprecated libraries
- General refactoring

### Fixed
- Fixed a bug where under certain circumstances, the quote was loaded incorrectly
- Fixed the problems with the Payment sessions for the Guests.

## [103.4.0] - 2020-10-20
### Changed
- CSP whitelisting
- Magento 2.4 support
- Adds gateway test to saving of the admin config

### Fixed
- Fixed 3DS cookie retention
- Fixes deadlock loading bug with Vault payments
- Fixes the telephone file usage

## [103.3.2] - 2020-03-20
### Fixed
- Bugfixes

## [103.3.1] - 2020-02-07
### Fixed
- Fixes a an issue with `\Composer` component usage

## [103.3.0] - 2021-10-19
### Changed
- More customer friendly error messages
- Show module version in admin panel
- Direct Integration removed
- Ability to switch off the sending of line-items
- Module restructure
- RSS admin feed for announcements
- Increased HostedCheckout API version from 43 to 53

### Fixed
- Fixes a bug with currency setup
- Fixes a bug with Hosted Checkout modal title

## [103.2.5] - 2019-12-04
### Fixed
- Fixes issue with Magento 2.3 patch 3 - MAGETWO-99075

## [102.2.4] - 2020-02-25
### Fixed
- Fixes a problem with unresponsive "place order" button in newer minor Magento releases
