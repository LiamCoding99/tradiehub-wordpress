<?php
if (!defined('ABSPATH')) exit;

/**
 * Escrow wallet bridge between the TradieHub quote workflow and TeraWallet.
 *
 * Flow:
 *   1. Homeowner accepts quote:   tradiehub_quote_accepted fires
 *   2. This class debits homeowner wallet, creates wp_tradiehub_escrow row (status=held)
 *   3. Both parties confirm complete: tradiehub_job_completed fires
 *   4. This class credits contractor wallet, updates escrow row (status=released)
 *   5. Either party disputes: tradiehub_job_disputed fires
 *   6. Funds stay held until admin resolves manually via the Escrow Dashboard
 *
 * TeraWallet primitives used:
 *   woo_wallet()->wallet->get_wallet_balance($user_id, 'edit')  -> float
 *   woo_wallet()->wallet->debit($user_id, $amount, $description)
 *   woo_wallet()->wallet->credit($user_id, $amount, $description)
 */
class TradieHub_Escrow_Wallet {
    public static function init(): void {
        add_action('tradiehub_quote_accepted', [self::class, 'hold_escrow'], 10, 2);
        add_action('tradiehub_job_completed',  [self::class, 'release_escrow'], 10, 1);
        add_action('tradiehub_job_disputed',   [self::class, 'flag_dispute'], 10, 1);
        add_action('admin_menu', [self::class, 'register_admin_page']);
    }

    /**
     * Debits homeowner wallet and creates a held escrow row.
     * Fires tradiehub_escrow_insufficient_funds if homeowner balance is too low.
     */
    public static function hold_escrow(int $quote_id, int $job_id): void {
        $amount        = (float) get_post_meta($quote_id, 'amount', true);
        $homeowner_id  = (int)   get_post_field('post_author', $job_id);
        $contractor_id = (int)   get_post_field('post_author', $quote_id);

        if ($amount <= 0 || !$homeowner_id || !$contractor_id) return;
        if (!function_exists('woo_wallet')) return;

        $balance = (float) woo_wallet()->wallet->get_wallet_balance($homeowner_id, 'edit');
        if ($balance < $amount) {
            do_action('tradiehub_escrow_insufficient_funds', $quote_id, $homeowner_id, $amount, $balance);
            return;
        }

        do_action('tradiehub_before_escrow_hold', $quote_id, $amount);

        woo_wallet()->wallet->debit(
            $homeowner_id,
            $amount,
            sprintf(__('Escrow held for job #%d', 'tradiehub'), $job_id)
        );

        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'tradiehub_escrow',
            [
                'job_id'        => $job_id,
                'quote_id'      => $quote_id,
                'homeowner_id'  => $homeowner_id,
                'contractor_id' => $contractor_id,
                'amount'        => $amount,
                'status'        => 'held',
                'created_at'    => current_time('mysql'),
            ],
            ['%d', '%d', '%d', '%d', '%f', '%s', '%s']
        );

