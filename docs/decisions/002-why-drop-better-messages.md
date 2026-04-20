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
