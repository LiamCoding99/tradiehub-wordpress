<?php
if (!defined('ABSPATH')) exit;

/*
 * Placeholder for child-theme-specific hooks.
 * Contractor profile display, quote form tweaks, and Elementor widget overrides
 * go here once the tradiehub-core plugin is active and the Felan demo is imported.
 */

// Disable Felan's built-in package listing if B2BKing handles quotes.
// add_filter('felan_show_package_listing', '__return_false');

// Add a "Licensed" badge to author archive titles for verified contractors.
add_filter('the_title', function (string $title): string {
    if (!is_author()) return $title;
    $author = get_queried_object();
    if ($author instanceof WP_User && user_can($author->ID, 'submit_tradiehub_quote')) {
        $meta = get_user_meta($author->ID, 'cslb_license_valid', true);
        if ($meta) {
            $title .= ' <span class="badge verified" aria-label="CSLB format verified">Licensed</span>';
        }
    }
    return $title;
}, 20, 1);
