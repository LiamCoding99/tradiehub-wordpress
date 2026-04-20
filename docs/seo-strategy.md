# TradieHub SEO Strategy

## Goal

Rank for high-intent local queries like "licensed plumber in Los Angeles" and "electrician near me California" - the exact searches homeowners make when they need a contractor.

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

This mirrors the SEO architecture of Yelp (/category/plumbers/los-angeles), Angi, and HomeAdvisor - all of which built their domain authority on programmatic local pages.

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
