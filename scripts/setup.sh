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
wp plugin install buddypress --activate
wp plugin install query-monitor --activate

echo "==> Installing and activating Astra theme..."
wp theme install astra --activate

echo "==> Activating TradieHub child theme..."
wp theme activate tradiehub

echo ""
echo "==> Done! Next steps:"
echo "    1. Run: bash scripts/seed-demo-data.sh"
echo "    2. Run: wp tradiehub generate-seo-pages"
echo "    3. Visit wp-admin to configure FluentCommunity spaces and BuddyPress groups"
