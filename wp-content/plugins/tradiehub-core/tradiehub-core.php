<?php
/**
 * Plugin Name: TradieHub Core
 * Description: Custom integration layer for TradieHub - wires Felan, FluentCommunity, B2BKing, and TeraWallet into a cohesive contractor marketplace.
 * Version: 1.0.0
 * Author: Liam
 * Requires PHP: 8.1
 * Requires at least: 6.5
 */

if (!defined('ABSPATH')) exit;

define('TRADIEHUB_VERSION', '1.0.0');
define('TRADIEHUB_PATH', plugin_dir_path(__FILE__));
define('TRADIEHUB_URL', plugin_dir_url(__FILE__));

require_once TRADIEHUB_PATH . 'includes/class-activator.php';
require_once TRADIEHUB_PATH . 'includes/class-contractor-profile.php';
require_once TRADIEHUB_PATH . 'includes/class-quote-workflow.php';
require_once TRADIEHUB_PATH . 'includes/class-escrow-wallet.php';
require_once TRADIEHUB_PATH . 'includes/class-local-seo.php';

register_activation_hook(__FILE__, ['TradieHub_Activator', 'activate']);

add_action('plugins_loaded', function () {
    TradieHub_Contractor_Profile::init();
    TradieHub_Quote_Workflow::init();
    TradieHub_Escrow_Wallet::init();
    TradieHub_Local_SEO::init();
});
