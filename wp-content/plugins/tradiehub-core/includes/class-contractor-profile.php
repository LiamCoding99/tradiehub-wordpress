<?php
if (!defined('ABSPATH')) exit;

class TradieHub_Contractor_Profile {
    private const CSLB_REGEX = '/^\d{6,8}$/';

    public static function init(): void {
        add_action('init', [self::class, 'register_taxonomy']);
        add_action('show_user_profile', [self::class, 'render_profile_fields']);
        add_action('edit_user_profile', [self::class, 'render_profile_fields']);
        add_action('personal_options_update', [self::class, 'save_profile_fields']);
        add_action('edit_user_profile_update', [self::class, 'save_profile_fields']);
        add_shortcode('tradiehub_contractor_profile', [self::class, 'render_profile_shortcode']);
    }

    public static function register_taxonomy(): void {
        register_taxonomy('tradiehub_specialty', ['tradiehub_job', 'tradiehub_quote'], [
            'labels' => [
                'name'          => __('Specialties', 'tradiehub'),
                'singular_name' => __('Specialty', 'tradiehub'),
            ],
            'public'       => true,
            'hierarchical' => false,
            'show_in_rest' => true,
            'rewrite'      => ['slug' => 'specialty'],
        ]);

        $specialties = [
            'plumbing', 'electrical', 'hvac', 'roofing', 'landscaping',
            'general-contracting', 'painting', 'flooring', 'remodeling', 'pest-control',
        ];
        foreach ($specialties as $slug) {
            if (!term_exists($slug, 'tradiehub_specialty')) {
                wp_insert_term(
                    ucwords(str_replace('-', ' ', $slug)),
                    'tradiehub_specialty',
                    ['slug' => $slug]
                );
            }
        }
    }

    public static function render_profile_fields(WP_User $user): void {
        if (!current_user_can('edit_user', $user->ID)) return;
        $meta = self::get_contractor_meta($user->ID);
        wp_nonce_field('tradiehub_contractor_profile', '_tradiehub_profile_nonce');
        ?>
        <h3><?php esc_html_e('TradieHub Contractor Profile', 'tradiehub'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="cslb_license_number"><?php esc_html_e('CSLB License Number', 'tradiehub'); ?></label></th>
                <td>
                    <input type="text" name="cslb_license_number" id="cslb_license_number"
                           value="<?php echo esc_attr($meta['cslb_license_number']); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('6-8 digit California contractor license number.', 'tradiehub'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="years_in_business"><?php esc_html_e('Years in Business', 'tradiehub'); ?></label></th>
                <td>
                    <input type="number" name="years_in_business" id="years_in_business"
                           min="0" max="100"
                           value="<?php echo esc_attr($meta['years_in_business']); ?>" class="small-text" />
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Insurance', 'tradiehub'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="has_liability_insurance" value="1"
                               <?php checked($meta['has_liability_insurance']); ?> />
                        <?php esc_html_e('Has general liability insurance', 'tradiehub'); ?>
                    </label><br>
                    <label>
                        <input type="checkbox" name="has_workers_comp" value="1"
                               <?php checked($meta['has_workers_comp']); ?> />
                        <?php esc_html_e("Has workers' compensation insurance", 'tradiehub'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th><label for="service_zip_codes"><?php esc_html_e('Service Zip Codes', 'tradiehub'); ?></label></th>
                <td>
                    <input type="text" name="service_zip_codes" id="service_zip_codes"
                           value="<?php echo esc_attr(implode(', ', $meta['service_zip_codes'])); ?>"
                           class="regular-text" />
                    <p class="description"><?php esc_html_e('Comma-separated CA zip codes (e.g. 90001, 90002).', 'tradiehub'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function save_profile_fields(int $user_id): void {
        if (!isset($_POST['_tradiehub_profile_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_tradiehub_profile_nonce'])), 'tradiehub_contractor_profile')) {
            return;
        }
        if (!current_user_can('edit_user', $user_id)) return;

        $license = sanitize_text_field(wp_unslash($_POST['cslb_license_number'] ?? ''));
        $license_valid = $license !== '' && preg_match(self::CSLB_REGEX, $license) === 1;

        update_user_meta($user_id, 'cslb_license_number', $license);
        update_user_meta($user_id, 'cslb_license_valid', $license_valid);
        update_user_meta($user_id, 'years_in_business', absint($_POST['years_in_business'] ?? 0));
        update_user_meta($user_id, 'has_liability_insurance', !empty($_POST['has_liability_insurance']));
        update_user_meta($user_id, 'has_workers_comp', !empty($_POST['has_workers_comp']));

        $zips_raw = sanitize_text_field(wp_unslash($_POST['service_zip_codes'] ?? ''));
        $zips = array_filter(array_map('trim', explode(',', $zips_raw)));
        // Accept only California zip codes (90000-96162)
        $ca_zips = array_values(array_filter($zips, fn($z) => preg_match('/^9[0-6]\d{3}$/', $z)));
        update_user_meta($user_id, 'service_zip_codes', $ca_zips);
    }

    public static function get_contractor_meta(int $user_id): array {
        return [
            'cslb_license_number'     => (string) get_user_meta($user_id, 'cslb_license_number', true),
            'years_in_business'       => (int)    get_user_meta($user_id, 'years_in_business', true),
            'has_liability_insurance' => (bool)   get_user_meta($user_id, 'has_liability_insurance', true),
            'has_workers_comp'        => (bool)   get_user_meta($user_id, 'has_workers_comp', true),
            'service_zip_codes'       => (array)  (get_user_meta($user_id, 'service_zip_codes', true) ?: []),
            'cslb_license_valid'      => (bool)   get_user_meta($user_id, 'cslb_license_valid', true),
        ];
    }

    public static function render_profile_shortcode(array $atts): string {
        $atts = shortcode_atts(['user_id' => get_queried_object_id()], $atts, 'tradiehub_contractor_profile');
        $user = get_userdata((int) $atts['user_id']);
        if (!$user) return '';

        $meta = self::get_contractor_meta($user->ID);
        ob_start();
        ?>
        <div class="tradiehub-contractor-profile">
            <h2><?php echo esc_html($user->display_name); ?></h2>
            <?php if ($meta['cslb_license_number']): ?>
            <p class="license">
                <strong><?php esc_html_e('CSLB License:', 'tradiehub'); ?></strong>
                <?php echo esc_html($meta['cslb_license_number']); ?>
                <?php if ($meta['cslb_license_valid']): ?>
                    <span class="badge verified"><?php esc_html_e('Format Valid', 'tradiehub'); ?></span>
                <?php endif; ?>
                <?php
                /*
                 * TODO: live CSLB API verification.
                 * Endpoint: https://www2.cslb.ca.gov/onlineservices/checklicenseII/licenseresults.aspx
                 * Cache results in a transient for 24h to avoid hammering the API.
                 * Store verified_at datetime once confirmed live.
                 */
                ?>
            </p>
            <?php endif; ?>
            <p><?php printf(esc_html__('%d years in business', 'tradiehub'), $meta['years_in_business']); ?></p>
            <?php if ($meta['has_liability_insurance']): ?>
                <span class="badge insurance"><?php esc_html_e('Liability Insurance', 'tradiehub'); ?></span>
            <?php endif; ?>
            <?php if ($meta['has_workers_comp']): ?>
                <span class="badge workers-comp"><?php esc_html_e("Workers' Comp", 'tradiehub'); ?></span>
            <?php endif; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}
