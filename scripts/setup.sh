#!/usr/bin/env bash
# TradieHub WordPress baseline setup.
# Run from the LocalWP app/public directory after creating the site in LocalWP.
# Usage: bash /path/to/repo/scripts/setup.sh

set -euo pipefail

echo "==> Updating WordPress core..."
wp core update

echo "==> Configuring site identity..."
wp option update blogname "TradieHub"
wp option update blogdescription "Find licensed contractors in California"
wp option update timezone_string "America/Los_Angeles"
wp option update WPLANG "en_US"

echo "==> Setting permalink structure..."
wp rewrite structure '/%postname%/' --hard

echo "==> Hiding site from search engines during dev..."
wp option update blog_public 0

echo "==> Disabling comments globally..."
wp option update default_comment_status closed
wp option update default_ping_status closed

echo "==> Configuring WooCommerce..."
wp option update woocommerce_currency "USD"
wp option update woocommerce_default_country "US:CA"
wp option update woocommerce_store_address "123 Market St"
wp option update woocommerce_store_city "San Francisco"
wp option update woocommerce_store_postcode "94103"
wp option update woocommerce_enable_guest_checkout "no"
wp option update woocommerce_show_marketplace_suggestions "no"
wp option update woocommerce_allow_tracking "no"

echo "==> Installing and activating free plugins..."
wp plugin install seo-by-rank-math --activate
wp plugin install woocommerce --activate
wp plugin install elementor --activate
wp plugin install woo-wallet --activate
wp plugin install fluent-community --activate
wp plugin install query-monitor --activate

echo ""
echo "==> Done! Manual steps remaining:"
echo "    1. Install Felan parent theme from ThemeForest ZIP"
echo "    2. Install B2BKing from CodeCanyon ZIP"
echo "    3. Install FluentCommunity Pro ZIP"
echo "    4. Visit wp-admin, accept Felan's TGMPA required plugin installs"
echo "    5. Run: bash scripts/seed-demo-data.sh"
