# TradieHub WordPress Build — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Scaffold the full TradieHub repository — Claude Code extensibility layer, Felan child theme, tradiehub-core custom plugin (4 modules), WP-CLI scripts, and portfolio documentation.

**Architecture:** File-creation project targeting a LocalWP environment. All custom code lives in `wp-content/themes/felan-child/` and `wp-content/plugins/tradiehub-core/`. WordPress core, Felan parent theme, and third-party plugins are NOT committed. The plugin modules are decoupled: each class registers its own hooks via a static `init()` method, fired from `plugins_loaded`.

**Tech Stack:** WordPress 6.7+, PHP 8.3, MariaDB, WooCommerce, TeraWallet (woo-wallet), B2BKing, FluentCommunity Pro, Rank Math SEO, Elementor (all installed via WP-CLI or manual ZIP upload as documented in design.md).

---

## File Map

```
tradiehub-wordpress/
├── .claude/
│   ├── CLAUDE.md
│   ├── agents/
│   │   ├── wordpress-developer.md
│   │   └── seo-auditor.md
│   └── skills/
│       ├── wp-cli-patterns/SKILL.md
│       └── wordpress-php-conventions/SKILL.md
├── .gitignore
├── README.md
├── design.md                              (already exists)
├── docs/
│   ├── screenshots/.gitkeep
│   ├── architecture.md
│   ├── seo-strategy.md
│   └── decisions/
│       ├── 001-why-terawallet-over-wpswings.md
│       ├── 002-why-drop-better-messages.md
│       └── 003-why-felan-theme.md
├── wp-content/
│   ├── themes/
│   │   └── felan-child/
│   │       ├── style.css
│   │       ├── functions.php
│   │       └── inc/customizations.php
│   └── plugins/
│       └── tradiehub-core/
│           ├── tradiehub-core.php
│           └── includes/
│               ├── class-activator.php
│               ├── class-contractor-profile.php
│               ├── class-quote-workflow.php
│               ├── class-escrow-wallet.php
│               └── class-local-seo.php
└── scripts/
    ├── setup.sh
    ├── seed-demo-data.sh
    └── export-db.sh
```

---

## Subsystem 1: Infrastructure

### Task 1: Claude Code extensibility setup

**Files:**
- Create: `.claude/CLAUDE.md`
- Create: `.claude/agents/wordpress-developer.md`
- Create: `.claude/agents/seo-auditor.md`
- Create: `.claude/skills/wp-cli-patterns/SKILL.md`
- Create: `.claude/skills/wordpress-php-conventions/SKILL.md`

- [ ] **Step 1: Create .claude/CLAUDE.md**

```markdown
# TradieHub Project — Always-On Rules

## Project Context
TradieHub is a WordPress-based contractor directory for California. Read `design.md` in the project root for the full build plan.

## Hard Rules (never violate)

1. **Never edit Felan parent theme files.** All customization goes in `wp-content/themes/felan-child/`.
2. **Never commit third-party plugin code.** Only `felan-child/`, `tradiehub-core/`, scripts, and docs are committed.
3. **No em-dashes in any written content** (code comments, README, ADRs, commit messages). Use commas, periods, or parentheses.
4. **All WordPress configuration via WP-CLI when possible.** Flag wp-admin-only steps as "MANUAL STEP".
5. **Never paste real CSLB license numbers** into seed data. Use format-valid fake numbers (e.g., "100001").

## Writing Style
- Clear, direct, no fluff.
- Code comments explain *why*, not *what*.
- Commit messages: `feat:`, `fix:`, `docs:`, `chore:`, `refactor:`. Imperative mood.

## Stack Quick Reference
- WordPress 6.7+, PHP 8.3, MariaDB
- Parent theme: Felan (paid, not committed)
- Child theme: `felan-child` (committed)
- Custom plugin: `tradiehub-core` (committed)
- Plugins: FluentCommunity (+ Pro), B2BKing, WooCommerce, TeraWallet (woo-wallet), Elementor, Rank Math SEO

## When Confused
Ask Liam rather than guess. If the question is small and you are 90%+ confident, proceed and note the assumption in a code comment.

## Checkpoint Protocol
At the end of each phase from design.md:
1. Commit all changes with a descriptive message
2. Tag the phase: `git tag phase-N-complete`
3. Report: what was done, what needs manual steps, what is next
4. Wait for Liam's confirmation before starting the next phase
```

- [ ] **Step 2: Create .claude/agents/wordpress-developer.md**

```markdown
---
name: wordpress-developer
description: Use for heavy WordPress investigation tasks that would fill the main context with noise. Examples: reading through plugin source code to find a hook, tracing how a WooCommerce action fires, inspecting database schema, or verifying plugin behavior. Do NOT use for writing custom code in tradiehub-core (that stays in main session).
tools: Read, Grep, Glob, Bash
model: sonnet
---

You are a WordPress investigation specialist for the TradieHub project. Your job is to do the heavy reading so the main session does not have to.

## Your Task Pattern
When invoked:
1. Understand what the parent agent needs to know (usually one specific question)
2. Investigate using Read, Grep, Glob across WordPress core, theme, and plugin files
3. Run WP-CLI commands if needed (`wp eval`, `wp db query`, `wp option get`, etc.)
4. Return a concise summary — the answer, not the journey

## Return Format

**Finding:** [one sentence answer to the question]

**Evidence:** [the specific file paths + line numbers or command outputs that support the finding]

**Gotchas:** [anything the parent agent should know when acting on this finding]

## Constraints
- Read-only intent. Do not edit files.
- Keep responses under 300 words.
- If a question is ambiguous, ask the parent for clarification rather than guessing.
```

- [ ] **Step 3: Create .claude/agents/seo-auditor.md**

```markdown
---
name: seo-auditor
description: Use for SEO verification tasks. Use proactively after generating programmatic landing pages to verify schema.org markup is present and valid, after configuring Rank Math to check sitemap coverage, and before marking Phase 7 complete to run a full SEO audit checklist.
tools: Read, Grep, Bash, WebFetch
model: sonnet
---

You are an SEO auditor for the TradieHub directory site (California contractors).

## Audit Checklist

### 1. Schema.org markup
- Contractor profile pages have `LocalBusiness` JSON-LD
- City x trade landing pages have `ItemList` JSON-LD
- Site-wide has `Organization` JSON-LD

### 2. Sitemap
- `/sitemap_index.xml` is accessible (200 response)
- Contains entries for contractor profiles, city x trade pages, main pages
- Total URL count matches expected (100+ landing pages + contractors + ~10 static pages)

### 3. Metadata
- Every public page has a unique title tag and meta description
- Canonical URLs are self-referential and absolute

### 4. Technical
- `robots.txt` exists and does not block the sitemap
- `blog_public` is 0 in dev. Remind Liam to flip this when deploying.
- No `<meta name="robots" content="noindex">` on public content

### 5. Content signals
- H1 tag present and unique per page
- Programmatic landing pages have at least 300 words of unique content

## Return Format
| Check | Status | Evidence | Severity |
|-------|--------|----------|----------|
| LocalBusiness schema | Pass/Partial/Fail | [URL + evidence] | Critical/Warning/Info |

End with a prioritized action list. Critical issues first.

## Constraints
- Do not fix issues yourself. Report them.
- Keep the audit under 800 words total.
```

- [ ] **Step 4: Create .claude/skills/wp-cli-patterns/SKILL.md**

```markdown
---
name: wp-cli-patterns
description: Use when writing or debugging WP-CLI commands for the TradieHub project. Covers JSON-encoded options, custom post type creation, user role management, batch operations, and common syntax traps.
---

# WP-CLI Patterns for TradieHub

## JSON-valued options (common trap)
```bash
# Correct: always pass --format=json for JSON values
wp option update my_option '{"key":"value","num":42}' --format=json

# Wrong: WordPress stores it as a literal string without --format=json
wp option update my_option '{"key":"value"}'
```

## Checking option storage format
```bash
wp option get my_option --format=json
wp db query "SELECT option_value FROM wp_options WHERE option_name='my_option'"
```

## Custom post type creation (for seed scripts)
```bash
wp post create \
  --post_type=tradiehub_job \
  --post_title="Need an electrician in Pasadena" \
  --post_status=publish \
  --post_author=5 \
  --meta_input='{"budget_min":"500","budget_max":"1000","zip":"91101"}' \
  --porcelain
