# TradieHub

> A licensed-contractor directory and marketplace for California, with escrow-protected deposits and a contractor-only community.

## The Problem

Hiring a contractor in California is stressful. Homeowners do not know if the contractor is licensed, worry about upfront deposits disappearing, and have no way to see honest community reviews (other contractors know who is reliable better than random customers do).

Most trades directories (Angi, Thumbtack, HomeAdvisor) just pass leads and charge contractors per-lead fees. There is no financial protection for the homeowner, and no community for contractors.

## The Solution

TradieHub is a directory, marketplace, and community where:

1. Contractors create verified profiles with their CSLB license number, service areas, specialties, and portfolio photos.
2. Homeowners post jobs with a budget and location. Contractors submit quotes.
3. When a homeowner accepts a quote, they pay a deposit into TradieHub's wallet - held in escrow. Funds are released to the contractor when both parties confirm the job is complete.
4. Contractors have a private community to discuss jobs, tools, code compliance, and referrals. This is the retention hook - contractors return for the community even when they do not have active jobs.

## Features

- Licensed contractor directory (10 California cities, 10 trades, 100 SEO landing pages)
- Job posting and quote request workflow
- Escrow-style wallet: funds held until job completion
- Contractor-only community with trade-specific spaces (powered by FluentCommunity)
- Real-time messaging between homeowners and contractors
- Schema.org LocalBusiness markup on all contractor profiles
- Programmatic SEO landing pages (same pattern as Yelp and Angi)

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
- [class-contractor-profile.php](wp-content/plugins/tradiehub-core/includes/class-contractor-profile.php) - CSLB license fields, service area meta, shortcode
- [class-quote-workflow.php](wp-content/plugins/tradiehub-core/includes/class-quote-workflow.php) - job and quote CPTs, REST API, email notifications
- [class-escrow-wallet.php](wp-content/plugins/tradiehub-core/includes/class-escrow-wallet.php) - escrow state machine, TeraWallet bridge, admin dashboard
- [class-local-seo.php](wp-content/plugins/tradiehub-core/includes/class-local-seo.php) - JSON-LD schema, rewrite rules, WP-CLI page generator

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

*(Liam writes this section - voice-critical, personal reflection on what was hard, what worked, what would be done differently.)*

## What Is Next

- Live CSLB license verification API (scoped in class-contractor-profile.php with a TODO comment)
- Stripe integration for wallet top-ups (currently mocked)
- Mobile app via React Native sharing auth with the WordPress REST API

---

Built by [Liam](https://liam-portfolio-omega.vercel.app/)
