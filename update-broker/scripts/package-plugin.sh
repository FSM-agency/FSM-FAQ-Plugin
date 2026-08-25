#!/usr/bin/env bash
# Build a WordPress-ready zip with root folder fsm-faq/.
# Explicit allowlist only — never copy root dotfiles (.env, .npmrc, .aws, etc.).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
OUT_DIR="${1:-$ROOT/update-broker/dist}"
VERSION="${2:-}"
# Resolve relative OUT_DIR against CWD before we cd into the staging dir.
OUT_DIR="$(cd "$(dirname "$OUT_DIR")" && pwd)/$(basename "$OUT_DIR")"
mkdir -p "$OUT_DIR"

if [[ -z "$VERSION" ]]; then
  VERSION="$(grep -E "^\s*\* Version:" "$ROOT/fsm-faq.php" | head -1 | sed -E 's/.*Version:\s*//')"
fi

STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT

mkdir -p "$STAGE/fsm-faq"

# Only ship WordPress plugin runtime files. Do not copy hidden root files.
ALLOWLIST=(
  fsm-faq.php
  readme.txt
  assets
  includes
  plugin-update-checker-5.6
  vendor
)

for name in "${ALLOWLIST[@]}"; do
  src="$ROOT/$name"
  if [[ -e "$src" ]]; then
    cp -a "$src" "$STAGE/fsm-faq/"
  fi
done

ZIP_PATH="$OUT_DIR/fsm-faq-${VERSION}.zip"
rm -f "$ZIP_PATH"
(cd "$STAGE" && zip -qr "$ZIP_PATH" fsm-faq)

echo "$ZIP_PATH"