```
The `--porcelain` flag returns just the new post ID, useful for chaining.

## User role operations
```bash
wp role create contractor "Contractor" --clone=subscriber
wp role create homeowner "Homeowner" --clone=subscriber
wp cap add contractor publish_tradiehub_jobs
wp user set-role 5 contractor
```

## Batch operations (for seed data)
```bash
while IFS=, read -r email name zip trade; do
  wp user create "$email" "$email" --role=tradiehub_contractor \
    --first_name="$name" --porcelain
done < contractors.csv
```

## Running PHP via wp eval
```bash
wp eval "echo count(get_posts(['post_type' => 'tradiehub_job', 'posts_per_page' => -1]));"
```

## Flushing rewrite rules (always after registering CPTs)
```bash
wp rewrite flush --hard
```

## Plugin-specific CLI commands in our stack
- `wp b2bking ...` — B2BKing subcommands (check `wp help b2bking`)
- `wp woo-wallet ...` — TeraWallet operations via CLI
- `wp elementor ...` — Elementor CLI for cache flush
- `wp tradiehub generate-seo-pages` — our custom command in class-local-seo.php

## Common debug commands
```bash
wp doctor check --all
wp db size --tables --format=table
```

## Traps
- `wp user create` does NOT email the user. Good for seeding.
- Post meta with nested arrays: use `--meta_input` with JSON or the meta stores as literal `Array`.
- Rewrite rules do not auto-flush after CPT registration. Always run `wp rewrite flush --hard`.
```

- [ ] **Step 5: Create .claude/skills/wordpress-php-conventions/SKILL.md**

```markdown
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
    !wp_verify_nonce($_POST['_tradiehub_nonce'], 'tradiehub_accept_quote')) {
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
// Action: "something happened"
do_action('tradiehub_quote_accepted', $quote_id);

// Filter: "transform this value"
$text = apply_filters('tradiehub_quote_display_text', $text);

// Hook with priority and arg count
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
- `require 'something.php'` without `get_stylesheet_directory()` or plugin path constant
- Checking roles directly instead of capabilities
```

- [ ] **Step 6: Verify all .claude files created, then commit**

```bash
ls .claude/ .claude/agents/ .claude/skills/wp-cli-patterns/ .claude/skills/wordpress-php-conventions/
git add .claude/
git commit -m "chore: set up Claude Code agents and skills"
```

---

### Task 2: Repo structure and .gitignore (Phase 0)

**Files:**
- Create: `.gitignore`
- Create: `docs/screenshots/.gitkeep`
- Create: `docs/architecture.md` (stub)
- Create: `docs/seo-strategy.md` (stub)
- Create: `docs/decisions/` (directory with stubs)
- Create: `wp-content/themes/felan-child/` (directory)
- Create: `wp-content/plugins/tradiehub-core/includes/` (directory)
- Create: `scripts/` (directory)

- [ ] **Step 1: Initialize git and create directory structure**

```bash
git init
mkdir -p docs/screenshots docs/decisions
mkdir -p wp-content/themes/felan-child/inc
mkdir -p wp-content/plugins/tradiehub-core/includes
mkdir -p scripts
touch docs/screenshots/.gitkeep
```

- [ ] **Step 2: Create .gitignore**

```gitignore
# WordPress core (not committed; LocalWP installs it separately)
wp-admin/
wp-includes/
wp-login.php
wp-blog-header.php
wp-comments-post.php
wp-config.php
wp-cron.php
wp-links-opml.php
wp-load.php
wp-mail.php
wp-settings.php
wp-signup.php
wp-trackback.php
xmlrpc.php
index.php
*.php
!wp-content/themes/felan-child/**/*.php
!wp-content/plugins/tradiehub-core/**/*.php

# WordPress uploads
wp-content/uploads/

# Third-party themes (paid, not committed)
wp-content/themes/felan/
wp-content/themes/felan-*/
!wp-content/themes/felan-child/

# Third-party plugins (not committed)
wp-content/plugins/
!wp-content/plugins/tradiehub-core/

# Environment and OS
.env
.DS_Store
Thumbs.db
*.log

# Build artifacts
node_modules/
vendor/
*.swp
*.swo

# Local WP config
wp-config-local.php
```

- [ ] **Step 3: Commit repo skeleton**

```bash
git add .gitignore docs/ scripts/ wp-content/
git commit -m "chore: initialize repo structure (Phase 0)"
git tag phase-0-complete
```

---

## Subsystem 2: WordPress Theme and Plugin Code

### Task 3: Felan Child Theme (Phase 2)

**Files:**
- Create: `wp-content/themes/felan-child/style.css`
- Create: `wp-content/themes/felan-child/functions.php`
- Create: `wp-content/themes/felan-child/inc/customizations.php`

- [ ] **Step 1: Create style.css**

```css
/*
Theme Name: Felan Child — TradieHub
Template: felan
Version: 1.0.0
Description: TradieHub custom child theme for Felan. Overrides typography, colors, and adds contractor-specific templates.
Author: Liam
*/

/* ============================================================
   TradieHub brand colors
   Primary: #1a6b4a (green — trusted, trades)
   Accent:  #f4a01b (amber — active, call-to-action)
   ============================================================ */

:root {
    --th-primary: #1a6b4a;
    --th-primary-dark: #145539;
    --th-accent: #f4a01b;
    --th-accent-dark: #d4890f;
    --th-text: #1c1c1c;
    --th-muted: #6b7280;
    --th-bg-light: #f9fafb;
    --th-border: #e5e7eb;
    --th-radius: 8px;
}

/* Escrow status badges */
.tradiehub-status-held     { color: #92400e; background: #fef3c7; padding: 2px 8px; border-radius: 4px; font-size: .875rem; }
.tradiehub-status-released { color: #065f46; background: #d1fae5; padding: 2px 8px; border-radius: 4px; font-size: .875rem; }
.tradiehub-status-disputed { color: #7f1d1d; background: #fee2e2; padding: 2px 8px; border-radius: 4px; font-size: .875rem; }

/* Contractor profile badges */
.tradiehub-contractor-profile .badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 4px;
    font-size: .8rem;
    font-weight: 600;
    margin-right: 4px;
}
.badge.verified      { background: #d1fae5; color: #065f46; }
.badge.insurance     { background: #dbeafe; color: #1e40af; }
.badge.workers-comp  { background: #ede9fe; color: #5b21b6; }

/* SEO landing page */
.tradiehub-seo-page .container { max-width: 960px; margin: 0 auto; padding: 2rem 1rem; }
.tradiehub-seo-page h1         { font-size: 2rem; color: var(--th-primary); margin-bottom: 1rem; }
.tradiehub-seo-page .intro     { font-size: 1.1rem; color: var(--th-muted); margin-bottom: 2rem; }
.contractor-list               { list-style: none; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; }
.contractor-card               { border: 1px solid var(--th-border); border-radius: var(--th-radius); padding: 1rem; background: #fff; }
.faq-section                   { margin-top: 3rem; }
.faq-section details           { border: 1px solid var(--th-border); border-radius: var(--th-radius); padding: 1rem; margin-bottom: .75rem; }
.faq-section summary           { font-weight: 600; cursor: pointer; }
.breadcrumb                    { margin-bottom: 1.5rem; color: var(--th-muted); font-size: .9rem; }
.breadcrumb a                  { color: var(--th-primary); text-decoration: none; }
```

- [ ] **Step 2: Create functions.php**

```php
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
```

- [ ] **Step 3: Create inc/customizations.php**

```php
<?php
if (!defined('ABSPATH')) exit;

/*
 * Placeholder for child-theme-specific hooks.
 * Contractor profile display, quote form tweaks, and Elementor widget overrides
 * go here once the tradiehub-core plugin is active and the Felan demo is imported.
 */

// Disable Felan's built-in package listing if B2BKing handles quotes.
// add_filter('felan_show_package_listing', '__return_false');

// Example: add a "Verified Contractor" badge to author archive titles.
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
```

