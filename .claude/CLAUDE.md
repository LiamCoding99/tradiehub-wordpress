# TradieHub Project: Always-On Rules

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
