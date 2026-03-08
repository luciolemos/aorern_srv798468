#!/usr/bin/env bash
set -euo pipefail

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/lib_test.sh"
testlib_init

BASE_URL="https://127.0.0.1/aorern"
FULL_SMOKE_URL="${FULL_SMOKE_URL:-}"
UNIT_ONLY=0
SKIP_PHPUNIT=0

while [[ $# -gt 0 ]]; do
  case "$1" in
    --unit-only)
      UNIT_ONLY=1
      shift
      ;;
    --skip-phpunit)
      SKIP_PHPUNIT=1
      shift
      ;;
    --base-url)
      BASE_URL="${2:-}"
      shift 2
      ;;
    --full-smoke-url)
      FULL_SMOKE_URL="${2:-}"
      shift 2
      ;;
    *)
      if [[ "$BASE_URL" == "https://127.0.0.1/aorern" ]]; then
        BASE_URL="$1"
      elif [[ -z "$FULL_SMOKE_URL" ]]; then
        FULL_SMOKE_URL="$1"
      else
        echo "Argumento não reconhecido: $1" >&2
        exit 1
      fi
      shift
      ;;
  esac
done

run_step() {
  local label="$1"
  shift
  echo "[quality] ${label}"
  "$@"
}

if [[ "$SKIP_PHPUNIT" -eq 0 ]]; then
  run_step "PHPUnit" ./vendor/bin/phpunit --colors=never
fi

if [[ "$UNIT_ONLY" -eq 1 ]]; then
  echo "[quality] Modo unit-only concluído."
  exit 0
fi

run_step "Integração Diretoria" bash scripts/test_admin_diretoria_flow.sh "$BASE_URL"
run_step "Integração Moderação Filiação" bash scripts/test_admin_membership_moderation.sh "$BASE_URL"
run_step "Smoke Filiação (status)" bash scripts/smoke_membership_status_flow.sh "$BASE_URL"

if [[ -n "$FULL_SMOKE_URL" ]]; then
  run_step "Smoke Filiação completo" bash scripts/smoke_test_membership_flow.sh "$FULL_SMOKE_URL"
else
  echo "[quality] Smoke completo pulado (defina FULL_SMOKE_URL ou passe 2º argumento)."
fi

echo "[quality] Gate concluído com sucesso."