- [ ] **Step 4: Verify PHP syntax**

```bash
php -l wp-content/themes/felan-child/functions.php
php -l wp-content/themes/felan-child/inc/customizations.php
```
Expected: `No syntax errors detected`

- [ ] **Step 5: Commit child theme**

```bash
git add wp-content/themes/felan-child/
git commit -m "feat: add Felan child theme with TradieHub brand styles (Phase 2)"
git tag phase-2-complete
```

---

### Task 4: Plugin skeleton + Activator (Phase 3, part 1)

**Files:**
- Create: `wp-content/plugins/tradiehub-core/tradiehub-core.php`
- Create: `wp-content/plugins/tradiehub-core/includes/class-activator.php`

- [ ] **Step 1: Create tradiehub-core.php**

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

- [ ] **Step 2: Create class-activator.php**

```php
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
            'read'                    => true,
            'submit_tradiehub_quote'  => true,
            'view_tradiehub_community'=> true,
        ]);

        add_role('tradiehub_homeowner', 'Homeowner', [
            'read'                    => true,
            'publish_tradiehub_jobs'  => true,
        ]);

        $admin = get_role('administrator');
        if ($admin) {
            foreach (['submit_tradiehub_quote', 'publish_tradiehub_jobs', 'manage_tradiehub_escrow', 'view_tradiehub_community'] as $cap) {
                $admin->add_cap($cap);
            }
        }
    }
}
```

- [ ] **Step 3: Verify PHP syntax**

```bash
php -l wp-content/plugins/tradiehub-core/tradiehub-core.php
php -l wp-content/plugins/tradiehub-core/includes/class-activator.php
```
Expected: `No syntax errors detected` for both files.

---

### Task 5: Contractor Profile module (Phase 3, part 2)

**Files:**
- Create: `wp-content/plugins/tradiehub-core/includes/class-contractor-profile.php`

- [ ] **Step 1: Create class-contractor-profile.php**

```php
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
            'cslb_license_number'    => (string) get_user_meta($user_id, 'cslb_license_number', true),
            'years_in_business'      => (int)    get_user_meta($user_id, 'years_in_business', true),
            'has_liability_insurance'=> (bool)   get_user_meta($user_id, 'has_liability_insurance', true),
            'has_workers_comp'       => (bool)   get_user_meta($user_id, 'has_workers_comp', true),
            'service_zip_codes'      => (array)  (get_user_meta($user_id, 'service_zip_codes', true) ?: []),
            'cslb_license_valid'     => (bool)   get_user_meta($user_id, 'cslb_license_valid', true),
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
```

- [ ] **Step 2: Verify PHP syntax**

```bash
php -l wp-content/plugins/tradiehub-core/includes/class-contractor-profile.php
```
Expected: `No syntax errors detected`

---

### Task 6: Quote Workflow module (Phase 3, part 3)

**Files:**
- Create: `wp-content/plugins/tradiehub-core/includes/class-quote-workflow.php`

- [ ] **Step 1: Create class-quote-workflow.php**

```php
<?php
if (!defined('ABSPATH')) exit;

class TradieHub_Quote_Workflow {
    public static function init(): void {
        add_action('init', [self::class, 'register_post_types']);
        add_action('rest_api_init', [self::class, 'register_rest_routes']);
        add_action('tradiehub_quote_submitted', [self::class, 'notify_homeowner_of_quote'], 10, 2);
    }

    public static function register_post_types(): void {
        register_post_type('tradiehub_job', [
            'labels' => [
                'name'          => __('Jobs', 'tradiehub'),
                'singular_name' => __('Job', 'tradiehub'),
                'add_new_item'  => __('Post a Job', 'tradiehub'),
            ],
            'public'        => true,
            'has_archive'   => 'jobs',
            'rewrite'       => ['slug' => 'jobs', 'with_front' => false],
            'supports'      => ['title', 'editor', 'author', 'custom-fields'],
            'show_in_rest'  => true,
            'rest_base'     => 'jobs',
            'menu_icon'     => 'dashicons-hammer',
            'capability_type' => ['tradiehub_job', 'tradiehub_jobs'],
            'map_meta_cap'  => true,
        ]);

        register_post_type('tradiehub_quote', [
            'labels' => [
                'name'          => __('Quotes', 'tradiehub'),
                'singular_name' => __('Quote', 'tradiehub'),
            ],
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => 'edit.php?post_type=tradiehub_job',
            'supports'      => ['title', 'author', 'custom-fields'],
            'show_in_rest'  => true,
            'rest_base'     => 'quotes',
            'capability_type' => ['tradiehub_quote', 'tradiehub_quotes'],
            'map_meta_cap'  => true,
        ]);

        foreach (['pending', 'accepted', 'rejected', 'completed'] as $status) {
            register_post_status("quote_{$status}", [
                'label'                     => ucfirst($status),
                'public'                    => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'post_type'                 => ['tradiehub_quote'],
            ]);
        }
    }

    public static function register_rest_routes(): void {
        register_rest_route('tradiehub/v1', '/jobs', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'rest_create_job'],
            'permission_callback' => fn() => current_user_can('publish_tradiehub_jobs'),
            'args' => [
                'title'       => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'description' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'wp_kses_post'],
                'zip'         => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'budget_min'  => ['required' => false, 'type' => 'integer'],
                'budget_max'  => ['required' => false, 'type' => 'integer'],
                'specialty'   => ['required' => true, 'type' => 'string'],
                'deadline'    => ['required' => false, 'type' => 'string'],
            ],
        ]);

        register_rest_route('tradiehub/v1', '/quotes', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'rest_submit_quote'],
            'permission_callback' => fn() => current_user_can('submit_tradiehub_quote'),
            'args' => [
                'job_id'  => ['required' => true, 'type' => 'integer'],
                'amount'  => ['required' => true, 'type' => 'number'],
                'timeline'=> ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'message' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'wp_kses_post'],
            ],
        ]);

        register_rest_route('tradiehub/v1', '/quotes/(?P<id>\d+)/accept', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'rest_accept_quote'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args' => ['id' => ['required' => true, 'type' => 'integer']],
        ]);

        register_rest_route('tradiehub/v1', '/jobs/(?P<id>\d+)/complete', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'rest_complete_job'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args' => ['id' => ['required' => true, 'type' => 'integer']],
        ]);
    }

    public static function rest_create_job(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $job_id = wp_insert_post([
            'post_type'    => 'tradiehub_job',
            'post_title'   => $request->get_param('title'),
            'post_content' => $request->get_param('description'),
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id(),
            'meta_input'   => [
                'zip'        => sanitize_text_field($request->get_param('zip')),
                'budget_min' => absint($request->get_param('budget_min') ?? 0),
                'budget_max' => absint($request->get_param('budget_max') ?? 0),
                'deadline'   => sanitize_text_field($request->get_param('deadline') ?? ''),
                'job_status' => 'open',
            ],
        ], true);

        if (is_wp_error($job_id)) {
            return new WP_Error('create_failed', $job_id->get_error_message(), ['status' => 500]);
        }

        if ($specialty = $request->get_param('specialty')) {
            wp_set_post_terms($job_id, [sanitize_text_field($specialty)], 'tradiehub_specialty');
        }

        return new WP_REST_Response(['job_id' => $job_id, 'url' => get_permalink($job_id)], 201);
    }

    public static function rest_submit_quote(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $job_id = $request->get_param('job_id');
        $job = get_post($job_id);

        if (!$job || $job->post_type !== 'tradiehub_job') {
            return new WP_Error('invalid_job', 'Job not found.', ['status' => 404]);
        }

        $contractor_id = get_current_user_id();

        // Enforce one quote per contractor per job
        $existing = get_posts([
            'post_type'   => 'tradiehub_quote',
            'author'      => $contractor_id,
            'meta_query'  => [['key' => 'job_id', 'value' => $job_id]],
            'post_status' => 'any',
            'numberposts' => 1,
            'fields'      => 'ids',
        ]);
        if ($existing) {
            return new WP_Error('duplicate_quote', 'You have already submitted a quote for this job.', ['status' => 409]);
        }

        $quote_id = wp_insert_post([
            'post_type'   => 'tradiehub_quote',
            'post_title'  => sprintf('Quote for job #%d', $job_id),
            'post_status' => 'quote_pending',
            'post_author' => $contractor_id,
            'meta_input'  => [
                'job_id'   => $job_id,
                'amount'   => floatval($request->get_param('amount')),
                'timeline' => sanitize_text_field($request->get_param('timeline') ?? ''),
                'message'  => wp_kses_post($request->get_param('message') ?? ''),
            ],
        ], true);

        if (is_wp_error($quote_id)) {
            return new WP_Error('create_failed', $quote_id->get_error_message(), ['status' => 500]);
        }

        do_action('tradiehub_quote_submitted', $quote_id, $job_id);

        return new WP_REST_Response(['quote_id' => $quote_id], 201);
    }

    public static function rest_accept_quote(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $quote_id = (int) $request->get_param('id');
        $quote = get_post($quote_id);

        if (!$quote || $quote->post_type !== 'tradiehub_quote') {
            return new WP_Error('invalid_quote', 'Quote not found.', ['status' => 404]);
        }

        $job = get_post((int) get_post_meta($quote_id, 'job_id', true));
        if (!$job || (int) $job->post_author !== get_current_user_id()) {
            return new WP_Error('forbidden', 'You can only accept quotes on your own jobs.', ['status' => 403]);
        }

        wp_update_post(['ID' => $quote_id, 'post_status' => 'quote_accepted']);
        do_action('tradiehub_quote_accepted', $quote_id, $job->ID);

        return new WP_REST_Response(['status' => 'accepted', 'quote_id' => $quote_id], 200);
    }

    public static function rest_complete_job(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $job_id = (int) $request->get_param('id');
        $job    = get_post($job_id);

        if (!$job || $job->post_type !== 'tradiehub_job') {
            return new WP_Error('invalid_job', 'Job not found.', ['status' => 404]);
        }

        $user_id     = get_current_user_id();
        $is_homeowner = (int) $job->post_author === $user_id;
        $meta_key     = $is_homeowner ? 'homeowner_confirmed_complete' : 'contractor_confirmed_complete';

        update_post_meta($job_id, $meta_key, true);

        $both = get_post_meta($job_id, 'homeowner_confirmed_complete', true) &&
                get_post_meta($job_id, 'contractor_confirmed_complete', true);

        if ($both) {
            update_post_meta($job_id, 'job_status', 'completed');
            do_action('tradiehub_job_completed', $job_id);
        }

        return new WP_REST_Response(['confirmed' => true, 'both_confirmed' => $both], 200);
    }

    public static function notify_homeowner_of_quote(int $quote_id, int $job_id): void {
        $job        = get_post($job_id);
        $homeowner  = get_userdata((int) $job->post_author);
        $contractor = get_userdata((int) get_post_field('post_author', $quote_id));
        $amount     = (float) get_post_meta($quote_id, 'amount', true);

        if (!$homeowner || !$contractor) return;

        wp_mail(
            $homeowner->user_email,
            sprintf(__('New quote received for "%s"', 'tradiehub'), $job->post_title),
            sprintf(
                __("Hi %s,\n\n%s submitted a quote of \$%s for your job: %s\n\nView quotes: %s", 'tradiehub'),
                $homeowner->display_name,
                $contractor->display_name,
                number_format($amount, 2),
                $job->post_title,
                home_url('/my-jobs/')
            )
        );
    }
}
```

