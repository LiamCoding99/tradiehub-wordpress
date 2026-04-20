<?php
if (!defined('ABSPATH')) exit;

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

// Customizer: wire brand colors into Astra's color settings.
add_action('customize_register', function (WP_Customize_Manager $wp_customize) {
    $wp_customize->get_setting('astra-color-global-color-1')->default = '#1a6b4a';
    $wp_customize->get_setting('astra-color-global-color-2')->default = '#f4a01b';
});
