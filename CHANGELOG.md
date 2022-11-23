# Changelog
All notable changes to this project will be documented in this file.

## [103.9.1] - 2022-11-23
### Fixed
- Client-side validation is not triggered while clicking on the “Place Order“ button for the ACH payment method
- There is an "Invalid credentials" error message is shown when trying to save module settings even for correct credentials
- Fix incorrect dependency used in logger of the Hosted Checkout payment method

## [103.9.0] - 2022-10-19
### Changed
- Add the “Verify and Tokenize“ Payment Option for the "Hosted Payment Session" Payment Method


## [103.8.0] - 2022-09-12
### Changed
- Authentication by OV SSL Certificate option added
- Add the “Verify and Tokenize“ Payment Option for the ACH Payment Method 

### Fixed
- The module has to pause for a few seconds and then resubmit the Authenticate Payer request as-is in the case of the HTTP 503 response from the gateway instead of simply state about the unknown error


## [103.7.2] - 2022-03-11
### Fixed
- EMV 3DS doesn't work if Website Code is used in the Base URL
- EMV 3DS doesn't work if "device.browser" is the required parameter for 3DS validation rules


## [103.7.1] - 2021-12-01
### Fixed
- Fixed a race condition issue for Hosted Payment Form rendering 
- Fixed an issue for Hosted Payment Form when payment form is rendered even if the payment session JS fails to load entirely


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