- [ ] **Step 2: Verify PHP syntax**

```bash
php -l wp-content/plugins/tradiehub-core/includes/class-quote-workflow.php
```
Expected: `No syntax errors detected`

---

### Task 7: Escrow Wallet module (Phase 3, part 4)

**Files:**
- Create: `wp-content/plugins/tradiehub-core/includes/class-escrow-wallet.php`

- [ ] **Step 1: Create class-escrow-wallet.php**

```php
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
     * Helper: returns total currently-held escrow amount for a user.
     * Used to display "locked" balance on wallet page.
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
```

- [ ] **Step 2: Verify PHP syntax**

```bash
php -l wp-content/plugins/tradiehub-core/includes/class-escrow-wallet.php
```
Expected: `No syntax errors detected`

---

### Task 8: Local SEO module (Phase 3, part 5)

**Files:**
- Create: `wp-content/plugins/tradiehub-core/includes/class-local-seo.php`

- [ ] **Step 1: Create class-local-seo.php**

```php
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
                '@type'         => 'PostalAddress',
                'postalCode'    => $z,
                'addressRegion' => 'CA',
                'addressCountry'=> 'US',
            ], $zips),
        ];

        if ($meta['cslb_license_number']) {
            $schema['hasCredential'] = [
                '@type'               => 'EducationalOccupationalCredential',
                'credentialCategory'  => 'CSLB License',
                'name'                => $meta['cslb_license_number'],
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
                    WP_CLI::warning("Failed: /{$slug}/ — " . $page_id->get_error_message());
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
```

- [ ] **Step 2: Verify PHP syntax for all plugin files**

```bash
php -l wp-content/plugins/tradiehub-core/includes/class-local-seo.php
php -l wp-content/plugins/tradiehub-core/tradiehub-core.php
php -l wp-content/plugins/tradiehub-core/includes/class-activator.php
php -l wp-content/plugins/tradiehub-core/includes/class-contractor-profile.php
php -l wp-content/plugins/tradiehub-core/includes/class-quote-workflow.php
php -l wp-content/plugins/tradiehub-core/includes/class-escrow-wallet.php
```
Expected: `No syntax errors detected` for all 6 files.

- [ ] **Step 3: Commit plugin**

```bash
git add wp-content/plugins/tradiehub-core/
git commit -m "feat: add tradiehub-core plugin with all four modules (Phase 3)"
git tag phase-3-complete
```

---

## Subsystem 3: WP-CLI Scripts

### Task 9: WordPress baseline setup script (Phase 1)

**Files:**
- Create: `scripts/setup.sh`

- [ ] **Step 1: Create scripts/setup.sh**

```bash
#!/usr/bin/env bash
# TradieHub WordPress baseline setup.
# Run from the LocalWP app/public directory after creating the site in LocalWP.
# Usage: bash /path/to/repo/scripts/setup.sh

set -euo pipefail

echo "==> Updating WordPress core..."
wp core update

echo "==> Configuring site identity..."
wp option update blogname "TradieHub"
wp option update blogdescription "Find licensed contractors in California"
wp option update timezone_string "America/Los_Angeles"
wp option update WPLANG "en_US"

echo "==> Setting permalink structure..."
wp rewrite structure '/%postname%/' --hard

echo "==> Hiding site from search engines during dev..."
wp option update blog_public 0

echo "==> Disabling comments globally..."
wp option update default_comment_status closed
wp option update default_ping_status closed

echo "==> Configuring WooCommerce..."
wp option update woocommerce_currency "USD"
wp option update woocommerce_default_country "US:CA"
wp option update woocommerce_store_address "123 Market St"
wp option update woocommerce_store_city "San Francisco"
wp option update woocommerce_store_postcode "94103"
wp option update woocommerce_enable_guest_checkout "no"
wp option update woocommerce_show_marketplace_suggestions "no"
wp option update woocommerce_allow_tracking "no"

echo "==> Installing and activating free plugins..."
wp plugin install seo-by-rank-math --activate
wp plugin install woocommerce --activate
wp plugin install elementor --activate
wp plugin install woo-wallet --activate
wp plugin install fluent-community --activate
wp plugin install query-monitor --activate

echo ""
echo "==> Done! Manual steps remaining:"
echo "    1. Install Felan parent theme from ThemeForest ZIP"
echo "    2. Install B2BKing from CodeCanyon ZIP"
echo "    3. Install FluentCommunity Pro ZIP"
echo "    4. Visit wp-admin, accept Felan's TGMPA required plugin installs"
echo "    5. Run: bash scripts/seed-demo-data.sh"
```

