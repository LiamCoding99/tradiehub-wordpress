# TradieHub — Claude Code Build Instructions

> Master design document for Claude Code. Paste or reference this file as the primary instruction set.
> Project owner: Liam (portfolio project).

---

<project_meta>
  <name>TradieHub</name>
  <tagline>Find licensed contractors in California. Post jobs, get quotes, pay with escrow-protected deposits.</tagline>
  <type>Local trades directory + job marketplace + community (WordPress)</type>
  <target_region>California, USA (Los Angeles, Bay Area, San Diego, Sacramento focus)</target_region>
  <target_users>
    <homeowners>People hiring contractors for home projects (plumbing, electrical, HVAC, landscaping, remodeling, etc.)</homeowners>
    <contractors>Licensed trades professionals listing services and bidding on jobs</contractors>
  </target_users>
  <portfolio_goal>Demonstrate WordPress full-stack skills: theme customization, plugin integration, WP-CLI automation, custom code, SEO, clean documentation. NOT going to be publicly hosted — local build, documented well, pushed to GitHub.</portfolio_goal>
  <deployment>Local only (LocalWP). Final artifact is a public GitHub repo with child theme code, custom plugin code, WP-CLI provisioning scripts, screenshots, and a strong README.</deployment>
</project_meta>

---

<stack_decisions>
  <dev_environment>LocalWP (free, has WP-CLI built in, easy to share screenshots from)</dev_environment>
  <wordpress_version>Latest stable (6.7+ as of April 2026)</wordpress_version>
  <php_version>8.3</php_version>

  <theme>
    <name>Felan</name>
    <version>1.1.7 or latest</version>
    <reason>Theme is literally designed for freelance marketplaces and job boards, so it fits the trades directory use case natively. Don't pay full ThemeForest price — this is a portfolio build, so document the stack choice but note any licensing caveats in the README.</reason>
    <rule>NEVER edit parent theme files. All customization goes in Felan Child Theme.</rule>
  </theme>

  <plugins_included>
    <plugin>
      <name>FluentCommunity (Free) + FluentCommunity Pro</name>
      <version>2.2.0+ (out of beta as of 2026)</version>
      <purpose>Community hub for contractors: tips, tool recommendations, code-compliance discussions, trade-specific groups (Electricians CA, Plumbers CA, etc). Also provides real-time messaging (drops the need for Better Messages Pro from the old stack).</purpose>
    </plugin>
    <plugin>
      <name>B2BKing</name>
      <version>5.5.30+ </version>
      <purpose>Handles the quote request workflow. Homeowners request quotes from contractors, contractors submit bids. Customer groups: "Homeowner" vs "Contractor".</purpose>
    </plugin>
    <plugin>
      <name>TeraWallet (free) — replacing WPSwings Wallet from old plan</name>
      <version>latest</version>
      <purpose>Escrow-style deposits. Homeowner tops up wallet, funds are held when a job is accepted, released to contractor on completion. Reason for swap: TeraWallet is free + actively maintained + does what WPSwings does for this use case. Document this decision in README.</purpose>
    </plugin>
    <plugin>
      <name>WooCommerce</name>
      <purpose>Required by B2BKing and TeraWallet. Powers the product catalog (services as products), order processing.</purpose>
    </plugin>
    <plugin>
      <name>Elementor (free)</name>
      <purpose>Felan requires it for page building.</purpose>
    </plugin>
    <plugin>
      <name>Rank Math SEO (free)</name>
      <purpose>Local SEO, schema.org LocalBusiness markup, sitemap. Critical for a directory site.</purpose>
    </plugin>
  </plugins_included>

  <plugins_explicitly_dropped>
    <plugin name="Better Messages Pro" reason="FluentCommunity 2.x now has native real-time messaging. Running both = redundant + confusing UX. Drop it." />
    <plugin name="WPSwings Wallet Pro" reason="Paid plugin, and TeraWallet (free) does the same thing for our escrow-style use case. Saves licensing cost for a portfolio build." />
  </plugins_explicitly_dropped>

  <what_is_outdated_in_original_plan>
    <item>"FluentCommunity Beta with Toolkit" language is obsolete — plugin is stable v2.x now, install directly.</item>
    <item>Better Messages Pro duplicates FluentCommunity's built-in messaging.</item>
    <item>No SEO plan in original doc. For a directory site, that's the whole game.</item>
    <item>No schema.org markup plan. LocalBusiness schema is essential for trades.</item>
    <item>No plan for HPOS (WooCommerce High-Performance Order Storage), which is default in modern WooCommerce.</item>
    <item>Original doc had a product assembly feel with no thesis. TradieHub has a thesis: "trades directory with escrow-protected deposits."</item>
  </what_is_outdated_in_original_plan>
</stack_decisions>

---

<product_thesis>
  <problem>Hiring a contractor in California is stressful. Homeowners don't know if the contractor is licensed, worry about upfront deposits disappearing, and have no way to see community reviews from other contractors (who know the industry better than random customers).</problem>

  <solution>TradieHub is a directory + marketplace + community where:
    1. Contractors create verified profiles (license number, CSLB lookup, service areas, portfolio photos).
    2. Homeowners post jobs with budget + location. Contractors submit quotes.
    3. When a homeowner accepts a quote, they pay a deposit into TradieHub's wallet (held in escrow style). Funds release to the contractor when the job is marked complete.
    4. Contractors have a private community to discuss jobs, tools, code compliance, referrals. This is the retention hook — contractors come back for the community even when they don't have active jobs.</solution>

  <differentiator>Most trades directories (Angi, Thumbtack, HomeAdvisor) just pass leads and charge contractors per-lead fees. TradieHub's escrow-style deposit protects both sides of the deal. That's the pitch.</differentiator>

  <portfolio_story>"I built a trades marketplace that goes beyond lead generation — it holds deposits in a wallet system to protect both homeowners and contractors, wraps it in a SEO-optimized directory, and gives contractors their own community space. Built on WordPress using Felan theme + FluentCommunity + B2BKing + TeraWallet, with custom PHP code tying the wallet to the quote workflow."</portfolio_story>
</product_thesis>

---

<claude_code_context>
  <important>
    Claude Code runs on a local terminal. It CAN:
    - Write PHP files (child theme, custom plugin)
    - Run WP-CLI commands
    - Edit wp-config.php, .htaccess, nginx configs
    - Create/edit files in the WordPress install
    - Write SQL, bash scripts, markdown docs

    Claude Code CANNOT (user must do manually):
    - Log into wp-admin and click around
    - Activate premium plugin licenses
    - Import Felan demo content via the theme's visual importer
    - Configure Elementor templates visually
    - Upload plugin ZIPs from ThemeForest/CodeCanyon (unless user provides them)

    For anything wp-admin-only, Claude Code should OUTPUT clear "MANUAL STEP" instructions with screenshots of what to look for.
  </important>

  <working_directory>The LocalWP site's `app/public` folder. Claude Code should `cd` there before most operations.</working_directory>
</claude_code_context>

---

