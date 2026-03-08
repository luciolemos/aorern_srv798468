#!/usr/bin/env bash

# Biblioteca comum para scripts de integração/smoke.
# shellcheck disable=SC2034

set -euo pipefail

testlib_init() {
  local script_dir
  script_dir="$(cd "$(dirname "${BASH_SOURCE[1]}")/.." && pwd)"
  ROOT_DIR="${ROOT_DIR:-$script_dir}"
  export ROOT_DIR
  export AORERN_ROOT="${AORERN_ROOT:-$ROOT_DIR}"
}

run_php() {
  php <<'PHP'
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
eval(getenv('TESTLIB_PHP_CODE'));
PHP
}

db_value() {
  local sql="$1"
  local binds="${2:-}"
  TESTLIB_PHP_CODE='
    $pdo = \App\Core\Database::connect();
    $sql = getenv("TESTLIB_SQL");
    $raw = getenv("TESTLIB_BINDS");
    $binds = $raw !== false && $raw !== "" ? json_decode($raw, true) : [];
    if (!is_array($binds)) {
        $binds = [];
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($binds);
    echo (string) $stmt->fetchColumn();
  ' TESTLIB_SQL="$sql" TESTLIB_BINDS="$binds" run_php
}

assert_not_empty() {
  local value="${1:-}"
  local message="${2:-Valor obrigatório não informado.}"
  if [[ -z "$value" ]]; then
    echo "$message" >&2
    exit 1
  fi
}

extract_csrf() {
  local file="$1"
  grep -o 'name="csrf_token" value="[^"]*"' "$file" | head -n1 | sed 's/.*value="//; s/"$//' || true
}