- [ ] **Step 2: Make executable and commit**

```bash
chmod +x scripts/setup.sh
git add scripts/setup.sh
git commit -m "feat: add WP-CLI baseline setup script (Phase 1)"
git tag phase-1-complete
```

---

### Task 10: Seed demo data script (Phase 8)

**Files:**
- Create: `scripts/seed-demo-data.sh`

- [ ] **Step 1: Create scripts/seed-demo-data.sh**

```bash
#!/usr/bin/env bash
# Seed TradieHub with realistic demo data.
# Run AFTER setup.sh, after tradiehub-core is activated.
# Usage: bash /path/to/repo/scripts/seed-demo-data.sh

set -euo pipefail

echo "==> Creating contractor accounts..."

CONTRACTORS=(
  "marcus.plumbing@example.com|Marcus Rivera|plumbing|90001|100001"
  "sarah.electric@example.com|Sarah Chen|electrical|94105|200002"
  "tom.hvac@example.com|Tom Nguyen|hvac|92101|300003"
  "diana.roofing@example.com|Diana Patel|roofing|95814|400004"
  "carlos.landscape@example.com|Carlos Mendez|landscaping|90291|500005"
  "jen.general@example.com|Jennifer Walsh|general-contracting|94016|600006"
  "bob.painting@example.com|Bob Kowalski|painting|90802|700007"
  "aisha.flooring@example.com|Aisha Johnson|flooring|95822|800008"
  "ryan.remodel@example.com|Ryan Park|remodeling|90210|900009"
  "linda.pest@example.com|Linda Torres|pest-control|92103|100010"
  "mike.plumb2@example.com|Mike Sanchez|plumbing|94103|100011"
  "anna.elec2@example.com|Anna Johansson|electrical|90012|200012"
  "pete.hvac2@example.com|Pete Goldberg|hvac|90210|300013"
  "grace.roof2@example.com|Grace Kim|roofing|94117|400014"
  "omar.general2@example.com|Omar Hassan|general-contracting|92103|500015"
)

declare -A CONTRACTOR_IDS
for entry in "${CONTRACTORS[@]}"; do
  IFS='|' read -r email name specialty zip license <<< "$entry"
  id=$(wp user create "$email" "$email" \
    --role=tradiehub_contractor \
    --display_name="$name" \
    --user_pass="DemoPass123!" \
    --porcelain 2>/dev/null || wp user get "$email" --field=ID 2>/dev/null)
  wp user meta update "$id" cslb_license_number "$license"
  wp user meta update "$id" cslb_license_valid 1
  wp user meta update "$id" service_specialties "$specialty"
  wp user meta update "$id" has_liability_insurance 1
  wp user meta update "$id" years_in_business "$((RANDOM % 15 + 2))"
  wp user meta update "$id" service_zip_codes "$zip"
  CONTRACTOR_IDS["$email"]="$id"
  echo "  Created contractor: $name (ID $id)"
done

echo "==> Creating homeowner accounts..."

HOMEOWNERS=(
  "jessica.h@example.com|Jessica Thompson"
  "david.h@example.com|David Lee"
  "mary.h@example.com|Mary O'Brien"
  "james.h@example.com|James Williams"
  "patricia.h@example.com|Patricia Martinez"
  "robert.h@example.com|Robert Brown"
  "linda.h@example.com|Linda Davis"
  "michael.h@example.com|Michael Wilson"
)

declare -A HOMEOWNER_IDS
for entry in "${HOMEOWNERS[@]}"; do
  IFS='|' read -r email name <<< "$entry"
  id=$(wp user create "$email" "$email" \
    --role=tradiehub_homeowner \
    --display_name="$name" \
    --user_pass="DemoPass123!" \
    --porcelain 2>/dev/null || wp user get "$email" --field=ID 2>/dev/null)
  HOMEOWNER_IDS["$email"]="$id"
  echo "  Created homeowner: $name (ID $id)"
done

echo "==> Creating job posts..."

J1=$(wp post create \
  --post_type=tradiehub_job \
  --post_title="EV charger 240V outlet installation in Pasadena" \
  --post_content="Need a licensed electrician in Pasadena to install a 240V outlet for my EV charger. Home is a 1960s build, panel has space but may need upgrade. Looking for quotes this week." \
  --post_status=publish \
  --post_author="${HOMEOWNER_IDS['jessica.h@example.com']}" \
  --meta_input='{"zip":"91101","budget_min":"400","budget_max":"800","job_status":"open"}' \
  --porcelain)
wp post term add "$J1" tradiehub_specialty electrical

J2=$(wp post create \
  --post_type=tradiehub_job \
  --post_title="Bathroom remodel in Oakland — tub to walk-in shower" \
  --post_content="Bathroom remodel in Oakland, 2-bed apartment. Replacing tub with walk-in shower, new tile, new vanity. Have the materials already. Need licensed plumber plus general contractor." \
  --post_status=publish \
  --post_author="${HOMEOWNER_IDS['david.h@example.com']}" \
  --meta_input='{"zip":"94612","budget_min":"3000","budget_max":"5000","job_status":"open"}' \
  --porcelain)
wp post term add "$J2" tradiehub_specialty plumbing

J3=$(wp post create \
  --post_type=tradiehub_job \
  --post_title="HVAC replacement in San Diego condo" \
  --post_content="My 15-year-old HVAC system is failing in my San Diego condo. Looking for a licensed HVAC contractor to assess and replace both the air handler and condenser. 1,200 sq ft unit." \
  --post_status=publish \
  --post_author="${HOMEOWNER_IDS['mary.h@example.com']}" \
  --meta_input='{"zip":"92103","budget_min":"4000","budget_max":"8000","job_status":"open"}' \
  --porcelain)
wp post term add "$J3" tradiehub_specialty hvac

J4=$(wp post create \
  --post_type=tradiehub_job \
  --post_title="Roof inspection and repair after storm damage in Sacramento" \
  --post_content="We had a bad windstorm last month and now have a small leak near the chimney. Need a licensed roofer to inspect and repair. About 2,100 sq ft composition shingle roof, 12 years old." \
  --post_status=publish \
  --post_author="${HOMEOWNER_IDS['james.h@example.com']}" \
  --meta_input='{"zip":"95814","budget_min":"500","budget_max":"2500","job_status":"open"}' \
  --porcelain)
wp post term add "$J4" tradiehub_specialty roofing

J5=$(wp post create \
  --post_type=tradiehub_job \
  --post_title="Backyard landscaping redesign in Los Angeles" \
  --post_content="Looking to redesign a neglected 800 sq ft backyard in Silver Lake, Los Angeles. Want drought-tolerant native plants, decomposed granite paths, and a small deck. License and portfolio required." \
  --post_status=publish \
  --post_author="${HOMEOWNER_IDS['patricia.h@example.com']}" \
  --meta_input='{"zip":"90039","budget_min":"5000","budget_max":"12000","job_status":"open"}' \
  --porcelain)
wp post term add "$J5" tradiehub_specialty landscaping

J6=$(wp post create \
  --post_type=tradiehub_job \
  --post_title="Kitchen remodel in San Jose bungalow" \
  --post_content="Full kitchen remodel in a 1940s San Jose bungalow. Gut and replace cabinets, countertops (quartz), flooring (tile), and appliances. Existing layout stays. Need licensed general contractor." \
  --post_status=publish \
  --post_author="${HOMEOWNER_IDS['robert.h@example.com']}" \
  --meta_input='{"zip":"95112","budget_min":"25000","budget_max":"45000","job_status":"open"}' \
  --porcelain)
wp post term add "$J6" tradiehub_specialty remodeling

J7=$(wp post create \
  --post_type=tradiehub_job \
  --post_title="Interior painting — 3BR house in Fresno" \
  --post_content="Need interior painting for a 3-bedroom, 2-bath house in Fresno. About 1,600 sq ft of paintable walls. Walls only, no ceilings. I will supply paint. Looking for a clean, licensed painter." \
  --post_status=publish \
  --post_author="${HOMEOWNER_IDS['linda.h@example.com']}" \
  --meta_input='{"zip":"93721","budget_min":"1200","budget_max":"2500","job_status":"open"}' \
  --porcelain)
wp post term add "$J7" tradiehub_specialty painting

J8=$(wp post create \
  --post_type=tradiehub_job \
  --post_title="Hardwood floor installation in Long Beach condo" \
  --post_content="Installing engineered hardwood in a 900 sq ft Long Beach condo (living room + 2 bedrooms). Removing existing carpet. Floating installation preferred. Supply and install quote welcome." \
  --post_status=publish \
  --post_author="${HOMEOWNER_IDS['michael.h@example.com']}" \
  --meta_input='{"zip":"90802","budget_min":"3500","budget_max":"6000","job_status":"open"}' \
  --porcelain)
wp post term add "$J8" tradiehub_specialty flooring

echo "  Created 8 job posts."

echo "==> Creating quotes..."

CONTRACTOR_SARAH="${CONTRACTOR_IDS['sarah.electric@example.com']}"
CONTRACTOR_ANNA="${CONTRACTOR_IDS['anna.elec2@example.com']}"
CONTRACTOR_TOM="${CONTRACTOR_IDS['tom.hvac@example.com']}"
CONTRACTOR_PETE="${CONTRACTOR_IDS['pete.hvac2@example.com']}"

wp post create \
  --post_type=tradiehub_quote \
  --post_title="Quote for job #${J1}" \
  --post_status=quote_pending \
  --post_author="$CONTRACTOR_SARAH" \
  --meta_input="{\"job_id\":\"${J1}\",\"amount\":\"650\",\"timeline\":\"3-4 days\",\"message\":\"I can have this done by end of week. I have done many EV charger installs in Pasadena and carry full liability insurance.\"}"

wp post create \
  --post_type=tradiehub_quote \
  --post_title="Quote for job #${J1}" \
  --post_status=quote_pending \
  --post_author="$CONTRACTOR_ANNA" \
  --meta_input="{\"job_id\":\"${J1}\",\"amount\":\"720\",\"timeline\":\"5 days\",\"message\":\"I specialize in panel upgrades and EV charger installs. Happy to do a free assessment first to confirm whether a panel upgrade is needed.\"}"

wp post create \
  --post_type=tradiehub_quote \
  --post_title="Quote for job #${J3}" \
  --post_status=quote_pending \
  --post_author="$CONTRACTOR_TOM" \
  --meta_input="{\"job_id\":\"${J3}\",\"amount\":\"5200\",\"timeline\":\"10 days including equipment lead time\",\"message\":\"I have replaced many Carrier and Trane units in San Diego condos. Can source a Daikin mini-split or standard split system per your preference.\"}"

wp post create \
  --post_type=tradiehub_quote \
  --post_title="Quote for job #${J3}" \
  --post_status=quote_pending \
  --post_author="$CONTRACTOR_PETE" \
  --meta_input="{\"job_id\":\"${J3}\",\"amount\":\"4800\",\"timeline\":\"7-8 days\",\"message\":\"Best price in San Diego for HVAC replacement. All work is permitted and inspected. I stand behind my installs with a 2-year labor warranty.\"}"

echo "  Created 4 quotes."

echo "==> Flushing rewrite rules..."
wp rewrite flush --hard

echo ""
echo "==> Seed complete!"
echo "    Contractors: ${#CONTRACTOR_IDS[@]}"
echo "    Homeowners: ${#HOMEOWNER_IDS[@]}"
echo "    Jobs: 8"
echo "    Quotes: 4"
```

