# ADR 003: Why Astra Theme

**Status:** Accepted
**Date:** 2026-04-20

## Context

TradieHub needed a WordPress parent theme that is lightweight, Elementor-compatible, and allows full visual customization via a child theme - without a licensing cost.

## Decision

Use Astra (free, wordpress.org) as the parent theme, with all TradieHub customization in a `tradiehub` child theme.

## Reasons

1. **Free.** Astra is on wordpress.org and installs via `wp theme install astra`. No ThemeForest account, no ZIP file to track outside the repo.
2. **Lightweight.** Astra loads under 50KB with no jQuery dependency. It does not bundle a job-board UI we would need to disable.
3. **Elementor-first.** Astra is designed around Elementor for page templates, which is the page builder already in the stack.
4. **Customizer API.** Astra exposes global color slots (global-color-1, global-color-2) that we wire to TradieHub brand colors in `customizations.php`, so the whole site updates from one place.
5. **No TGMPA.** Astra does not force-install required plugins via TGMPA, so setup is fully scriptable.

## Tradeoffs

- Astra does not ship a purpose-built contractor listing template. Those pages are handled by tradiehub-core's SEO rewrite rules and the `[tradiehub_contractor_profile]` shortcode rather than a theme template.
- Visual polish requires more CSS work in `tradiehub/style.css` compared to a purpose-built job-board theme.

## Consequence

The `tradiehub` child theme is committed to the repo. Astra parent is installed via `scripts/setup.sh` (`wp theme install astra`). No parent theme files are edited.
