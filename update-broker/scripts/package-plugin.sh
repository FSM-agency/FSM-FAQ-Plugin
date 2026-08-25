#!/usr/bin/env bash
# Build a WordPress-ready zip with root folder fsm-faq/.
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
# Copy plugin files (exclude broker/tooling).
shopt -s dotglob nullglob
for item in "$ROOT"/*; do
  base="$(basename "$item")"
  case "$base" in
    .git|.github|update-broker|node_modules|.DS_Store|.cursor) continue ;;
  esac
  cp -a "$item" "$STAGE/fsm-faq/"
done
# Hidden files at root except .git
for item in "$ROOT"/.[!.]*; do
  [[ -e "$item" ]] || continue
  base="$(basename "$item")"
  case "$base" in
    .git|.github|.cursor|.DS_Store) continue ;;
  esac
  cp -a "$item" "$STAGE/fsm-faq/" 2>/dev/null || true
done

ZIP_PATH="$OUT_DIR/fsm-faq-${VERSION}.zip"
rm -f "$ZIP_PATH"
(cd "$STAGE" && zip -qr "$ZIP_PATH" fsm-faq)

echo "$ZIP_PATH"