- [ ] **Step 2: Create scripts/export-db.sh**

```bash
#!/usr/bin/env bash
# Export the TradieHub database for versioning or sharing.
# Usage: bash scripts/export-db.sh

set -euo pipefail

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
OUTPUT="db-exports/tradiehub_${TIMESTAMP}.sql"
mkdir -p db-exports

echo "==> Exporting database to ${OUTPUT}..."
wp db export "$OUTPUT" --porcelain

echo "==> Done. File size: $(du -h "$OUTPUT" | cut -f1)"
echo "    Note: never commit db-exports/ to git (contains real data)."
```

- [ ] **Step 3: Make scripts executable and commit**

```bash
chmod +x scripts/seed-demo-data.sh scripts/export-db.sh
echo "db-exports/" >> .gitignore
git add scripts/seed-demo-data.sh scripts/export-db.sh .gitignore
git commit -m "feat: add seed data and DB export scripts (Phase 8)"
git tag phase-8-complete
```

---

## Subsystem 4: Documentation

### Task 11: Architecture Decision Records (ADRs)

**Files:**
- Create: `docs/decisions/001-why-terawallet-over-wpswings.md`
- Create: `docs/decisions/002-why-drop-better-messages.md`
- Create: `docs/decisions/003-why-felan-theme.md`

- [ ] **Step 1: Create ADR 001**

```markdown
# ADR 001: Why TeraWallet over WPSwings Wallet Pro

**Status:** Accepted
**Date:** 2026-04-20

## Context

TradieHub needs an escrow-style wallet system where homeowners can deposit funds that are held until a job is completed. The original stack plan referenced WPSwings Wallet Pro.

## Decision

Use TeraWallet (plugin slug: `woo-wallet`) instead of WPSwings Wallet Pro.

## Reasons

1. **Cost.** TeraWallet is free and actively maintained on the WordPress.org plugin directory. WPSwings Wallet Pro is a paid plugin (~$49/year). For a portfolio build this matters.
2. **Feature parity.** Both plugins expose `credit()` and `debit()` primitives that are sufficient for our escrow bridge code. TeraWallet's WooCommerce integration is equally mature.
3. **API surface.** TeraWallet exposes `woo_wallet()->wallet->credit($user_id, $amount, $description)` and `->debit()`. These are the only two calls our `class-escrow-wallet.php` needs.
4. **Community support.** TeraWallet has 20,000+ active installs and responds to support threads. WPSwings support quality for free-tier issues is inconsistent.

## Tradeoffs

WPSwings Pro has a more polished admin UI and some premium features (auto top-up, wallet sharing) that TeraWallet lacks. These features are not required for TradieHub's escrow use case.

## Consequence

The `tradiehub-core` plugin's escrow module is coupled to TeraWallet's API. If a future version changes `woo_wallet()`, the bridge code in `class-escrow-wallet.php` will need updating. This is documented with a version comment in that file.
```

- [ ] **Step 2: Create ADR 002**

```markdown
# ADR 002: Why We Dropped Better Messages Pro

**Status:** Accepted
**Date:** 2026-04-20

## Context

The original TradieHub stack plan included Better Messages Pro for real-time direct messaging between homeowners and contractors.

## Decision

Remove Better Messages Pro from the stack entirely. FluentCommunity 2.x handles this natively.

## Reasons

1. **Redundancy.** FluentCommunity 2.x (released stable in 2026) ships built-in real-time messaging including direct messages between users. Running Better Messages Pro alongside it creates two separate messaging experiences with no shared inbox.
2. **UX fragmentation.** Users should not have to check two places for messages. FluentCommunity's messaging lives inside the community hub that contractors already use daily.
3. **Cost.** Better Messages Pro is a paid plugin. Eliminating it reduces licensing cost without reducing functionality.
4. **Maintenance overhead.** Two messaging systems means two sets of database tables, two notification pathways, and two sets of plugin updates to monitor.

## Consequence

All direct messaging between homeowners and contractors routes through FluentCommunity's messaging module. The community hub becomes the single communication layer. This is documented in the README stack table.
```

- [ ] **Step 3: Create ADR 003**

