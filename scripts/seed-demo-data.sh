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
  --post_title="Bathroom remodel in Oakland - tub to walk-in shower" \
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
  --post_title="Interior painting - 3BR house in Fresno" \
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
