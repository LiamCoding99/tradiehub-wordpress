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