```markdown
# ADR 003: Why Felan Theme

**Status:** Accepted
**Date:** 2026-04-20

## Context

TradieHub needed a WordPress theme that could handle a freelance/contractor marketplace with job listings, contractor profiles, and quote workflows without building all UI from scratch.

## Decision

Use Felan (ThemeForest, ~$59 one-time) as the parent theme, with all customization in a `felan-child` child theme.

## Reasons

1. **Purpose-built.** Felan is designed specifically for freelance marketplaces and job boards. Its default templates cover contractor listings, job posting, and profile pages — the exact pages TradieHub needs.
2. **Elementor integration.** Felan ships with Elementor-compatible widgets and demo content importable via a one-click importer. This dramatically reduces custom front-end work.
3. **WooCommerce compatibility.** Felan is WooCommerce-aware out of the box, which matters since WooCommerce powers our wallet and quote workflows.
4. **Portfolio optics.** A recognizable, polished theme lets the portfolio screenshots focus on TradieHub's unique features (escrow, community) rather than raw CSS work.

## Tradeoffs

- Paid theme (~$59) that cannot be committed to the public repo. The README documents this requirement and explains how to recreate the stack.
- The parent theme's TGMPA plugin install step requires a manual wp-admin visit, which cannot be scripted.
- Felan's built-in package/listing system overlaps with our B2BKing quote workflow. We disable the Felan package listing via a child theme filter to avoid confusion.

## Consequence

The `felan-child` theme handles all visual customization. No Felan parent theme files are edited. This is a hard rule documented in `.claude/CLAUDE.md`.
```

- [ ] **Step 4: Commit ADRs**

```bash
git add docs/decisions/
git commit -m "docs: add architecture decision records for wallet, messaging, and theme choices"
```

---

### Task 12: Architecture and SEO strategy docs

**Files:**
- Create: `docs/architecture.md`
- Create: `docs/seo-strategy.md`

- [ ] **Step 1: Create docs/architecture.md**

```markdown
# TradieHub Architecture

## System Overview

TradieHub is a WordPress-based contractor directory and marketplace for California. The system is built on a layered architecture:

```
Browser
  |
WordPress (Felan child theme + Elementor page builder)
  |
tradiehub-core plugin (custom integration layer)
  |
Plugin tier:
  - WooCommerce (order backbone)
  - TeraWallet (wallet and escrow primitives)
  - B2BKing (quote workflow UI)
  - FluentCommunity Pro (contractor community + messaging)
  - Rank Math SEO (schema, sitemap)
  |
Database: MariaDB
  - WordPress standard tables
  - wp_tradiehub_escrow (custom escrow state table)
```

## Key Components

### tradiehub-core Plugin Modules

| Module | File | Responsibility |
|--------|------|----------------|
| Activator | `class-activator.php` | Creates custom DB table, registers roles and capabilities on plugin activation |
| Contractor Profile | `class-contractor-profile.php` | User meta fields (CSLB license, service areas, insurance), taxonomy, shortcode |
| Quote Workflow | `class-quote-workflow.php` | `tradiehub_job` and `tradiehub_quote` CPTs, REST API endpoints, email notifications |
| Escrow Wallet | `class-escrow-wallet.php` | TeraWallet bridge, escrow state machine (held, released, disputed), admin dashboard |
| Local SEO | `class-local-seo.php` | Schema.org JSON-LD output, rewrite rules for city x trade pages, WP-CLI generator |

### Custom DB Table: wp_tradiehub_escrow

| Column | Type | Purpose |
|--------|------|---------|
| id | bigint PK | Auto-increment row ID |
| job_id | bigint FK | Links to `tradiehub_job` post |
| quote_id | bigint FK | Links to `tradiehub_quote` post |
| homeowner_id | bigint FK | Links to wp_users |
| contractor_id | bigint FK | Links to wp_users |
| amount | decimal(10,2) | Amount in USD |
| status | varchar(20) | held, released, or disputed |
| created_at | datetime | When escrow was created |
| released_at | datetime | When funds were released (null if held/disputed) |

### REST API Endpoints (tradiehub/v1)

| Method | Path | Auth | Purpose |
|--------|------|------|---------|
| POST | /jobs | Homeowner | Create a job post |
| POST | /quotes | Contractor | Submit a quote on a job |
| POST | /quotes/{id}/accept | Homeowner (job owner) | Accept a quote, triggers escrow hold |
| POST | /jobs/{id}/complete | Homeowner or Contractor | Mark a job complete, both must confirm to release escrow |

## Data Flow: Quote to Escrow Release

```
Homeowner posts job (tradiehub_job, status=open)
  |
Contractors submit quotes (tradiehub_quote, status=quote_pending)
  |
Homeowner accepts quote via POST /quotes/{id}/accept
  |
tradiehub_quote_accepted action fires
  |
TradieHub_Escrow_Wallet::hold_escrow()
  - Checks homeowner wallet balance
  - Debits homeowner via woo_wallet()->wallet->debit()
  - Inserts wp_tradiehub_escrow row (status=held)
  |
Work happens
  |
Both parties POST /jobs/{id}/complete
  |
tradiehub_job_completed action fires
  |
TradieHub_Escrow_Wallet::release_escrow()
  - Credits contractor via woo_wallet()->wallet->credit()
  - Updates wp_tradiehub_escrow row (status=released)
```

## SEO Architecture

100 programmatic landing pages are generated via `wp tradiehub generate-seo-pages`.

Each page maps to a URL like `/electricians-in-los-angeles/` handled by custom rewrite rules. The `TradieHub_Local_SEO::handle_seo_page_template()` intercepts matched requests and renders the page directly with JSON-LD `ItemList` schema, contractor listings, and FAQ content.

Page count: 10 cities x 10 trades = 100 pages.

This is the same pattern used by Yelp, Zillow, and Angi to build location-based SEO coverage at scale.
```

- [ ] **Step 2: Create docs/seo-strategy.md**

```markdown
# TradieHub SEO Strategy

## Goal

Rank for high-intent local queries like "licensed plumber in Los Angeles" and "electrician near me California" — the exact searches homeowners make when they need a contractor.

## Tactic 1: Programmatic Landing Pages

10 cities x 10 trades = 100 pages targeting queries like:
- "plumbers in los angeles"
- "electricians in san francisco"
- "hvac contractors in san diego"

Each page has:
- Unique H1 ("Top Plumbers in Los Angeles, CA")
- Intro paragraph (3 variants rotated per city+trade combo to avoid exact-duplicate content)
- Live contractor listings from the database
- 5-question FAQ (markup stays unique because answers reference the city+trade combo)
- JSON-LD `ItemList` schema with each listed contractor as a `LocalBusiness`
- BreadcrumbList schema

This mirrors the SEO architecture of Yelp (/category/plumbers/los-angeles), Angi, and HomeAdvisor — all of which built their domain authority on programmatic local pages.

## Tactic 2: Contractor Profile Schema

Each contractor's author archive page outputs `LocalBusiness` JSON-LD including:
- Business name (contractor display name)
- Service area (zip codes from their profile)
- CSLB credential (license number if verified)

This helps individual contractor pages rank for "[name] contractor" and "[trade] in [zip]" queries.

## Tactic 3: Rank Math SEO Configuration

- Business type: LocalBusiness
- Sitemap: auto-generated (includes all 100 landing pages + contractor profiles + static pages)
- Schema modules enabled: LocalBusiness, Person, Article

## Verification Commands

After setup, verify schema output:

```bash
curl -s http://tradiehub.local/plumbers-in-los-angeles/ | grep -A 30 'application/ld+json'
```

Check sitemap coverage:

```bash
curl http://tradiehub.local/sitemap_index.xml
```

Expected: sitemap entries for all 100 programmatic pages plus contractor profiles.

## Comparison to Competitors

| Site | SEO approach | TradieHub difference |
|------|-------------|----------------------|
| Angi | Paid lead gen, pays for PPC | Organic-first, no per-lead fees for contractors |
| Thumbtack | Category + city landing pages | Same pattern, but adds escrow protection angle to copy |
| HomeAdvisor | License badge, review volume | TradieHub adds live CSLB license number on profiles (verifiable) |

## When to Flip blog_public

The site is hidden from search engines during development (`blog_public = 0`). Before launching, run:

```bash
wp option update blog_public 1
```

Then submit the sitemap to Google Search Console.
```

- [ ] **Step 3: Commit docs**

```bash
git add docs/architecture.md docs/seo-strategy.md
git commit -m "docs: add architecture overview and SEO strategy"
```

---

### Task 13: README (Phase 9)

**Files:**
- Create: `README.md`

- [ ] **Step 1: Create README.md**

