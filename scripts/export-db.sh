#!/usr/bin/env bash
# Export the TradieHub database for versioning or sharing.
# Usage: bash scripts/export-db.sh

set -euo pipefail

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
OUTPUT="db-exports/tradiehub_${TIMESTAMP}.sql"
mkdir -p db-exports

echo "==> Exporting database to ${OUTPUT}..."
wp db export "$OUTPUT" --porcelain

echo "==> Done. File size: $(du -h "$OUTPUT" | cut -f1)"
echo "    Note: never commit db-exports/ to git (contains real data)."
