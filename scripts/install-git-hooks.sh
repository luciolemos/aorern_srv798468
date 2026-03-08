#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
HOOKS_DIR="$ROOT_DIR/.git/hooks"
PRE_COMMIT_HOOK="$HOOKS_DIR/pre-commit"

if [[ ! -d "$HOOKS_DIR" ]]; then
  echo "Diretório de hooks não encontrado: $HOOKS_DIR" >&2
  exit 1
fi

cat > "$PRE_COMMIT_HOOK" <<'HOOK'
#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(git rev-parse --show-toplevel)"
cd "$ROOT_DIR"

bash scripts/preflight.sh
HOOK

chmod +x "$PRE_COMMIT_HOOK"
echo "Hook pre-commit instalado em $PRE_COMMIT_HOOK"
