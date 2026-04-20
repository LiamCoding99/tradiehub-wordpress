<?php
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
    $parent = wp_get_theme()->parent();
    wp_enqueue_style(
        'felan-parent',
        get_template_directory_uri() . '/style.css',
        [],
        $parent ? $parent->get('Version') : '1.0'
    );
    wp_enqueue_style(
        'felan-child',
        get_stylesheet_uri(),
        ['felan-parent'],
        wp_get_theme()->get('Version')
    );
});

require_once get_stylesheet_directory() . '/inc/customizations.php';
