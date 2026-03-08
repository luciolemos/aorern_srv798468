#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

BASE_URL="${BASE_URL:-${1:-https://127.0.0.1/aorern}}"
FULL_SMOKE_URL="${FULL_SMOKE_URL:-${2:-$BASE_URL}}"
ARTIFACT_DIR="$ROOT_DIR/artifacts"
STAMP_UTC="$(date -u +%Y%m%d-%H%M%S)"
ARTIFACT_FILE="$ARTIFACT_DIR/release-check-${STAMP_UTC}.md"
ARTIFACT_SHA_FILE="${ARTIFACT_FILE}.sha256"
ARTIFACT_KEEP="${ARTIFACT_KEEP:-20}"

mkdir -p "$ARTIFACT_DIR"
touch "$ARTIFACT_FILE"
if [[ ! -w "$ARTIFACT_FILE" ]]; then
  echo "Não foi possível escrever no artefato: $ARTIFACT_FILE" >&2
  exit 1
fi

exec > >(tee -a "$ARTIFACT_FILE") 2>&1

COMMIT_SHA="$(git rev-parse --short HEAD 2>/dev/null || echo 'desconhecido')"
RUN_AT_UTC="$(date -u '+%Y-%m-%d %H:%M:%S UTC')"

on_exit() {
  local code=$?
  if [[ "$code" -ne 0 ]]; then
    echo ""
    echo "## Resultado"
    echo "- Status: FAILED"
    echo "- Artefato: ${ARTIFACT_FILE}"
    echo "- Encerrado em: $(date -u '+%Y-%m-%d %H:%M:%S UTC')"
  fi
}
trap on_exit EXIT

echo "# Release Check"
echo ""
echo "- Executado em: ${RUN_AT_UTC}"
echo "- Commit: ${COMMIT_SHA}"
echo "- BASE_URL: ${BASE_URL}"
echo "- FULL_SMOKE_URL: ${FULL_SMOKE_URL}"
echo ""

echo "[release-check] 1/3 preflight"
bash scripts/preflight.sh

echo "[release-check] 2/3 quality-full"
bash scripts/run_quality_gate.sh --base-url "$BASE_URL" --full-smoke-url "$FULL_SMOKE_URL"

echo "[release-check] 3/3 migrations pendentes"
php <<'PHP'
<?php
$root = getcwd();
require $root . '/vendor/autoload.php';

$migrationFiles = glob($root . '/database/migrations/*.php') ?: [];
$available = [];
foreach ($migrationFiles as $file) {
    $name = pathinfo($file, PATHINFO_FILENAME);
    if ($name !== '' && $name[0] !== '.') {
        $available[$name] = true;
    }
}

$pdo = \App\Core\Database::connect();
$stmt = $pdo->query("SHOW TABLES LIKE 'migrations'");
$hasTable = (bool) $stmt->fetchColumn();

if (!$hasTable) {
    fwrite(STDERR, "Tabela migrations não existe. Execute php migrate migrate antes do deploy.\n");
    exit(1);
}

$stmt = $pdo->query("SELECT migration FROM migrations");
$executedRows = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
$executed = [];
foreach ($executedRows as $name) {
    $executed[(string) $name] = true;
}

$pending = array_values(array_diff(array_keys($available), array_keys($executed)));
sort($pending);

if ($pending !== []) {
    fwrite(STDERR, "Migrations pendentes detectadas:\n");
    foreach ($pending as $migration) {
        fwrite(STDERR, " - {$migration}\n");
    }
    exit(1);
}

echo "OK migrations (sem pendências)\n";
PHP

echo ""
echo "## Resultado"
echo "- Status: APPROVED"
echo "- Artefato: ${ARTIFACT_FILE}"
echo "- Encerrado em: $(date -u '+%Y-%m-%d %H:%M:%S UTC')"

if [[ "$ARTIFACT_KEEP" =~ ^[0-9]+$ ]] && [[ "$ARTIFACT_KEEP" -gt 0 ]]; then
  mapfile -t ARTIFACT_FILES < <(ls -1t "$ARTIFACT_DIR"/release-check-*.md 2>/dev/null || true)
  if [[ "${#ARTIFACT_FILES[@]}" -gt "$ARTIFACT_KEEP" ]]; then
    for old_file in "${ARTIFACT_FILES[@]:$ARTIFACT_KEEP}"; do
      rm -f "$old_file"
      rm -f "${old_file}.sha256"
      echo "[release-check] artefato removido por rotação: $old_file"
    done
  fi
fi

sha256sum "$ARTIFACT_FILE" > "$ARTIFACT_SHA_FILE"