```markdown
# TradieHub

> A licensed-contractor directory and marketplace for California, with escrow-protected deposits and a contractor-only community.

![TradieHub homepage](docs/screenshots/01-homepage.png)

## The Problem

Hiring a contractor in California is stressful. Homeowners do not know if the contractor is licensed, worry about upfront deposits disappearing, and have no way to see honest community reviews (other contractors know who is reliable better than random customers do).

Most trades directories (Angi, Thumbtack, HomeAdvisor) just pass leads and charge contractors per-lead fees. There is no financial protection for the homeowner, and no community for contractors.

## The Solution

TradieHub is a directory, marketplace, and community where:

1. Contractors create verified profiles with their CSLB license number, service areas, specialties, and portfolio photos.
2. Homeowners post jobs with a budget and location. Contractors submit quotes.
3. When a homeowner accepts a quote, they pay a deposit into TradieHub's wallet — held in escrow. Funds are released to the contractor when both parties confirm the job is complete.
4. Contractors have a private community to discuss jobs, tools, code compliance, and referrals. This is the retention hook — contractors return for the community even when they do not have active jobs.

## Features

- Licensed contractor directory (10 California cities, 10 trades, 100 SEO landing pages)
- Job posting and quote request workflow
- Escrow-style wallet: funds held until job completion
- Contractor-only community with trade-specific spaces (powered by FluentCommunity)
- Real-time messaging between homeowners and contractors
- Schema.org LocalBusiness markup on all contractor profiles
- Programmatic SEO landing pages (same pattern as Yelp and Angi)

## Screenshots

| | |
|---|---|
| ![Homepage](docs/screenshots/01-homepage.png) | ![Contractor directory](docs/screenshots/02-contractor-directory.png) |
| ![Job posting form](docs/screenshots/04-post-a-job.png) | ![Wallet with escrow](docs/screenshots/06-wallet-with-escrow.png) |
| ![Community feed](docs/screenshots/07-community-feed.png) | ![Admin escrow dashboard](docs/screenshots/12-admin-escrow-dashboard.png) |

## Stack

| Component | Tool | Version | Why |
|-----------|------|---------|-----|
| CMS | WordPress | 6.7+ | Ecosystem, plugin availability |
| Theme | Felan (child theme) | 1.1.7+ | Purpose-built for freelance/job-board marketplaces |
| Quote workflow | B2BKing | 5.5.30+ | B2B quoting, customer groups (Homeowner vs Contractor) |
| Wallet/Escrow | TeraWallet (woo-wallet) | latest | Free, WooCommerce-native, exposes credit/debit primitives |
| Community | FluentCommunity Pro | 2.x | Real-time messaging + community spaces, replaces Better Messages Pro |
| SEO | Rank Math SEO | latest | Local SEO, schema.org, sitemap |
| Page builder | Elementor (free) | latest | Required by Felan for page templates |

See [docs/decisions/](docs/decisions/) for detailed reasoning on each stack choice.

## Architecture

See [docs/architecture.md](docs/architecture.md) for the full system diagram and data flow.

The custom `tradiehub-core` plugin is the integration layer:
- [class-contractor-profile.php](wp-content/plugins/tradiehub-core/includes/class-contractor-profile.php) — CSLB license fields, service area meta, shortcode
- [class-quote-workflow.php](wp-content/plugins/tradiehub-core/includes/class-quote-workflow.php) — job and quote CPTs, REST API, email notifications
- [class-escrow-wallet.php](wp-content/plugins/tradiehub-core/includes/class-escrow-wallet.php) — escrow state machine, TeraWallet bridge, admin dashboard
- [class-local-seo.php](wp-content/plugins/tradiehub-core/includes/class-local-seo.php) — JSON-LD schema, rewrite rules, WP-CLI page generator

## Key Technical Decisions

- [Why TeraWallet over WPSwings Wallet Pro](docs/decisions/001-why-terawallet-over-wpswings.md)
- [Why we dropped Better Messages Pro](docs/decisions/002-why-drop-better-messages.md)
- [Why Felan theme](docs/decisions/003-why-felan-theme.md)

## Running Locally

1. Clone this repo
2. Set up a LocalWP site (PHP 8.3, nginx, MariaDB)
3. Symlink (or copy) `wp-content/themes/felan-child/` and `wp-content/plugins/tradiehub-core/` into your LocalWP install's `wp-content/` directory
4. Obtain paid plugins: Felan (ThemeForest ~$59), B2BKing (CodeCanyon ~$179/yr), FluentCommunity Pro (~$129/yr)
5. Run from the LocalWP `app/public` directory: `bash /path/to/repo/scripts/setup.sh`
6. Run: `bash /path/to/repo/scripts/seed-demo-data.sh`
7. MANUAL: Visit wp-admin, accept Felan's TGMPA required plugin installs, import Felan demo content, configure B2BKing customer groups, create FluentCommunity spaces
8. Run: `wp tradiehub generate-seo-pages`
9. Visit `http://tradiehub.local`

## What I Learned

*(Liam writes this section — voice-critical, personal reflection on what was hard, what worked, what would be done differently.)*

## What Is Next

- Live CSLB license verification API (scoped in class-contractor-profile.php with a TODO comment)
- Stripe integration for wallet top-ups (currently mocked)
- Mobile app via React Native sharing auth with the WordPress REST API

---

Built by [Liam](https://liam-portfolio-omega.vercel.app/)
```

- [ ] **Step 2: Commit README and final phase tags**

```bash
git add README.md
git commit -m "docs: add README portfolio document (Phase 9)"
git tag phase-9-complete
```

---

## Self-Review

### Spec Coverage Check

| Requirement | Task | Status |
|-------------|------|--------|
| .claude/CLAUDE.md with hard rules | Task 1 | Covered |
| .claude/agents/wordpress-developer.md | Task 1 | Covered |
| .claude/agents/seo-auditor.md | Task 1 | Covered |
| Skills: wp-cli-patterns, wordpress-php-conventions | Task 1 | Covered |
| .gitignore excluding WP core, third-party plugins | Task 2 | Covered |
| Felan child theme style.css + functions.php | Task 3 | Covered |
| tradiehub-core plugin skeleton + constants | Task 4 | Covered |
| class-activator.php (DB table, roles, caps) | Task 4 | Covered |
| class-contractor-profile.php (CSLB, user meta, shortcode) | Task 5 | Covered |
| class-quote-workflow.php (CPTs, REST API, notifications) | Task 6 | Covered |
| class-escrow-wallet.php (TeraWallet bridge, admin dashboard) | Task 7 | Covered |
| class-local-seo.php (JSON-LD, rewrite rules, WP-CLI command) | Task 8 | Covered |
| scripts/setup.sh (WP-CLI Phase 1 commands) | Task 9 | Covered |
| scripts/seed-demo-data.sh (realistic 15 contractors, 8 homeowners, 8 jobs, 4 quotes) | Task 10 | Covered |
| scripts/export-db.sh | Task 10 | Covered |
| 3 ADRs | Task 11 | Covered |
| docs/architecture.md | Task 12 | Covered |
| docs/seo-strategy.md | Task 12 | Covered |
| README.md portfolio document | Task 13 | Covered |

### Placeholder Scan

Searched for TBD, TODO, implement later: only one intentional TODO in `class-contractor-profile.php` for the live CSLB API call, which design.md explicitly calls out as a portfolio talking point. Not a gap.

### Type Consistency

- `TradieHub_Contractor_Profile::get_contractor_meta()` is referenced in `class-local-seo.php` (Task 8) and `class-escrow-wallet.php` (Task 7) — both defined in Task 5. Consistent.
- `woo_wallet()->wallet->credit()` / `->debit()` called in Task 7, TeraWallet primitives as documented in design.md. Consistent.
- `tradiehub_quote_accepted` action: fired in Task 6 (`rest_accept_quote`), hooked in Task 7 (`hold_escrow`). Argument signature `(int $quote_id, int $job_id)` matches both. Consistent.
- `tradiehub_job_completed` action: fired in Task 6 (`rest_complete_job`), hooked in Task 7 (`release_escrow`). Argument `int $job_id`. Consistent.
