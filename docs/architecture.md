# TradieHub Architecture

## System Overview

TradieHub is a WordPress-based contractor directory and marketplace for California. The system is built on a layered architecture:

```
Browser
  |
WordPress (Astra + TradieHub child theme + Elementor page builder)
  |
tradiehub-core plugin (custom integration layer)
  |
Plugin tier:
  - WooCommerce (order backbone)
  - TeraWallet (wallet and escrow primitives)
  - FluentCommunity (contractor community + messaging)
  - BuddyPress (member profiles and directory)
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
| released_at | datetime | When funds were released (null if held or disputed) |

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