        do_action('tradiehub_after_escrow_hold', $quote_id, $amount);
    }

    /**
     * Credits contractor wallet and marks escrow as released.
     * Only processes escrow rows in "held" status to prevent double-release.
     */
    public static function release_escrow(int $job_id): void {
        global $wpdb;

        $escrow = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tradiehub_escrow WHERE job_id = %d AND status = 'held'",
            $job_id
        ));

        if (!$escrow) return;

        do_action('tradiehub_before_escrow_release', (int) $escrow->id, (float) $escrow->amount);

        if (function_exists('woo_wallet')) {
            woo_wallet()->wallet->credit(
                (int) $escrow->contractor_id,
                (float) $escrow->amount,
                sprintf(__('Payment released for job #%d', 'tradiehub'), $job_id)
            );
        }

        $wpdb->update(
            $wpdb->prefix . 'tradiehub_escrow',
            ['status' => 'released', 'released_at' => current_time('mysql')],
            ['id' => (int) $escrow->id],
            ['%s', '%s'],
            ['%d']
        );

        do_action('tradiehub_after_escrow_release', (int) $escrow->id, (float) $escrow->amount);
    }

    /**
     * Marks escrow as disputed. Funds stay held until an admin resolves it.
     */
    public static function flag_dispute(int $job_id): void {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'tradiehub_escrow',
            ['status' => 'disputed'],
            ['job_id' => $job_id, 'status' => 'held'],
            ['%s'],
            ['%d', '%s']
        );
    }

    public static function register_admin_page(): void {
        add_submenu_page(
            'woocommerce',
            __('Escrow Dashboard', 'tradiehub'),
            __('TradieHub Escrow', 'tradiehub'),
            'manage_tradiehub_escrow',
            'tradiehub-escrow',
            [self::class, 'render_admin_page']
        );
    }

    public static function render_admin_page(): void {
        if (!current_user_can('manage_tradiehub_escrow')) {
            wp_die(esc_html__('You do not have permission to view this page.', 'tradiehub'));
        }

        global $wpdb;

        // Static query with no user input - prepare() not needed but joins use $wpdb->prefix safely.
        $escrows = $wpdb->get_results(
            "SELECT e.*,
                    u1.display_name AS homeowner_name,
                    u2.display_name AS contractor_name
             FROM {$wpdb->prefix}tradiehub_escrow e
             LEFT JOIN {$wpdb->users} u1 ON u1.ID = e.homeowner_id
             LEFT JOIN {$wpdb->users} u2 ON u2.ID = e.contractor_id
             ORDER BY e.created_at DESC
             LIMIT 200"
        );

        // Static aggregate query with no user input.
        $totals = $wpdb->get_row(
            "SELECT
                SUM(CASE WHEN status='held'     THEN amount ELSE 0 END) AS total_held,
                SUM(CASE WHEN status='released' THEN amount ELSE 0 END) AS total_released,
                SUM(CASE WHEN status='disputed' THEN amount ELSE 0 END) AS total_disputed
             FROM {$wpdb->prefix}tradiehub_escrow"
        );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('TradieHub Escrow Dashboard', 'tradiehub'); ?></h1>
            <div class="notice notice-info inline">
                <p>
                    <strong><?php esc_html_e('Currently held:', 'tradiehub'); ?></strong>
                    $<?php echo esc_html(number_format((float) ($totals->total_held ?? 0), 2)); ?> &nbsp;|&nbsp;
                    <strong><?php esc_html_e('Released:', 'tradiehub'); ?></strong>
                    $<?php echo esc_html(number_format((float) ($totals->total_released ?? 0), 2)); ?> &nbsp;|&nbsp;
                    <strong><?php esc_html_e('Disputed:', 'tradiehub'); ?></strong>
                    $<?php echo esc_html(number_format((float) ($totals->total_disputed ?? 0), 2)); ?>
                </p>
            </div>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'tradiehub'); ?></th>
                        <th><?php esc_html_e('Job', 'tradiehub'); ?></th>
                        <th><?php esc_html_e('Homeowner', 'tradiehub'); ?></th>
                        <th><?php esc_html_e('Contractor', 'tradiehub'); ?></th>
                        <th><?php esc_html_e('Amount', 'tradiehub'); ?></th>
                        <th><?php esc_html_e('Status', 'tradiehub'); ?></th>
                        <th><?php esc_html_e('Created', 'tradiehub'); ?></th>
                        <th><?php esc_html_e('Released', 'tradiehub'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($escrows as $row): ?>
                    <tr>
                        <td><?php echo (int) $row->id; ?></td>
                        <td>
                            <a href="<?php echo esc_url((string) get_edit_post_link((int) $row->job_id)); ?>">
                                #<?php echo (int) $row->job_id; ?>
                            </a>
                        </td>
                        <td><?php echo esc_html((string) $row->homeowner_name); ?></td>
                        <td><?php echo esc_html((string) $row->contractor_name); ?></td>
                        <td>$<?php echo esc_html(number_format((float) $row->amount, 2)); ?></td>
                        <td>
                            <span class="tradiehub-status-<?php echo esc_attr((string) $row->status); ?>">
                                <?php echo esc_html(ucfirst((string) $row->status)); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html((string) $row->created_at); ?></td>
                        <td><?php echo esc_html($row->released_at ?: '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Returns total currently-held escrow amount for a user.
     * Used to display "locked" balance on the wallet page.
     */
    public static function get_held_amount_for_user(int $user_id, string $role = 'homeowner'): float {
        global $wpdb;
        $col = $role === 'homeowner' ? 'homeowner_id' : 'contractor_id';
        return (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM {$wpdb->prefix}tradiehub_escrow WHERE `{$col}` = %d AND status = 'held'",
            $user_id
        ));
    }
}
