#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

VERSION="${VERSION:-${1:-}}"
BASE_URL="${BASE_URL:-${2:-https://127.0.0.1/aorern}}"
FULL_SMOKE_URL="${FULL_SMOKE_URL:-${3:-$BASE_URL}}"

if [[ -z "$VERSION" ]]; then
  echo "Uso: VERSION=vX.Y.Z bash scripts/release_tag.sh [VERSION] [BASE_URL] [FULL_SMOKE_URL]" >&2
  exit 1
fi

if ! [[ "$VERSION" =~ ^v[0-9]+\.[0-9]+\.[0-9]+([-.][A-Za-z0-9._]+)?$ ]]; then
  echo "VERSION inválida: '$VERSION'. Use formato vX.Y.Z (ex.: v1.4.0)." >&2
  exit 1
fi

if ! git diff --quiet || ! git diff --cached --quiet; then
  echo "Working tree com alterações. Commit/stash antes de criar tag de release." >&2
  exit 1
fi

if git rev-parse "$VERSION" >/dev/null 2>&1; then
  echo "Tag já existe: $VERSION" >&2
  exit 1
fi

echo "[release-tag] Executando release-check..."
BASE_URL="$BASE_URL" FULL_SMOKE_URL="$FULL_SMOKE_URL" bash scripts/release_check.sh

LATEST_ARTIFACT="$(ls -1t artifacts/release-check-*.md 2>/dev/null | head -n1 || true)"
if [[ -z "$LATEST_ARTIFACT" ]]; then
  echo "Nenhum artefato de release-check encontrado em artifacts/." >&2
  exit 1
fi

LATEST_SHA_FILE="${LATEST_ARTIFACT}.sha256"
if [[ ! -f "$LATEST_SHA_FILE" ]]; then
  echo "Checksum do artefato não encontrado: $LATEST_SHA_FILE" >&2
  exit 1
fi

(cd "$(dirname "$LATEST_SHA_FILE")" && sha256sum -c "$(basename "$LATEST_SHA_FILE")")

if ! rg -q "Status: APPROVED" "$LATEST_ARTIFACT"; then
  echo "Artefato não está aprovado: $LATEST_ARTIFACT" >&2
  exit 1
fi

CHECKSUM_VALUE="$(awk '{print $1}' "$LATEST_SHA_FILE")"
COMMIT_SHA="$(git rev-parse --short HEAD)"

TAG_MESSAGE=$(
  cat <<EOF
Release ${VERSION}

Commit: ${COMMIT_SHA}
Release check artifact: ${LATEST_ARTIFACT}
Artifact checksum (sha256): ${CHECKSUM_VALUE}
Status: APPROVED
EOF
)

git tag -a "$VERSION" -m "$TAG_MESSAGE"

echo "[release-tag] Tag criada: $VERSION"
echo "[release-tag] Commit: $COMMIT_SHA"
echo "[release-tag] Artefato: $LATEST_ARTIFACT"
echo "[release-tag] SHA256: $CHECKSUM_VALUE"
echo "[release-tag] Próximo passo: git push origin $VERSION"
