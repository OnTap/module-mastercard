#!/usr/bin/env bash

# Copyright © 2016 Magento. All rights reserved.
# See COPYING.txt for license details.

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

# mock mail
sudo service postfix stop
echo # print a newline
smtp-sink -d "%d.%H.%M.%S" localhost:2500 1000 &
echo 'sendmail_path = "/usr/sbin/sendmail -t -i "' > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/sendmail.ini

# disable xdebug and adjust memory limit
echo > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini
echo 'memory_limit = -1' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
phpenv rehash;

composer config -a -g http-basic.repo.magento.com $COMPOSER_USER $COMPOSER_PASS
composer remove magento/module-bundle-sample-data magento/module-theme-sample-data magento/module-widget-sample-data magento/module-catalog-sample-data magento/module-customer-sample-data magento/module-cms-sample-data magento/module-catalog-rule-sample-data magento/module-sales-rule-sample-data magento/module-review-sample-data magento/module-tax-sample-data magento/module-sales-sample-data magento/module-grouped-product-sample-data magento/module-downloadable-sample-data magento/module-msrp-sample-data magento/module-configurable-sample-data magento/module-product-links-sample-data magento/module-wishlist-sample-data magento/module-swatches-sample-data magento/sample-data-media magento/module-offline-shipping-sample-data

# If env var is present, configure support for 3rd party builds which include private dependencies
test -n "$GITHUB_TOKEN" && composer config github-oauth.github.com "$GITHUB_TOKEN" || true
