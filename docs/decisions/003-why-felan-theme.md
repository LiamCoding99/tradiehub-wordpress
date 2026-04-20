# ADR 003: Why Felan Theme

**Status:** Accepted
**Date:** 2026-04-20

## Context

TradieHub needed a WordPress theme that could handle a freelance/contractor marketplace with job listings, contractor profiles, and quote workflows without building all UI from scratch.

## Decision

Use Felan (ThemeForest, ~$59 one-time) as the parent theme, with all customization in a `felan-child` child theme.

## Reasons

1. **Purpose-built.** Felan is designed specifically for freelance marketplaces and job boards. Its default templates cover contractor listings, job posting, and profile pages - the exact pages TradieHub needs.
2. **Elementor integration.** Felan ships with Elementor-compatible widgets and demo content importable via a one-click importer. This dramatically reduces custom front-end work.
3. **WooCommerce compatibility.** Felan is WooCommerce-aware out of the box, which matters since WooCommerce powers our wallet and quote workflows.
4. **Portfolio optics.** A recognizable, polished theme lets the portfolio screenshots focus on TradieHub's unique features (escrow, community) rather than raw CSS work.

## Tradeoffs

- Paid theme (~$59) that cannot be committed to the public repo. The README documents this requirement and explains how to recreate the stack.
- The parent theme's TGMPA plugin install step requires a manual wp-admin visit, which cannot be scripted.
- Felan's built-in package/listing system overlaps with our B2BKing quote workflow. We disable the Felan package listing via a child theme filter to avoid confusion.

## Consequence

The `felan-child` theme handles all visual customization. No Felan parent theme files are edited. This is a hard rule documented in `.claude/CLAUDE.md`.
