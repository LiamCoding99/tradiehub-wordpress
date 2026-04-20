<?php
if (!defined('ABSPATH')) exit;

class TradieHub_Activator {
    public static function activate(): void {
        self::create_escrow_table();
        self::register_roles_and_caps();
        flush_rewrite_rules();
    }

    private static function create_escrow_table(): void {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table = $wpdb->prefix . 'tradiehub_escrow';

        $sql = "CREATE TABLE $table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            job_id bigint(20) unsigned NOT NULL,
            quote_id bigint(20) unsigned NOT NULL,
            homeowner_id bigint(20) unsigned NOT NULL,
            contractor_id bigint(20) unsigned NOT NULL,
            amount decimal(10,2) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'held',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            released_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY idx_homeowner (homeowner_id),
            KEY idx_contractor (contractor_id),
            KEY idx_status (status)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option('tradiehub_db_version', TRADIEHUB_VERSION);
    }

    private static function register_roles_and_caps(): void {
        add_role('tradiehub_contractor', 'Contractor', [
            'read'                     => true,
            'submit_tradiehub_quote'   => true,
            'view_tradiehub_community' => true,
        ]);

        add_role('tradiehub_homeowner', 'Homeowner', [
            'read'                   => true,
            'publish_tradiehub_jobs' => true,
        ]);

        $admin = get_role('administrator');
        if ($admin) {
            foreach (['submit_tradiehub_quote', 'publish_tradiehub_jobs', 'manage_tradiehub_escrow', 'view_tradiehub_community'] as $cap) {
                $admin->add_cap($cap);
            }
        }
    }
}