<phase_0_repo_and_workspace_setup>
  <goal>Set up the GitHub repo structure and local dev environment BEFORE touching WordPress.</goal>

  <tasks>
    <task>
      <title>Initialize public GitHub repo</title>
      <instructions>
        Create a new public repo named `tradiehub-wordpress`. Structure:
        ```
        tradiehub-wordpress/
        ├── README.md                    # Main portfolio doc
        ├── design.md                    # This file (for reference)
        ├── docs/
        │   ├── screenshots/             # All screenshots go here
        │   ├── architecture.md          # System diagrams, decisions
        │   ├── seo-strategy.md          # SEO approach for a directory site
        │   └── decisions/               # ADRs (Architecture Decision Records)
        │       ├── 001-why-terawallet-over-wpswings.md
        │       ├── 002-why-drop-better-messages.md
        │       └── 003-why-felan-theme.md
        ├── wp-content/
        │   ├── themes/
        │   │   └── felan-child/         # Custom child theme code
        │   └── plugins/
        │       └── tradiehub-core/      # Custom plugin (the "glue" code)
        ├── scripts/
        │   ├── setup.sh                 # Full WP-CLI setup script
        │   ├── seed-demo-data.sh        # Creates sample contractors, jobs, etc
        │   └── export-db.sh             # DB export for versioning
        └── .gitignore
        ```

        IMPORTANT: `.gitignore` should exclude the full WordPress install. Only commit:
        - The child theme (`felan-child/`)
        - The custom plugin (`tradiehub-core/`)
        - The scripts and docs

        The parent Felan theme and third-party plugins are NOT committed (licensing + size). README instructs viewers on how to recreate the stack.
      </instructions>
    </task>

    <task>
      <title>Create LocalWP site</title>
      <instructions>
        MANUAL STEP for user: Open LocalWP → "Create a new site" → name it "tradiehub" → PHP 8.3 → nginx → MariaDB → create admin user (use a memorable dev password).

        After LocalWP creates the site, Claude Code should:
        1. Navigate to `~/Local Sites/tradiehub/app/public` (or wherever LocalWP puts it on the user's OS)
        2. Confirm WordPress is installed by running `wp core version`
        3. Create a symlink or instruct the user on where to clone the GitHub repo so the `wp-content/themes/felan-child` and `wp-content/plugins/tradiehub-core` paths line up
      </instructions>
    </task>
  </tasks>
</phase_0_repo_and_workspace_setup>

---

<phase_1_wordpress_baseline>
  <goal>Get a clean WordPress install with the required infrastructure plugins, before adding Felan/FluentCommunity/B2BKing.</goal>

  <tasks>
    <task>
      <title>WP-CLI baseline configuration</title>
      <script>
        ```bash
        # All commands run from app/public

        # Update core to latest
        wp core update

        # Set timezone and locale
        wp option update timezone_string "America/Los_Angeles"
        wp option update WPLANG "en_US"

        # Permalinks: /%postname%/ is best for SEO
        wp rewrite structure '/%postname%/' --hard

        # Site identity
        wp option update blogname "TradieHub"
        wp option update blogdescription "Find licensed contractors in California"

        # Discourage search engines while in dev (MANUALLY FLIP OFF when deploying)
        wp option update blog_public 0

        # Disable comments by default on posts (directory doesn't need blog comments)
        wp option update default_comment_status closed
        wp option update default_ping_status closed
        ```
      </script>
    </task>

    <task>
      <title>Install helper plugins</title>
      <script>
        ```bash
        # SEO
        wp plugin install seo-by-rank-math --activate

        # WooCommerce (required by B2BKing + TeraWallet)
        wp plugin install woocommerce --activate

        # Elementor (required by Felan)
        wp plugin install elementor --activate

        # Wallet (our escrow-style system)
        wp plugin install woo-wallet --activate

        # FluentCommunity Free tier (Pro installed later as ZIP)
        wp plugin install fluent-community --activate

        # Utility: WP-CLI tools for dev
        wp plugin install query-monitor --activate
        ```
      </script>
    </task>

    <task>
      <title>WooCommerce baseline setup (skip the wizard, configure via WP-CLI)</title>
      <script>
        ```bash
        # Currency + country
        wp option update woocommerce_currency "USD"
        wp option update woocommerce_default_country "US:CA"
        wp option update woocommerce_store_address "123 Market St"
        wp option update woocommerce_store_city "San Francisco"
        wp option update woocommerce_store_postcode "94103"

        # Enable HPOS (High-Performance Order Storage) — modern WooCommerce default
        wp option update woocommerce_custom_orders_table_enabled "yes"

        # Disable WooCommerce marketing emails/upsells
        wp option update woocommerce_show_marketplace_suggestions "no"
        wp option update woocommerce_allow_tracking "no"

        # Disable guest checkout — we need user accounts for quotes/wallet
        wp option update woocommerce_enable_guest_checkout "no"
        ```
      </script>
    </task>
  </tasks>
</phase_1_wordpress_baseline>

---

<phase_2_felan_theme>
  <goal>Install Felan parent theme + create a child theme with custom styling.</goal>

  <tasks>
    <task>
      <title>Install Felan parent theme</title>
      <instructions>
        MANUAL STEP: User needs to obtain the Felan theme ZIP from ThemeForest (it's a paid theme, ~$59). Place the ZIP at `~/Downloads/felan.zip`.

        Then Claude Code runs:
        ```bash
        wp theme install ~/Downloads/felan.zip
        # Don't activate yet — we activate the child theme
        ```

        Install recommended plugins Felan bundles (usually Felan Core, Redux Framework, etc.):
        ```bash
        # After parent theme install, Felan prompts for required plugins
        # These install automatically via TGMPA on first dashboard visit
        # MANUAL STEP: visit wp-admin dashboard, accept Felan's recommended plugin installs
        ```
      </instructions>
    </task>

    <task>
      <title>Create Felan Child Theme</title>
      <instructions>
        Create `wp-content/themes/felan-child/` with:

        **style.css** (required child theme header):
        ```css
        /*
        Theme Name: Felan Child — TradieHub
        Template: felan
        Version: 1.0.0
        Description: TradieHub custom child theme for Felan. Overrides typography, colors, and adds contractor-specific templates.
        Author: Liam
        */
        ```

        **functions.php**:
        ```php
        <?php
        if (!defined('ABSPATH')) exit;

        // Enqueue parent + child styles
        add_action('wp_enqueue_scripts', function () {
            $parent = wp_get_theme()->parent();
            wp_enqueue_style('felan-parent', get_template_directory_uri() . '/style.css', [], $parent->get('Version'));
            wp_enqueue_style('felan-child', get_stylesheet_uri(), ['felan-parent'], wp_get_theme()->get('Version'));
        });

        // Child theme bootstrap file
        require_once get_stylesheet_directory() . '/inc/customizations.php';
        ```

        **inc/customizations.php**: placeholder for custom hooks that will come in later phases (contractor profile fields, quote-to-wallet bridge, etc.).

        Then activate:
        ```bash
        wp theme activate felan-child
        ```
      </instructions>
    </task>

    <task>
      <title>Felan demo content import</title>
      <instructions>
        MANUAL STEP (can't be done via WP-CLI reliably for Felan's importer):
        1. Go to wp-admin → Felan → Demo Import
        2. Pick the "Freelance Marketplace" demo (closest to trades directory)
        3. Import it
        4. Once imported, we'll customize it in Phase 3

        Take a screenshot after import → save to `docs/screenshots/01-felan-demo-imported.png`
      </instructions>
    </task>
  </tasks>
</phase_2_felan_theme>

---

<phase_3_custom_plugin_tradiehub_core>
  <goal>Build the custom plugin `tradiehub-core` that wires everything together. This is where the portfolio code lives — it's what differentiates this from "I installed plugins."</goal>

  <tasks>
    <task>
      <title>Create plugin skeleton</title>
      <instructions>
        Create `wp-content/plugins/tradiehub-core/tradiehub-core.php`:

        ```php
        <?php
        /**
         * Plugin Name: TradieHub Core
         * Description: Custom integration layer for TradieHub — wires Felan, FluentCommunity, B2BKing, and TeraWallet into a cohesive contractor marketplace.
         * Version: 1.0.0
         * Author: Liam
         * Requires PHP: 8.1
         * Requires at least: 6.5
         */

        if (!defined('ABSPATH')) exit;

        define('TRADIEHUB_VERSION', '1.0.0');
        define('TRADIEHUB_PATH', plugin_dir_path(__FILE__));
        define('TRADIEHUB_URL', plugin_dir_url(__FILE__));

        // Core modules
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
        ```

        Each referenced class is a module. Build them in subsequent tasks.
      </instructions>
    </task>

    <task>
      <title>Module: class-contractor-profile.php</title>
      <purpose>Extend user profiles with contractor-specific fields: CSLB license number, service areas (California zip codes), specialties (plumbing, electrical, etc.), years in business, insurance status.</purpose>
      <key_fields>
        - `cslb_license_number` (string, validated format)
        - `service_specialties` (multi-select taxonomy)
        - `service_zip_codes` (array of CA zips)
        - `years_in_business` (int)
        - `has_liability_insurance` (bool)
        - `has_workers_comp` (bool)
        - `license_verified_at` (datetime, for future CSLB API integration)
      </key_fields>
      <instructions>
        Use WordPress user meta + custom taxonomies. Expose a `[tradiehub_contractor_profile]` shortcode for front-end display. Include basic CSLB format validation (regex: 6-8 digits). Leave the actual CSLB lookup as a TODO with a clear code comment — that's a portfolio talking point ("I scoped the CSLB verification but didn't implement the live API call, here's how I'd do it").
      </instructions>
    </task>

    <task>
      <title>Module: class-quote-workflow.php</title>
      <purpose>Job posting + quote submission workflow. A homeowner creates a "job post" (custom post type `tradiehub_job`). Contractors submit quotes (custom post type `tradiehub_quote`) linked to the job.</purpose>
      <key_behavior>
        - CPT: `tradiehub_job` with fields: title, description, zip, budget range, trade category, deadline
        - CPT: `tradiehub_quote` with fields: parent job ID, contractor user ID, quote amount, estimated completion time, message
        - On quote submission: email the homeowner, create FluentCommunity notification
        - On quote acceptance: trigger the escrow wallet flow (Phase 3 next module)
      </key_behavior>
      <instructions>
        Use standard WordPress CPT registration + REST API exposure (rest_base). Build frontend submission via Gutenberg block OR Elementor widget (pick whichever is easier — Elementor is likely easier since Felan already uses it). Store quote status as post_status: `pending`, `accepted`, `rejected`, `completed`.
      </instructions>
    </task>

    <task>
      <title>Module: class-escrow-wallet.php (the portfolio crown jewel)</title>
      <purpose>Bridge between the quote workflow and TeraWallet. When a homeowner accepts a quote, transfer the quoted amount from their wallet into a "held" state. When the job is marked complete by both parties, release funds to the contractor.</purpose>
      <key_behavior>
        - New wallet transaction type: `tradiehub_escrow_held`
        - Action `tradiehub_quote_accepted`: debit homeowner wallet, mark as held (custom meta)
        - Action `tradiehub_job_completed`: credit contractor wallet, clear held status
        - Action `tradiehub_job_disputed`: open a dispute (admin-moderated), funds stay held
        - Admin dashboard page listing all currently-held escrow amounts
      </key_behavior>
      <instructions>
        TeraWallet (woo-wallet) exposes `woo_wallet()->wallet->credit($user_id, $amount, $description)` and `->debit(...)`. Use those as the primitives. Store escrow state in a custom DB table `wp_tradiehub_escrow` with columns: id, job_id, quote_id, homeowner_id, contractor_id, amount, status, created_at, released_at.

        Write this as the most-commented, most-testable module in the plugin. It's what you'll point to in interviews. Include at least 2-3 PHPUnit-style tests (or just well-structured WP-CLI test commands) that simulate the escrow flow end-to-end.
      </instructions>
    </task>

    <task>
      <title>Module: class-local-seo.php</title>
      <purpose>Inject schema.org LocalBusiness + Service structured data on contractor profile pages and service pages. This is what makes a directory site rank.</purpose>
      <key_behavior>
        - On contractor profile pages: output JSON-LD `LocalBusiness` schema with contractor name, address (zip only for privacy), service area, ratings aggregate
        - On service category pages (e.g., "Plumbers in Los Angeles"): output JSON-LD `ItemList` of contractors
        - Generate "City × Trade" landing pages programmatically for top California metros: `/plumbers-in-los-angeles/`, `/electricians-in-san-francisco/`, `/hvac-contractors-in-san-diego/`, etc. Use a rewrite rule + template.
      </key_behavior>
      <instructions>
        Major cities to cover: Los Angeles, San Francisco, San Diego, Sacramento, San Jose, Fresno, Long Beach, Oakland, Bakersfield, Anaheim.
        Major trades to cover: Plumbing, Electrical, HVAC, Roofing, Landscaping, General Contracting, Painting, Flooring, Remodeling, Pest Control.

        That's 10 cities × 10 trades = 100 programmatic landing pages. Each gets unique content (pulled from contractor listings matching the city + trade) + proper schema markup. This is a MASSIVE SEO win and a portfolio talking point ("I generated 100+ SEO landing pages programmatically").
      </instructions>
    </task>
  </tasks>
</phase_3_custom_plugin_tradiehub_core>

---

<phase_4_b2bking_configuration>
  <goal>Configure B2BKing for the quote workflow. Most of this is admin UI, so Claude Code's job is to write clear MANUAL STEP instructions with expected screenshots.</goal>

  <tasks>
    <task>
      <title>Install B2BKing</title>
      <instructions>
        MANUAL STEP: User obtains B2BKing ZIP from CodeCanyon (~$179/year). Place at `~/Downloads/b2bking.zip`.

        ```bash
        wp plugin install ~/Downloads/b2bking.zip --activate
        ```

        Activate license MANUALLY in wp-admin → B2BKing → License.
      </instructions>
    </task>

    <task>
      <title>Configure customer groups</title>
      <instructions>
        MANUAL STEPS in wp-admin → B2BKing → Customer Groups:
        1. Create group "Homeowner" — can post jobs, request quotes, cannot see contractor-only content
        2. Create group "Contractor" — can submit quotes, access contractor community, cannot post jobs
        3. Create group "Admin" — full access, can moderate disputes

        Screenshot each group after creation → `docs/screenshots/04-b2bking-groups.png`
      </instructions>
    </task>

    <task>
      <title>Configure quote request workflow</title>
      <instructions>
        MANUAL STEPS in wp-admin → B2BKing → Settings → Quotes:
        - Enable "Request a Quote" feature
        - Set to "Per Product" mode (each service listing can be quoted)
        - Enable the quote conversation feature (back-and-forth messaging on quotes)
        - Disable "Pay from Quote" since we're handling payment through the wallet escrow flow, not direct quote-to-order
      </instructions>
    </task>
  </tasks>
</phase_4_b2bking_configuration>

---

<phase_5_fluentcommunity_setup>
  <goal>Set up the contractor community. This is the retention engine.</goal>

  <tasks>
    <task>
      <title>Install FluentCommunity Pro</title>
      <instructions>
        MANUAL STEP: User obtains FluentCommunity Pro ZIP (paid). Place at `~/Downloads/fluent-community-pro.zip`.

        ```bash
        wp plugin install ~/Downloads/fluent-community-pro.zip --activate
        ```

        Activate license in wp-admin.
      </instructions>
    </task>

    <task>
      <title>Create community spaces</title>
      <instructions>
        MANUAL STEPS in wp-admin → FluentCommunity → Spaces:
        Create these spaces (all private, contractor-only):
        - "General Contractor Talk" (public to all contractors)
        - "Electricians CA" (trade-specific)
        - "Plumbers CA"
        - "HVAC CA"
        - "Roofers CA"
        - "Code Compliance Q&A" (a pinned resource space)
        - "Job Leads Exchange" (contractors refer each other when they can't take a job)

        Each space gets a short welcome message. Gate space access via role — only users in "Contractor" group (via B2BKing integration) can access.
      </instructions>
    </task>

    <task>
      <title>Enable real-time messaging</title>
      <instructions>
        MANUAL STEPS in wp-admin → FluentCommunity → Settings → Messaging:
        - Enable real-time messaging
        - Enable direct messages between users (for homeowner ↔ contractor quote discussions)
        - Disable media upload size limit beyond 5MB (keep bandwidth reasonable)

        NOTE: This replaces the Better Messages Pro from the original plan. Document this choice in `docs/decisions/002-why-drop-better-messages.md`.
      </instructions>
    </task>
  </tasks>
</phase_5_fluentcommunity_setup>

---

<phase_6_page_structure>
  <goal>Build the core pages using Felan's Elementor blocks + plugin shortcodes.</goal>

  <page_map>
    <page url="/" title="Home">Hero with "Find a contractor in California" search, featured trades, recent job posts, testimonials.</page>
    <page url="/contractors/" title="Find Contractors">Directory listing, filterable by trade + zip code.</page>
    <page url="/plumbers-in-[city]/" title="Programmatic SEO landing pages">Generated by class-local-seo.php, one per city×trade combo.</page>
    <page url="/post-a-job/" title="Post a Job">Homeowner-facing job posting form (tradiehub-core CPT submission).</page>
    <page url="/my-jobs/" title="My Jobs">Homeowner dashboard: posted jobs, received quotes, active projects.</page>
    <page url="/my-quotes/" title="My Quotes">Contractor dashboard: submitted quotes, won jobs, active projects.</page>
    <page url="/wallet/" title="Wallet">TeraWallet dashboard. `[woo_wallet]` shortcode.</page>
    <page url="/community/" title="Community">FluentCommunity entry point (contractors only).</page>
    <page url="/dashboard/" title="Dashboard">Unified: wallet balance, active jobs/quotes, community notifications.</page>
    <page url="/profile/[username]/" title="Contractor profile">Public contractor profile page with LocalBusiness schema.</page>
  </page_map>

  <instructions>
    For each page, create via WP-CLI:
    ```bash
    wp post create --post_type=page --post_title="Find Contractors" --post_status=publish --post_name=contractors
    # ...repeat for each page
    ```

    Then MANUAL STEP: open each page in Elementor and drop in the right shortcodes/widgets. Take a screenshot of each finished page → `docs/screenshots/`.
  </instructions>
</phase_6_page_structure>

---

<phase_7_seo_and_local_landing_pages>
  <goal>SEO is the single biggest lever for a directory site. This phase is a huge portfolio signal.</goal>

  <tasks>
    <task>
      <title>Rank Math baseline</title>
      <script>
        ```bash
        # Configure via wp option (Rank Math stores settings in options)
        wp option update rank-math-options-general '{"local_business_type":"LocalBusiness","local_address_format":"{address} {locality}, {region} {postalcode}"}' --format=json
        ```

        MANUAL STEPS in Rank Math wizard:
        - Business type: "Small Business"
        - Set social profiles (even dummy ones for portfolio)
        - Enable sitemap (default on)
        - Enable schema: Article, LocalBusiness, Person
      </script>
    </task>

    <task>
      <title>Schema.org injection (handled by tradiehub-core, verify output)</title>
      <instructions>
        After Phase 3's `class-local-seo.php` is built, verify schema output:
        ```bash
        # Fetch a contractor profile page and extract JSON-LD
        curl -s http://tradiehub.local/contractors/sample-plumber/ | grep -A 50 'application/ld+json'
        ```

        Paste the output into `docs/seo-strategy.md` as proof of implementation. Also test with Google's Rich Results Test tool — screenshot the result → `docs/screenshots/10-schema-validation.png`.
      </instructions>
    </task>

    <task>
      <title>Programmatic SEO landing pages generation</title>
      <instructions>
        Write a WP-CLI command in tradiehub-core: `wp tradiehub generate-seo-pages`. It iterates the 10 cities × 10 trades matrix and generates a page for each combo. Each page has:
        - H1: "Top [Trade] in [City], CA"
        - Intro paragraph (100-150 words, unique per combo — use templated variations)
        - List of contractors matching (city, trade)
        - FAQ section (5 common questions per trade)
        - LocalBusiness schema + BreadcrumbList schema
        - Link back to main /contractors/ directory

        Reference: this pattern is how Zillow, Yelp, and Angi built their SEO. Document this in `docs/seo-strategy.md` with a comparison to those sites.
      </instructions>
    </task>

    <task>
      <title>robots.txt + sitemap</title>
      <instructions>
        Rank Math auto-generates sitemap.xml. Verify it includes the programmatic landing pages:
        ```bash
        curl http://tradiehub.local/sitemap_index.xml
        ```

        Screenshot the sitemap → `docs/screenshots/11-sitemap.png` as proof the 100+ landing pages are indexed.
      </instructions>
    </task>
  </tasks>
</phase_7_seo_and_local_landing_pages>

---

<phase_8_seed_demo_data>
  <goal>Populate the site with believable demo data so screenshots look real. Empty sites screenshot badly.</goal>

  <tasks>
    <task>
      <title>Write scripts/seed-demo-data.sh</title>
      <instructions>
        Create a WP-CLI script that generates:
        - 15 contractor users (realistic names, CA addresses, varied trades, avatar URLs from unsplash or dicebear)
        - 8 homeowner users
        - 12 job posts (varied trades, cities, budgets)
        - 25 quotes (each job gets 1-3 quotes)
        - 4 "completed" jobs with released escrow transactions
        - Community posts in each FluentCommunity space (3-5 per space)

        Use realistic copy. NOT "Lorem ipsum" — write sample job descriptions like:
        - "Need a licensed electrician in Pasadena to install a 240V outlet for my EV charger. Home is 1960s build, panel has space but may need upgrade. Looking for quotes this week."
        - "Bathroom remodel in Oakland, 2-bed apartment. Replacing tub with walk-in shower, new tile, new vanity. Have the materials already. Need licensed plumber + general."

        This realism pays off in screenshots.
      </instructions>
    </task>
  </tasks>
</phase_8_seed_demo_data>

---

<phase_9_documentation_readme>
  <goal>The README is the portfolio piece. Spend real effort here.</goal>

  <readme_structure>
    ```markdown
    # TradieHub

    > A licensed-contractor directory and marketplace for California, with escrow-protected deposits and a contractor-only community.

    ![TradieHub homepage screenshot](docs/screenshots/01-homepage.png)

    ## The Problem

    Hiring a contractor in California is stressful. Homeowners worry about...
    (200 words, problem-focused)

    ## The Solution

    TradieHub differs from Angi and Thumbtack by...
    (200 words, with a side-by-side comparison table)

    ## Features

    - ✅ Licensed contractor directory (10 cities × 10 trades = 100 SEO landing pages)
    - ✅ Job posting + quote request workflow
    - ✅ Escrow-style wallet (funds held until job completion)
    - ✅ Contractor-only community (trade-specific spaces)
    - ✅ Real-time messaging between homeowners and contractors
    - ✅ Schema.org LocalBusiness markup for SEO

    ## Screenshots

    [Grid of 8-12 screenshots with captions]

    ## Stack

    [Table of plugins, theme, versions, and WHY each was chosen]

    ## Architecture

    [Link to docs/architecture.md with a diagram]

    ## Key Technical Decisions

    - [Why TeraWallet over WPSwings](docs/decisions/001-why-terawallet-over-wpswings.md)
    - [Why we dropped Better Messages Pro](docs/decisions/002-why-drop-better-messages.md)
    - [Why Felan theme](docs/decisions/003-why-felan-theme.md)

    ## Custom Code

    The `tradiehub-core` plugin is where the interesting code lives:

    - **Escrow Wallet Bridge** — ties TeraWallet transactions to the quote workflow lifecycle
    - **Programmatic SEO Generator** — generates 100+ landing pages via WP-CLI
    - **Contractor Profile Extensions** — custom fields with CSLB format validation

    [View source →](wp-content/plugins/tradiehub-core/)

    ## Running Locally

    1. Clone this repo
    2. Set up a LocalWP site (PHP 8.3, nginx, MariaDB)
    3. Symlink `wp-content/themes/felan-child/` and `wp-content/plugins/tradiehub-core/` into your LocalWP install
    4. Obtain paid plugins (Felan, B2BKing, FluentCommunity Pro) — licensed separately
    5. Run `scripts/setup.sh`
    6. Run `scripts/seed-demo-data.sh`
    7. Visit `http://tradiehub.local`

    ## What I Learned

    [200 words, honest reflection — what was hard, what would I do differently]

    ## What's Next

    - Live CSLB license verification API
    - Stripe integration for wallet top-ups (currently mocked)
    - Mobile app via React Native (share auth with WordPress REST API)

    ---

    Built by [Liam](https://liam-portfolio-omega.vercel.app/)
    ```
  </readme_structure>

  <instructions>
    Claude Code should draft this README as a starting point. User (Liam) will refine the "What I Learned" and "Problem" sections personally since those are voice-critical.
  </instructions>
</phase_9_documentation_readme>

---

<phase_10_screenshots_checklist>
  <goal>Screenshots are what recruiters actually see. Be deliberate.</goal>

  <required_screenshots>
    1. `01-homepage.png` — hero + search
    2. `02-contractor-directory.png` — filterable list
    3. `03-contractor-profile.png` — individual profile with schema visible via browser devtools
    4. `04-post-a-job.png` — job posting form
    5. `05-quote-inbox.png` — contractor's quote dashboard
    6. `06-wallet-with-escrow.png` — wallet balance showing a held escrow transaction
    7. `07-community-feed.png` — FluentCommunity space
    8. `08-messaging.png` — real-time DM between users
    9. `09-seo-landing-page.png` — one of the programmatic city×trade pages
    10. `10-schema-validation.png` — Google Rich Results Test passing
    11. `11-sitemap.png` — sitemap showing all indexed pages
    12. `12-admin-escrow-dashboard.png` — admin view of all held escrow funds
  </required_screenshots>

  <tips>
    - Use a clean browser profile (no extensions visible)
    - Consistent 1440×900 or 1920×1080 window size
    - Demo data should have realistic names, not "test1 user"
    - For mobile responsiveness, include 2-3 mobile screenshots at 390×844 (iPhone 14)
  </tips>
</phase_10_screenshots_checklist>

---

<what_user_liam_does_manually>
  <step>Buy Felan theme (ThemeForest ~$59)</step>
  <step>Buy B2BKing (CodeCanyon ~$179/yr)</step>
  <step>Buy FluentCommunity Pro (~$129/yr)</step>
  <step>Set up LocalWP site</step>
  <step>Accept Felan's TGMPA-required plugins on first dashboard visit</step>
  <step>Import Felan demo content via theme's importer</step>
  <step>Activate all plugin licenses in wp-admin</step>
  <step>Configure B2BKing customer groups (UI-only)</step>
  <step>Create FluentCommunity spaces (UI-only)</step>
  <step>Build Elementor page layouts for /post-a-job, /contractors, /dashboard, profile pages</step>
  <step>Take all screenshots</step>
  <step>Write "What I Learned" README section personally</step>
  <step>Push to GitHub</step>
  <step>Add project card to portfolio site</step>

  <total_licensing_cost_note>~$370-400 in plugin licenses. If budget is tight, consider: (a) doing the build without B2BKing/FluentCommunity Pro and using free alternatives with lesser features, or (b) documenting the "ideal" stack in README while building with free equivalents.</total_licensing_cost_note>
</what_user_liam_does_manually>

---

<what_claude_code_does>
  <step>Scaffolds GitHub repo structure</step>
  <step>Runs all WP-CLI baseline commands</step>
  <step>Installs free plugins (WooCommerce, Elementor, TeraWallet, Rank Math, FluentCommunity free)</step>
  <step>Writes the Felan child theme</step>
  <step>Writes the entire `tradiehub-core` custom plugin (the 4 module classes)</step>
  <step>Writes `scripts/setup.sh` for reproducible environment bootstrapping</step>
  <step>Writes `scripts/seed-demo-data.sh` with realistic demo data</step>
  <step>Writes the WP-CLI command `wp tradiehub generate-seo-pages`</step>
  <step>Writes schema.org JSON-LD injection logic</step>
  <step>Writes draft README.md and ADR docs</step>
  <step>Creates pages via `wp post create`</step>
  <step>Writes .gitignore to exclude WordPress core and third-party plugins</step>
  <step>Validates schema via curl + Rich Results Test URL generation</step>
</what_claude_code_does>

---

<execution_notes>
  <order>Execute phases 0 → 10 in order. Don't skip Phase 0 (repo setup); committing incrementally throughout the build is part of the portfolio story.</order>
  <commit_strategy>Commit at the end of each phase with a descriptive message. Tag phase completion: `git tag phase-3-complete`, etc.</commit_strategy>
  <questions_policy>If anything in this doc is ambiguous, Claude Code should ASK rather than invent. The user (Liam) prefers clarifying questions over wasted work.</questions_policy>
  <style_note>Liam prefers no em-dashes in written content. Use commas, periods, or parentheses. Apply to all generated docs, README, and code comments.</style_note>
</execution_notes>

---

<claude_code_setup>
  <goal>Set up Claude Code's extensibility layer BEFORE executing phases. This gives Claude Code better context, keeps the main session clean, and makes the workflow reproducible.</goal>

  <priority>HIGH — do this before Phase 0 in Claude Code itself (not in the WordPress install).</priority>

  <what_to_create>
    <file path=".claude/CLAUDE.md" purpose="Always-on project rules Claude Code reads every session" />
    <file path=".claude/agents/wordpress-developer.md" purpose="Subagent for heavy WordPress tasks (reading plugin source, verifying hooks, inspecting DB schema)" />
    <file path=".claude/agents/seo-auditor.md" purpose="Subagent for Phase 7 — audits schema, sitemap, metadata without polluting main context" />
    <file path=".claude/skills/wp-cli-patterns/SKILL.md" purpose="Skill capturing WP-CLI syntax quirks and common commands for this project" />
    <file path=".claude/skills/wordpress-php-conventions/SKILL.md" purpose="Skill capturing WordPress PHP conventions (hooks, nonces, sanitization, capabilities)" />
  </what_to_create>

  <rationale>
    - **CLAUDE.md = always-on context.** Rules that apply to every task (child theme only, no em-dashes, WP-CLI-first approach) go here. Context-cheap because it's shared across every turn.
    - **Subagents = context isolation.** When Claude Code needs to read through 20 Felan template files to understand how a hook works, that noise doesn't need to live in the main session. Spawn the wordpress-developer subagent, it returns a 5-line summary, main context stays clean.
    - **Skills = on-demand knowledge.** WP-CLI syntax and WordPress PHP conventions only matter when actively doing those tasks. Skills are loaded only when the description matches the current work, so they don't waste context otherwise.
  </rationale>
</claude_code_setup>

---

<claude_md_template>
  <file_path>.claude/CLAUDE.md</file_path>
  <content>
    ```markdown
    # TradieHub Project — Always-On Rules

    ## Project Context
    TradieHub is a WordPress-based contractor directory for California. Read `design.md` in the project root for the full build plan. This file contains rules that apply to every session.

    ## Hard Rules (never violate)

    1. **Never edit Felan parent theme files.** All customization goes in `wp-content/themes/felan-child/`.
    2. **Never commit third-party plugin code.** Only `felan-child/`, `tradiehub-core/`, scripts, and docs are committed. The `.gitignore` handles this — don't override it.
    3. **No em-dashes in any written content** (code comments, README, ADRs, commit messages). Use commas, periods, or parentheses.
    4. **All WordPress configuration via WP-CLI when possible.** If a task requires wp-admin clicking, explicitly flag it as "MANUAL STEP" so Liam knows.
    5. **Never paste real CSLB license numbers** into seed data. Use format-valid but fake numbers (e.g., "100001", "100002").

    ## Writing Style
    - Clear, direct, no fluff. Liam prefers concise over verbose.
    - Code comments explain *why*, not *what*. The what is already in the code.
    - Commit messages: `feat:`, `fix:`, `docs:`, `chore:`, `refactor:`. Imperative mood ("add escrow release logic", not "added...").

    ## Stack Quick Reference
    - WordPress 6.7+, PHP 8.3, MariaDB
    - Parent theme: Felan (paid, not committed)
    - Child theme: `felan-child` (committed)
    - Custom plugin: `tradiehub-core` (committed, the portfolio centerpiece)
    - Plugins: FluentCommunity (+ Pro), B2BKing, WooCommerce, TeraWallet (woo-wallet), Elementor, Rank Math SEO

    ## When Confused
    Ask Liam rather than guess. He prefers clarifying questions over wasted work. If the question is small and you're 90%+ confident, proceed and note the assumption in a code comment.

    ## Checkpoint Protocol
    At the end of each phase from design.md:
    1. Commit all changes with a descriptive message
    2. Tag the phase: `git tag phase-N-complete`
    3. Report to Liam: what was done, what needs manual steps, what's next
    4. Wait for Liam's confirmation before starting the next phase
    ```
  </content>
</claude_md_template>

---

<subagent_wordpress_developer>
  <file_path>.claude/agents/wordpress-developer.md</file_path>
  <purpose>Isolated agent for heavy WordPress investigation tasks — reading plugin source, tracing hooks, inspecting DB schema, checking WordPress Core behavior — without polluting the main session's context window.</purpose>
  <content>
    ```markdown
    ---
    name: wordpress-developer
    description: Use for heavy WordPress investigation tasks that would fill the main context with noise. Examples, use this agent proactively when the task requires reading through plugin source code to find a hook, tracing how a WooCommerce action fires, inspecting database schema, or verifying plugin behavior. Do NOT use for writing custom code in tradiehub-core (that stays in main session).
    tools: Read, Grep, Glob, Bash
    model: sonnet
    ---

    You are a WordPress investigation specialist for the TradieHub project. Your job is to do the heavy reading so the main session doesn't have to.

    ## Your Task Pattern
    When invoked:
    1. Understand what the parent agent needs to know (usually one specific question)
    2. Investigate using Read, Grep, Glob across WordPress core, theme, and plugin files
    3. Run WP-CLI commands if needed (`wp eval`, `wp db query`, `wp option get`, etc.)
    4. Return a concise summary — the answer, not the journey

    ## Return Format
    Always structure your response as:

    **Finding:** [one sentence answer to the question]

    **Evidence:** [the specific file paths + line numbers or command outputs that support the finding]

    **Gotchas:** [anything the parent agent should know when acting on this finding — version-specific behavior, known bugs, edge cases]

    ## Common Investigations
    - "Does FluentCommunity fire a hook when a user joins a space?" → grep through fluent-community source for `do_action`
    - "What's the TeraWallet transaction meta schema?" → `wp db query "DESCRIBE wp_woo_wallet_transactions"`
    - "Does B2BKing store quote status as post_status or post_meta?" → read b2bking source + check a sample post
    - "Is there a WordPress function to validate a US zip code?" → check WP core + common plugins before writing custom

    ## Constraints
    - You have read-only intent. Do not edit files. If the parent needs changes, report findings and let them decide.
    - Keep responses under 300 words. If you need more space, you're answering a question that wasn't asked.
    - If a question is ambiguous, ask the parent for clarification rather than guessing.
    ```
  </content>
</subagent_wordpress_developer>

---

<subagent_seo_auditor>
  <file_path>.claude/agents/seo-auditor.md</file_path>
  <purpose>Isolated agent for Phase 7 SEO audits — checking schema output, sitemap coverage, metadata presence, robots.txt — without the main session doing repetitive curl + parse work.</purpose>
  <content>
    ```markdown
    ---
    name: seo-auditor
    description: Use for SEO verification tasks. Examples, use proactively after generating programmatic landing pages to verify schema.org markup is present and valid, after configuring Rank Math to check sitemap coverage, and before marking Phase 7 complete to run a full SEO audit checklist.
    tools: Read, Grep, Bash, WebFetch
    model: sonnet
    ---

    You are an SEO auditor for the TradieHub directory site (California contractors). Your job is to verify that the site has the SEO foundations needed to rank for local queries.

    ## Audit Checklist
    When invoked for a full audit, check each item:

    ### 1. Schema.org markup
    - Contractor profile pages have `LocalBusiness` JSON-LD
    - City × trade landing pages have `ItemList` JSON-LD listing contractors
    - Site-wide has `Organization` JSON-LD
    - Test each URL against Google's Rich Results Test endpoint (construct the URL, don't actually submit)

    ### 2. Sitemap
    - `/sitemap_index.xml` is accessible (200 response)
    - Contains entries for: contractor profiles, city × trade pages, main content pages
    - Total URL count matches expected (at minimum: 100 landing pages + seeded contractor count + ~10 static pages)

    ### 3. Metadata
    - Every public page has a unique title tag
    - Every public page has a meta description (not just inherited from theme default)
    - Canonical URLs are self-referential and absolute

    ### 4. Technical
    - `robots.txt` exists and doesn't block the sitemap
    - `blog_public` option is 0 in dev (site is hidden from search engines while building). Remind Liam to flip this when deploying.
    - No `<meta name="robots" content="noindex">` on public-facing content

    ### 5. Content signals
    - H1 tag present and unique per page
    - H2/H3 hierarchy is logical (no skipping levels)
    - Programmatic landing pages have at least 300 words of unique content, not templated boilerplate

    ## Return Format
    Structure findings as a table:

    | Check | Status | Evidence | Severity |
    |-------|--------|----------|----------|
    | LocalBusiness schema on profile pages | ✅ Pass / ⚠️ Partial / ❌ Fail | [URL + line of JSON-LD or curl output] | Critical/Warning/Info |

    End with a prioritized action list. Critical issues first.

    ## Constraints
    - Do not fix issues yourself. Report them. The main session handles fixes.
    - Don't fetch external URLs except Google's testing tools.
    - Keep the audit under 800 words total.
    ```
  </content>
</subagent_seo_auditor>

---

<skill_wp_cli_patterns>
  <file_path>.claude/skills/wp-cli-patterns/SKILL.md</file_path>
  <purpose>On-demand reference for WP-CLI syntax quirks. Loaded when Claude Code is writing WP-CLI commands, keeps the main context clean when not needed.</purpose>
  <content>
    ```markdown
    ---
    name: wp-cli-patterns
    description: Use when writing or debugging WP-CLI commands for the TradieHub project. Covers JSON-encoded options, custom post type creation, user role management, batch operations, and common syntax traps that trip up automation.
    ---

    # WP-CLI Patterns for TradieHub

    ## JSON-valued options (common trap)
    When `wp option update` needs a JSON value, always pass `--format=json` AND escape properly:

    ```bash
    # Correct
    wp option update my_option '{"key":"value","num":42}' --format=json

    # Wrong — WordPress stores it as a literal string
    wp option update my_option '{"key":"value"}'
    ```

    ## Checking option storage format
    Before writing, check if the plugin stores settings serialized or as JSON:

    ```bash
    wp option get my_option --format=json
    wp db query "SELECT option_value FROM wp_options WHERE option_name='my_option'"
    ```

    ## Custom post type creation (one-liner for seed scripts)
    ```bash
    wp post create \
      --post_type=tradiehub_job \
      --post_title="Need an electrician in Pasadena" \
      --post_status=publish \
      --post_author=5 \
      --meta_input='{"budget":"500-1000","zip":"91101","trade":"electrical"}' \
      --porcelain
    ```

    The `--porcelain` flag returns just the new post ID, useful for chaining.

    ## User role operations
    ```bash
    # Create custom roles
    wp role create contractor "Contractor" --clone=subscriber
    wp role create homeowner "Homeowner" --clone=subscriber

    # Add capabilities
    wp cap add contractor publish_tradiehub_quotes

    # Assign role
    wp user set-role 5 contractor
    ```

    ## Batch operations (for seed data)
    Use `xargs` + a loop file for batches:

    ```bash
    # Create 10 contractors from a CSV
    while IFS=, read -r email name zip trade; do
      wp user create "$email" "$email" --role=contractor --first_name="$name" \
        --porcelain
    done < contractors.csv
    ```

    ## Running PHP via wp eval
    For one-off data checks without writing a file:

    ```bash
    wp eval "echo count(get_posts(['post_type' => 'tradiehub_job', 'posts_per_page' => -1]));"
    ```

    ## Flushing rewrite rules (always do this after registering CPTs)
    ```bash
    wp rewrite flush --hard
    ```

    ## Plugin-specific CLI commands available in our stack
    - `wp b2bking ...` — B2BKing has its own subcommands (check `wp help b2bking`)
    - `wp woo-wallet ...` — TeraWallet exposes wallet operations via CLI
    - `wp elementor ...` — Elementor CLI for flushing cache and regenerating CSS

    ## Common debug commands
    ```bash
    # Site health
    wp doctor check --all

    # Check for plugin conflicts
    wp plugin list --status=active --field=name | xargs -I {} wp plugin deactivate {}

    # Quick DB size check (useful before/after seed data)
    wp db size --tables --format=table
    ```

    ## Traps
    - `wp user create` creates but does NOT email the user. Good for seeding, bad if you accidentally run in production.
    - Post meta with nested arrays: use `--meta_input` with `--format=json` or the meta stores as `Array` literal string.
    - Rewrite rules don't auto-flush when you register a CPT via code. Run `wp rewrite flush --hard` after any CPT changes.
    ```
  </content>
</skill_wp_cli_patterns>

---

<skill_wordpress_php_conventions>
  <file_path>.claude/skills/wordpress-php-conventions/SKILL.md</file_path>
  <purpose>On-demand reference for WordPress-specific PHP conventions. Loaded when Claude Code is writing PHP for the child theme or tradiehub-core plugin.</purpose>
  <content>
    ```markdown
    ---
    name: wordpress-php-conventions
    description: Use when writing PHP for TradieHub's child theme or custom plugin. Covers WordPress hooks, nonces, capability checks, sanitization/escaping, WP-standard naming, and the security patterns that separate portfolio-quality WordPress code from amateur code.
    ---

    # WordPress PHP Conventions for TradieHub

    ## Naming
    - Prefix everything user-facing with `tradiehub_` or class-prefix with `TradieHub_`
    - Hook names: lowercase, underscore-separated (`tradiehub_quote_accepted`)
    - Class names: PascalCase with prefix (`TradieHub_Escrow_Wallet`)
    - Function names: snake_case with prefix (`tradiehub_release_escrow`)

    ## The Big Five Security Patterns

    ### 1. Nonces (CSRF protection)
    ```php
    // In the form
    wp_nonce_field('tradiehub_accept_quote', '_tradiehub_nonce');

    // In the handler
    if (!isset($_POST['_tradiehub_nonce']) ||
        !wp_verify_nonce($_POST['_tradiehub_nonce'], 'tradiehub_accept_quote')) {
        wp_die('Security check failed');
    }
    ```

    ### 2. Capability checks
    Never check roles directly. Check capabilities.

    ```php
    // Wrong
    if (current_user_can('contractor')) { ... }

    // Right
    if (!current_user_can('submit_tradiehub_quote')) {
        wp_die('Insufficient permissions');
    }
    ```

    ### 3. Sanitize input
    ```php
    $zip = sanitize_text_field($_POST['zip']);
    $amount = floatval($_POST['amount']);
    $description = wp_kses_post($_POST['description']);  // allows safe HTML
    $email = sanitize_email($_POST['email']);
    $url = esc_url_raw($_POST['url']);
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
        $user_id,
        'held'
    ));
    ```

    ## Hook Patterns

    ### Actions vs Filters
    - **Action:** "something happened" — notify others (`do_action('tradiehub_quote_accepted', $quote_id)`)
    - **Filter:** "transform this value" — modify data (`$text = apply_filters('tradiehub_quote_display_text', $text)`)

    ### Hook with priority
    ```php
    // Default priority is 10, default args is 1
    add_action('woocommerce_order_status_completed', 'tradiehub_release_escrow', 10, 1);
    ```

    ### Custom hooks for extensibility
    Fire custom actions so future code can extend without editing core:

    ```php
    do_action('tradiehub_before_escrow_release', $escrow_id, $amount);
    // ... release logic
    do_action('tradiehub_after_escrow_release', $escrow_id, $amount);
    ```

    ## Custom Post Types (the right way)
    ```php
    register_post_type('tradiehub_job', [
        'labels' => [
            'name' => __('Jobs', 'tradiehub'),
            'singular_name' => __('Job', 'tradiehub'),
        ],
        'public' => true,
        'has_archive' => 'jobs',
        'rewrite' => ['slug' => 'jobs', 'with_front' => false],
        'supports' => ['title', 'editor', 'author', 'custom-fields'],
        'show_in_rest' => true,  // enables Gutenberg + REST API
        'menu_icon' => 'dashicons-hammer',
        'capability_type' => 'tradiehub_job',
        'map_meta_cap' => true,
    ]);
    ```

    ## Custom DB Tables
    Don't shoehorn everything into `postmeta`. For the escrow table, use a real table:

    ```php
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
    ```

    Always use `dbDelta` for table creation, never raw `CREATE TABLE`.

    ## AJAX Endpoints
    ```php
    // Frontend
    add_action('wp_ajax_tradiehub_submit_quote', 'tradiehub_handle_quote_submission');
    add_action('wp_ajax_nopriv_tradiehub_submit_quote', 'tradiehub_handle_anon_quote_attempt');

    function tradiehub_handle_quote_submission() {
        check_ajax_referer('tradiehub_quote', '_nonce');

        if (!current_user_can('submit_tradiehub_quote')) {
            wp_send_json_error('Insufficient permissions', 403);
        }

        // ... logic
        wp_send_json_success(['message' => 'Quote submitted', 'id' => $new_id]);
    }
    ```

    ## REST API (modern preferred approach)
    ```php
    add_action('rest_api_init', function () {
        register_rest_route('tradiehub/v1', '/quote', [
            'methods' => 'POST',
            'callback' => 'tradiehub_rest_submit_quote',
            'permission_callback' => fn() => current_user_can('submit_tradiehub_quote'),
            'args' => [
                'job_id' => ['required' => true, 'type' => 'integer'],
                'amount' => ['required' => true, 'type' => 'number'],
            ],
        ]);
    });
    ```

    ## Never Do These
    - ❌ `SELECT * FROM wp_users` with concatenated input (use `$wpdb->prepare`)
    - ❌ `echo $_POST['anything']` (always sanitize + escape)
    - ❌ `mysql_query()` (deprecated, use `$wpdb`)
    - ❌ Direct file writes without `WP_Filesystem` for cross-host portability
    - ❌ Hardcoded table prefixes (use `$wpdb->prefix`)
    - ❌ `require 'something.php'` without `get_stylesheet_directory()` or similar path function
    ```
  </content>
</skill_wordpress_php_conventions>

---

<setup_order>
  Execute this setup BEFORE Phase 0 of the main build:

  1. Create `.claude/` directory in project root
  2. Create `.claude/CLAUDE.md` with the content above
  3. Create `.claude/agents/wordpress-developer.md`
  4. Create `.claude/agents/seo-auditor.md`
  5. Create `.claude/skills/wp-cli-patterns/SKILL.md`
  6. Create `.claude/skills/wordpress-php-conventions/SKILL.md`
  7. Commit: `git commit -m "chore: set up Claude Code agents and skills"`
  8. Then proceed to Phase 0

  From there on, Claude Code will auto-invoke the subagents and skills when relevant. Liam can also explicitly invoke them:
  - `@wordpress-developer find the hook that fires when a FluentCommunity space is joined`
  - `@seo-auditor run the full SEO audit on the current build`
  - Skills auto-load based on description matching the current task
</setup_order>

---

<how_to_use_this_file_with_claude_code>
  Run Claude Code in the project root. First command:

  > "Read `design.md` and confirm you understand the project. Then set up the `.claude/` directory with CLAUDE.md, agents, and skills as specified in the `<claude_code_setup>` section. After that's committed, execute Phase 0."

  Claude Code will work through each phase, pausing at MANUAL STEP markers to wait for Liam's confirmation that he's completed the wp-admin / UI-only steps.

  If Claude Code runs into a plugin-specific quirk not covered in this doc, it should check:
  - Felan docs: https://ricetheme.gitbook.io/felan-freelance-marketplace-and-job-board-wp
  - FluentCommunity docs: https://fluentcommunity.co/docs
  - B2BKing docs: https://woocommerce-b2b-plugin.com/docs
  - TeraWallet (woo-wallet): https://wordpress.org/plugins/woo-wallet
</how_to_use_this_file_with_claude_code>
