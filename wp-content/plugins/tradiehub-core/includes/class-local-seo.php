<?php
if (!defined('ABSPATH')) exit;

class TradieHub_Local_SEO {
    private const CITIES = [
        'los-angeles'   => 'Los Angeles',
        'san-francisco' => 'San Francisco',
        'san-diego'     => 'San Diego',
        'sacramento'    => 'Sacramento',
        'san-jose'      => 'San Jose',
        'fresno'        => 'Fresno',
        'long-beach'    => 'Long Beach',
        'oakland'       => 'Oakland',
        'bakersfield'   => 'Bakersfield',
        'anaheim'       => 'Anaheim',
    ];

    private const TRADES = [
        'plumbing'            => 'Plumbers',
        'electrical'          => 'Electricians',
        'hvac'                => 'HVAC Contractors',
        'roofing'             => 'Roofers',
        'landscaping'         => 'Landscapers',
        'general-contracting' => 'General Contractors',
        'painting'            => 'Painters',
        'flooring'            => 'Flooring Contractors',
        'remodeling'          => 'Remodeling Contractors',
        'pest-control'        => 'Pest Control Services',
    ];

    public static function init(): void {
        add_action('wp_head', [self::class, 'output_schema_markup'], 5);
        add_action('init', [self::class, 'register_rewrite_rules']);
        add_filter('query_vars', [self::class, 'add_query_vars']);
        add_action('template_redirect', [self::class, 'handle_seo_page_template']);

        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::add_command('tradiehub generate-seo-pages', [self::class, 'cli_generate_seo_pages']);
        }
    }

    public static function register_rewrite_rules(): void {
        foreach (array_keys(self::TRADES) as $trade_slug) {
            foreach (array_keys(self::CITIES) as $city_slug) {
                add_rewrite_rule(
                    "^{$trade_slug}-in-{$city_slug}/?$",
                    "index.php?tradiehub_trade={$trade_slug}&tradiehub_city={$city_slug}",
                    'top'
                );
            }
        }
    }

    public static function add_query_vars(array $vars): array {
        $vars[] = 'tradiehub_trade';
        $vars[] = 'tradiehub_city';
        return $vars;
    }

    public static function handle_seo_page_template(): void {
        $trade = get_query_var('tradiehub_trade');
        $city  = get_query_var('tradiehub_city');
        if (!$trade || !$city) return;
        if (!isset(self::TRADES[$trade], self::CITIES[$city])) return;

        self::render_seo_landing_page($trade, $city);
        exit;
    }

    private static function render_seo_landing_page(string $trade_slug, string $city_slug): void {
        $trade_label = self::TRADES[$trade_slug];
        $city_label  = self::CITIES[$city_slug];

        $contractors = get_users([
            'role'       => 'tradiehub_contractor',
            'meta_query' => [[
                'key'     => 'service_specialties',
                'value'   => $trade_slug,
                'compare' => 'LIKE',
            ]],
            'number' => 20,
        ]);

        // Rotate intro copy deterministically per city+trade combo to avoid duplicate content.
        $intro_variants = [
            "Looking for licensed {$trade_label} in {$city_label}, CA? TradieHub connects homeowners with verified, CSLB-licensed professionals.",
            "Find trusted {$trade_label} in {$city_label}, California. Every contractor on TradieHub is licensed, insured, and community-reviewed.",
            "Hire licensed {$trade_label} in {$city_label} with confidence. TradieHub's escrow-protected payment holds your deposit until the job is done.",
        ];
        $intro = $intro_variants[crc32($trade_slug . $city_slug) % count($intro_variants)];

        $schema = self::build_item_list_schema($contractors, $trade_label, $city_label);

        get_header();
        ?>
        <script type="application/ld+json"><?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?></script>
        <main class="tradiehub-seo-page">
            <div class="container">
                <nav class="breadcrumb" aria-label="Breadcrumb">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'tradiehub'); ?></a> &rsaquo;
                    <a href="<?php echo esc_url(home_url('/contractors/')); ?>"><?php esc_html_e('Contractors', 'tradiehub'); ?></a> &rsaquo;
                    <?php echo esc_html("Top {$trade_label} in {$city_label}, CA"); ?>
                </nav>
                <h1><?php echo esc_html("Top {$trade_label} in {$city_label}, CA"); ?></h1>
                <p class="intro"><?php echo esc_html($intro); ?></p>
                <?php if ($contractors): ?>
                <ul class="contractor-list">
                    <?php foreach ($contractors as $c): ?>
                    <li class="contractor-card">
                        <a href="<?php echo esc_url(get_author_posts_url($c->ID)); ?>"><?php echo esc_html($c->display_name); ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p><?php printf(esc_html__('No %s listed in %s yet. Be the first to join!', 'tradiehub'), esc_html($trade_label), esc_html($city_label)); ?></p>
                <?php endif; ?>
                <?php self::render_faq_section($trade_label, $city_label); ?>
                <p><a href="<?php echo esc_url(home_url('/contractors/')); ?>"><?php esc_html_e('Browse all contractors', 'tradiehub'); ?></a></p>
            </div>
        </main>
        <?php
        get_footer();
    }

    private static function render_faq_section(string $trade_label, string $city_label): void {
        $faqs = [
            [
                'q' => "Are {$trade_label} in {$city_label} required to be licensed in California?",
                'a' => "Yes. Most contractors in California must hold a valid CSLB license. TradieHub contractors display their license number on their profile.",
            ],
            [
                'q' => "How do I hire a contractor through TradieHub?",
                'a' => "Post your job with a description and budget. Licensed {$trade_label} in your area will submit quotes. Review their profiles, compare quotes, and accept the best fit.",
            ],
            [
                'q' => "What does TradieHub's escrow deposit protect me from?",
                'a' => "When you accept a quote, your deposit is held in TradieHub's secure wallet. Funds are only released to the contractor once the job is marked complete by both parties.",
            ],
            [
                'q' => "How much do {$trade_label} charge in {$city_label}?",
                'a' => "Rates vary by project scope and experience. Post your job on TradieHub to receive competitive quotes from licensed {$trade_label} near you.",
            ],
            [
                'q' => "Can I see reviews before hiring?",
                'a' => "Yes. Each contractor profile on TradieHub shows ratings from past homeowners and feedback from the contractor community.",
            ],
        ];
        ?>
        <section class="faq-section">
            <h2><?php printf(esc_html__('FAQ: %s in %s', 'tradiehub'), esc_html($trade_label), esc_html($city_label)); ?></h2>
            <?php foreach ($faqs as $faq): ?>
            <details>
                <summary><?php echo esc_html($faq['q']); ?></summary>
                <p><?php echo esc_html($faq['a']); ?></p>
            </details>
            <?php endforeach; ?>
        </section>
        <?php
    }

    public static function output_schema_markup(): void {
        // SEO pages output their own schema inline; skip here to avoid duplication.
        if (get_query_var('tradiehub_trade') && get_query_var('tradiehub_city')) return;

        if (is_author()) {
            $author = get_queried_object();
            if ($author instanceof WP_User && user_can($author->ID, 'submit_tradiehub_quote')) {
                $schema = self::build_local_business_schema($author);
                echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . "</script>\n";
            }
        }

        if (is_front_page()) {
            echo '<script type="application/ld+json">' . wp_json_encode(self::build_organization_schema(), JSON_UNESCAPED_SLASHES) . "</script>\n";
        }
    }

    private static function build_local_business_schema(WP_User $contractor): array {
        $meta = TradieHub_Contractor_Profile::get_contractor_meta($contractor->ID);
        $zips = $meta['service_zip_codes'];

        $schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'LocalBusiness',
            'name'       => $contractor->display_name,
            'url'        => get_author_posts_url($contractor->ID),
            'areaServed' => array_map(fn($z) => [
                '@type'          => 'PostalAddress',
                'postalCode'     => $z,
                'addressRegion'  => 'CA',
                'addressCountry' => 'US',
            ], $zips),
        ];

        if ($meta['cslb_license_number']) {
            $schema['hasCredential'] = [
                '@type'              => 'EducationalOccupationalCredential',
                'credentialCategory' => 'CSLB License',
                'name'               => $meta['cslb_license_number'],
            ];
        }

        return $schema;
    }

    private static function build_item_list_schema(array $contractors, string $trade, string $city): array {
        $items = array_values(array_map(function (WP_User $c, int $i) {
            return [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'item'     => [
                    '@type' => 'LocalBusiness',
                    'name'  => $c->display_name,
                    'url'   => get_author_posts_url($c->ID),
                ],
            ];
        }, $contractors, array_keys($contractors)));

        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'ItemList',
            'name'            => "Top {$trade} in {$city}, CA",
            'description'     => "Licensed {$trade} available on TradieHub in {$city}, California.",
            'itemListElement' => $items,
        ];
    }

    private static function build_organization_schema(): array {
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'Organization',
            'name'        => 'TradieHub',
            'url'         => home_url('/'),
            'description' => 'Find licensed contractors in California. Post jobs, get quotes, pay with escrow-protected deposits.',
            'areaServed'  => ['@type' => 'State', 'name' => 'California'],
        ];
    }

    /**
     * WP-CLI command: wp tradiehub generate-seo-pages
     * Creates 100 pages (10 cities x 10 trades) and flushes rewrites.
     */
    public static function cli_generate_seo_pages(): void {
        $count = 0;
        foreach (self::TRADES as $trade_slug => $trade_label) {
            foreach (self::CITIES as $city_slug => $city_label) {
                $slug = "{$trade_slug}-in-{$city_slug}";

                if (get_page_by_path($slug, OBJECT, 'page')) {
                    WP_CLI::log("Skipping existing: /{$slug}/");
                    continue;
                }

                $page_id = wp_insert_post([
                    'post_type'    => 'page',
                    'post_title'   => "Top {$trade_label} in {$city_label}, CA",
                    'post_name'    => $slug,
                    'post_status'  => 'publish',
                    'post_content' => '',
                    'meta_input'   => [
                        'tradiehub_seo_city'  => $city_slug,
                        'tradiehub_seo_trade' => $trade_slug,
                    ],
                ]);

                if (is_wp_error($page_id)) {
                    WP_CLI::warning("Failed: /{$slug}/ - " . $page_id->get_error_message());
                    continue;
                }

                $count++;
                WP_CLI::log("Created: /{$slug}/");
            }
        }

        WP_CLI::success("Generated {$count} SEO landing pages.");
        WP_CLI::runcommand('rewrite flush --hard');
    }
}
