---
name: wordpress-php-conventions
description: Use when writing PHP for TradieHub's child theme or custom plugin. Covers hooks, nonces, capability checks, sanitization/escaping, WP-standard naming, and security patterns.
---

# WordPress PHP Conventions for TradieHub

## Naming
- Prefix everything with `tradiehub_` or class-prefix `TradieHub_`
- Hook names: lowercase, underscore-separated (`tradiehub_quote_accepted`)
- Class names: PascalCase with prefix (`TradieHub_Escrow_Wallet`)
- Function names: snake_case with prefix (`tradiehub_release_escrow`)

## The Big Five Security Patterns

### 1. Nonces (CSRF protection)
```php
wp_nonce_field('tradiehub_accept_quote', '_tradiehub_nonce');

if (!isset($_POST['_tradiehub_nonce']) ||
    !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_tradiehub_nonce'])), 'tradiehub_accept_quote')) {
    wp_die('Security check failed');
}
```

### 2. Capability checks (never check roles directly)
```php
if (!current_user_can('submit_tradiehub_quote')) {
    wp_die('Insufficient permissions');
}
```

### 3. Sanitize input
```php
$zip         = sanitize_text_field($_POST['zip']);
$amount      = floatval($_POST['amount']);
$description = wp_kses_post($_POST['description']);
$email       = sanitize_email($_POST['email']);
```

### 4. Escape output
```php
echo esc_html($user_input);
echo esc_attr($html_attribute);
echo esc_url($link);
echo wp_kses_post($rich_text);
```

### 5. Prepare SQL
```php
global $wpdb;
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}tradiehub_escrow WHERE homeowner_id = %d AND status = %s",
    $user_id, 'held'
));
```

## Hook Patterns
```php
do_action('tradiehub_quote_accepted', $quote_id);
$text = apply_filters('tradiehub_quote_display_text', $text);
add_action('woocommerce_order_status_completed', 'tradiehub_release_escrow', 10, 1);
```

## Custom DB Tables
Always use `dbDelta`, never raw `CREATE TABLE`:
```php
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
dbDelta($sql);
```

## REST API (preferred over AJAX for new code)
```php
register_rest_route('tradiehub/v1', '/quote', [
    'methods'             => 'POST',
    'callback'            => 'tradiehub_rest_submit_quote',
    'permission_callback' => fn() => current_user_can('submit_tradiehub_quote'),
    'args' => [
        'job_id' => ['required' => true, 'type' => 'integer'],
        'amount' => ['required' => true, 'type' => 'number'],
    ],
]);
```

## Never Do These
- Direct `echo $_POST['anything']` (always sanitize + escape)
- Hardcoded table prefixes (use `$wpdb->prefix`)
- Checking roles directly instead of capabilities
- `require 'something.php'` without plugin path constant
