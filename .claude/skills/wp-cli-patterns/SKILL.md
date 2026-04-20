---
name: wp-cli-patterns
description: Use when writing or debugging WP-CLI commands for the TradieHub project. Covers JSON-encoded options, custom post type creation, user role management, batch operations, and common syntax traps.
---

# WP-CLI Patterns for TradieHub

## JSON-valued options (common trap)
When `wp option update` needs a JSON value, always pass `--format=json` AND escape properly:

```bash
# Correct
wp option update my_option '{"key":"value","num":42}' --format=json

# Wrong: WordPress stores it as a literal string
wp option update my_option '{"key":"value"}'
```

## Checking option storage format
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
- `wp b2bking ...` - B2BKing subcommands (check `wp help b2bking`)
- `wp woo-wallet ...` - TeraWallet operations via CLI
- `wp elementor ...` - Elementor CLI for cache flush
- `wp tradiehub generate-seo-pages` - our custom command in class-local-seo.php

## Common debug commands
```bash
wp doctor check --all
wp db size --tables --format=table
```

## Traps
- `wp user create` does NOT email the user. Good for seeding, bad if you accidentally run in production.
- Post meta with nested arrays: use `--meta_input` with JSON or the meta stores as literal `Array`.
- Rewrite rules do not auto-flush after CPT registration. Always run `wp rewrite flush --hard`.
