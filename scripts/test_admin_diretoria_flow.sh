#!/usr/bin/env bash
set -euo pipefail

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/lib_test.sh"
testlib_init

BASE_URL="${1:-https://127.0.0.1/aorern}"
COOKIE_ADMIN="/tmp/aorern-it-admin-diretoria.cookie"
STAMP="$(date +%s)"

TMP_ADMIN_USER="itdiradmin${STAMP}"
TMP_ADMIN_EMAIL="it.dir.admin.${STAMP}@aorern.local"
TMP_ADMIN_PASS="Temp#12345"

ASSOC_A_USER="itdirassoca${STAMP}"
ASSOC_A_EMAIL="it.dir.assoc.a.${STAMP}@aorern.local"
ASSOC_A_CPF="$((51000000000 + (STAMP % 1000000000)))"
ASSOC_B_USER="itdirassocb${STAMP}"
ASSOC_B_EMAIL="it.dir.assoc.b.${STAMP}@aorern.local"
ASSOC_B_CPF="$((61000000000 + (STAMP % 1000000000)))"

TERM_STAFF="MAND-IT-${STAMP}"
TERM_NAME="Mandato IT ${STAMP}"
FUNC_A_STAFF="FUNC-IT-A-${STAMP}"
FUNC_A_NAME="Presidente AORE/RN IT ${STAMP}"
FUNC_B_STAFF="FUNC-IT-B-${STAMP}"
FUNC_B_NAME="Diretor Administrativo IT ${STAMP}"

export TMP_ADMIN_USER TMP_ADMIN_EMAIL TMP_ADMIN_PASS
export ASSOC_A_USER ASSOC_A_EMAIL ASSOC_A_CPF ASSOC_B_USER ASSOC_B_EMAIL ASSOC_B_CPF
export TERM_STAFF TERM_NAME FUNC_A_STAFF FUNC_A_NAME FUNC_B_STAFF FUNC_B_NAME

cleanup() {
php <<'PHP' >/dev/null
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();

$stmt = $pdo->prepare("DELETE bm FROM board_memberships bm INNER JOIN board_terms bt ON bt.id = bm.term_id WHERE bt.nome = ?");
$stmt->execute([getenv('TERM_NAME')]);

$stmt = $pdo->prepare("DELETE FROM board_terms WHERE nome = ?");
$stmt->execute([getenv('TERM_NAME')]);

$stmt = $pdo->prepare("DELETE FROM pessoal WHERE cpf IN (?, ?)");
$stmt->execute([getenv('ASSOC_A_CPF'), getenv('ASSOC_B_CPF')]);

$stmt = $pdo->prepare("DELETE FROM funcoes WHERE staff_id IN (?, ?)");
$stmt->execute([getenv('FUNC_A_STAFF'), getenv('FUNC_B_STAFF')]);

$stmt = $pdo->prepare("DELETE FROM users WHERE email IN (?, ?, ?)");
$stmt->execute([getenv('TMP_ADMIN_EMAIL'), getenv('ASSOC_A_EMAIL'), getenv('ASSOC_B_EMAIL')]);
PHP
rm -f "$COOKIE_ADMIN" /tmp/aorern-it-diretoria-*.html
}

trap cleanup EXIT

echo "[it 1/8] Provisionando admin, associados, mandato e funções..."
php <<'PHP'
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();
$pdo->beginTransaction();
try {
    $hash = password_hash(getenv('TMP_ADMIN_PASS'), PASSWORD_BCRYPT);

    $stmtUser = $pdo->prepare(
        "INSERT INTO users (username,email,password,avatar,role,status,ativo,created_at,updated_at)
         VALUES (?,?,?,?,?,?,?,NOW(),NOW())"
    );
    $stmtPessoal = $pdo->prepare(
        "INSERT INTO pessoal (staff_id,nome,cpf,user_id,funcao_id,data_admissao,status,status_associativo,criado_em)
         VALUES (?,?,?,?,?,CURDATE(),'Ativo','efetivo',NOW())"
    );
    $stmtFuncao = $pdo->prepare(
        "INSERT INTO funcoes (staff_id,nome,criado_em) VALUES (?,?,NOW())"
    );
    $stmtMandato = $pdo->prepare(
        "INSERT INTO board_terms (nome,status,data_inicio,data_fim,observacoes,criado_em,atualizado_em)
         VALUES (?,?,?,?,?,NOW(),NOW())"
    );

    $stmtUser->execute([getenv('TMP_ADMIN_USER'), getenv('TMP_ADMIN_EMAIL'), $hash, null, 'admin', 'ativo', 1]);

    $stmtFuncao->execute([getenv('FUNC_A_STAFF'), getenv('FUNC_A_NAME')]);
    $funcaoAId = (int) $pdo->lastInsertId();
    $stmtFuncao->execute([getenv('FUNC_B_STAFF'), getenv('FUNC_B_NAME')]);
    $stmtMandato->execute([getenv('TERM_NAME'), 'active', date('Y-m-d'), null, 'Integração automatizada']);

    $stmtUser->execute([getenv('ASSOC_A_USER'), getenv('ASSOC_A_EMAIL'), $hash, null, 'usuario', 'ativo', 1]);
    $userAId = (int) $pdo->lastInsertId();
    $stmtPessoal->execute(['SE-IT-' . getenv('ASSOC_A_CPF'), 'Associado IT A', getenv('ASSOC_A_CPF'), $userAId, $funcaoAId]);

    $stmtUser->execute([getenv('ASSOC_B_USER'), getenv('ASSOC_B_EMAIL'), $hash, null, 'usuario', 'ativo', 1]);
    $userBId = (int) $pdo->lastInsertId();
    $stmtPessoal->execute(['SE-IT-' . getenv('ASSOC_B_CPF'), 'Associado IT B', getenv('ASSOC_B_CPF'), $userBId, $funcaoAId]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
PHP

lookup_id() {
  local table="$1"
  local key="$2"
  local value="$3"
  local safe_table safe_key
  safe_table="$(printf '%s' "$table" | tr -cd 'a-z_')"
  safe_key="$(printf '%s' "$key" | tr -cd 'a-z_')"
  db_value "SELECT id FROM ${safe_table} WHERE ${safe_key} = ? LIMIT 1" "[\"${value}\"]"
}

read_user_role() {
  local email="$1"
  db_value "SELECT role FROM users WHERE email = ? LIMIT 1" "[\"${email}\"]"
}

count_memberships() {
  local term_id="$1"
  db_value "SELECT COUNT(*) FROM board_memberships WHERE term_id = ?" "[${term_id}]"
}

read_membership_state() {
  local id="$1"
  MEMBERSHIP_ID="$id" php <<'PHP'
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();
$stmt = $pdo->prepare("SELECT is_active, access_role FROM board_memberships WHERE id = ? LIMIT 1");
$stmt->execute([(int) getenv('MEMBERSHIP_ID')]);
$row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
echo ($row['is_active'] ?? '') . '|' . ($row['access_role'] ?? '');
PHP
}

DIRETORIA_ID_BY_FUNC_TERM() {
  local funcao_id="$1"
  local term_id="$2"
  FUNCAO_ID="$funcao_id" TERM_ID="$term_id" php <<'PHP'
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();
$stmt = $pdo->prepare("SELECT id FROM board_memberships WHERE funcao_id = ? AND term_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([(int) getenv('FUNCAO_ID'), (int) getenv('TERM_ID')]);
echo (string) $stmt->fetchColumn();
PHP
}

TERM_ID="$(lookup_id board_terms nome "$TERM_NAME")"
FUNCAO_A_ID="$(lookup_id funcoes staff_id "$FUNC_A_STAFF")"
FUNCAO_B_ID="$(lookup_id funcoes staff_id "$FUNC_B_STAFF")"
PESSOAL_A_ID="$(lookup_id pessoal cpf "$ASSOC_A_CPF")"
PESSOAL_B_ID="$(lookup_id pessoal cpf "$ASSOC_B_CPF")"

assert_not_empty "$TERM_ID" "Falha ao resolver ID do mandato na integração de Diretoria."
assert_not_empty "$FUNCAO_A_ID" "Falha ao resolver ID da função A na integração de Diretoria."
assert_not_empty "$FUNCAO_B_ID" "Falha ao resolver ID da função B na integração de Diretoria."
assert_not_empty "$PESSOAL_A_ID" "Falha ao resolver ID do associado A na integração de Diretoria."
assert_not_empty "$PESSOAL_B_ID" "Falha ao resolver ID do associado B na integração de Diretoria."

echo "[it 2/8] Login admin..."
curl -k -s -c "$COOKIE_ADMIN" -b "$COOKIE_ADMIN" \
  --data-urlencode "username=${TMP_ADMIN_USER}" \
  --data-urlencode "password=${TMP_ADMIN_PASS}" \
  "${BASE_URL}/login/authenticate-admin" > /tmp/aorern-it-diretoria-login.html

echo "[it 3/8] Criando vínculo de diretoria (gerente)..."
curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  "${BASE_URL}/admin/diretoria/cadastrar" > /tmp/aorern-it-diretoria-cadastrar-a.html
CSRF_A="$(extract_csrf /tmp/aorern-it-diretoria-cadastrar-a.html)"
if [[ -z "$CSRF_A" ]]; then
  echo "Token CSRF não encontrado no cadastro da diretoria." >&2
  head -n 40 /tmp/aorern-it-diretoria-cadastrar-a.html >&2 || true
  exit 1
fi

curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  --data-urlencode "csrf_token=${CSRF_A}" \
  --data-urlencode "funcao_id=${FUNCAO_A_ID}" \
  --data-urlencode "grupo=Diretoria Executiva" \
  --data-urlencode "ordem=1" \
  --data-urlencode "term_id=${TERM_ID}" \
  --data-urlencode "pessoal_id=${PESSOAL_A_ID}" \
  --data-urlencode "is_active=1" \
  --data-urlencode "access_role=gerente" \
  --data-urlencode "observacoes=Integração A" \
  "${BASE_URL}/admin/diretoria/salvar" > /tmp/aorern-it-diretoria-salvar-a.html

COUNT_AFTER_A="$(count_memberships "$TERM_ID")"
if [[ "$COUNT_AFTER_A" != "1" ]]; then
  echo "Esperado 1 registro após criação A, encontrado ${COUNT_AFTER_A}." >&2
  exit 1
fi

ROLE_A="$(read_user_role "$ASSOC_A_EMAIL")"
if [[ "$ROLE_A" != "gerente" ]]; then
  echo "Perfil do associado A não foi promovido para gerente (atual: ${ROLE_A})." >&2
  exit 1
fi

echo "[it 4/8] Bloqueando duplicidade de função no mesmo mandato..."
curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  "${BASE_URL}/admin/diretoria/cadastrar" > /tmp/aorern-it-diretoria-cadastrar-dup.html
CSRF_DUP="$(extract_csrf /tmp/aorern-it-diretoria-cadastrar-dup.html)"

curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  --data-urlencode "csrf_token=${CSRF_DUP}" \
  --data-urlencode "funcao_id=${FUNCAO_A_ID}" \
  --data-urlencode "grupo=Diretoria Executiva" \
  --data-urlencode "ordem=2" \
  --data-urlencode "term_id=${TERM_ID}" \
  --data-urlencode "pessoal_id=${PESSOAL_B_ID}" \
  --data-urlencode "is_active=1" \
  --data-urlencode "access_role=operador" \
  "${BASE_URL}/admin/diretoria/salvar" > /tmp/aorern-it-diretoria-dup.html

COUNT_AFTER_DUP="$(count_memberships "$TERM_ID")"
if [[ "$COUNT_AFTER_DUP" != "1" ]]; then
  echo "Validação de duplicidade falhou: esperava 1 registro, encontrou ${COUNT_AFTER_DUP}." >&2
  exit 1
fi

echo "[it 5/8] Criando segundo vínculo válido (operador)..."
curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  "${BASE_URL}/admin/diretoria/cadastrar" > /tmp/aorern-it-diretoria-cadastrar-b.html
CSRF_B="$(extract_csrf /tmp/aorern-it-diretoria-cadastrar-b.html)"

curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  --data-urlencode "csrf_token=${CSRF_B}" \
  --data-urlencode "funcao_id=${FUNCAO_B_ID}" \
  --data-urlencode "grupo=Diretoria Executiva" \
  --data-urlencode "ordem=2" \
  --data-urlencode "term_id=${TERM_ID}" \
  --data-urlencode "pessoal_id=${PESSOAL_B_ID}" \
  --data-urlencode "is_active=1" \
  --data-urlencode "access_role=operador" \
  --data-urlencode "observacoes=Integração B" \
  "${BASE_URL}/admin/diretoria/salvar" > /tmp/aorern-it-diretoria-salvar-b.html

COUNT_AFTER_B="$(count_memberships "$TERM_ID")"
if [[ "$COUNT_AFTER_B" != "2" ]]; then
  echo "Esperado 2 registros após criação B, encontrado ${COUNT_AFTER_B}." >&2
  exit 1
fi

ROLE_B="$(read_user_role "$ASSOC_B_EMAIL")"
if [[ "$ROLE_B" != "operador" ]]; then
  echo "Perfil do associado B não foi promovido para operador (atual: ${ROLE_B})." >&2
  exit 1
fi

DIRETORIA_B_ID="$(DIRETORIA_ID_BY_FUNC_TERM "$FUNCAO_B_ID" "$TERM_ID")"
if [[ -z "$DIRETORIA_B_ID" ]]; then
  echo "Registro B da diretoria não localizado para edição." >&2
  exit 1
fi

echo "[it 6/8] Editando vínculo para remover perfil administrativo..."
curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  "${BASE_URL}/admin/diretoria/editar/${DIRETORIA_B_ID}" > /tmp/aorern-it-diretoria-editar-b.html
CSRF_EDIT="$(extract_csrf /tmp/aorern-it-diretoria-editar-b.html)"

curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  --data-urlencode "csrf_token=${CSRF_EDIT}" \
  --data-urlencode "funcao_id=${FUNCAO_B_ID}" \
  --data-urlencode "grupo=Diretoria Executiva" \
  --data-urlencode "ordem=2" \
  --data-urlencode "term_id=${TERM_ID}" \
  --data-urlencode "pessoal_id=${PESSOAL_B_ID}" \
  --data-urlencode "is_active=0" \
  --data-urlencode "access_role=" \
  --data-urlencode "observacoes=Rebaixado para usuário padrão" \
  "${BASE_URL}/admin/diretoria/atualizar/${DIRETORIA_B_ID}" > /tmp/aorern-it-diretoria-atualizar-b.html

STATE_B="$(read_membership_state "$DIRETORIA_B_ID")"
if [[ "$STATE_B" != "0|" ]]; then
  echo "Registro B não ficou inativo/sem perfil admin (estado: ${STATE_B})." >&2
  exit 1
fi

ROLE_B_AFTER="$(read_user_role "$ASSOC_B_EMAIL")"
if [[ "$ROLE_B_AFTER" != "usuario" ]]; then
  echo "Perfil do associado B deveria retornar para usuario (atual: ${ROLE_B_AFTER})." >&2
  exit 1
fi

DIRETORIA_A_ID="$(DIRETORIA_ID_BY_FUNC_TERM "$FUNCAO_A_ID" "$TERM_ID")"
if [[ -z "$DIRETORIA_A_ID" ]]; then
  echo "Registro A da diretoria não localizado para exclusão." >&2
  exit 1
fi

echo "[it 7/8] Excluindo vínculo A e validando sincronização do papel..."
curl -k -s -L -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  "${BASE_URL}/admin/diretoria/deletar/${DIRETORIA_A_ID}" > /tmp/aorern-it-diretoria-deletar-a.html

COUNT_AFTER_DELETE="$(count_memberships "$TERM_ID")"
if [[ "$COUNT_AFTER_DELETE" != "1" ]]; then
  echo "Esperado 1 registro após exclusão A, encontrado ${COUNT_AFTER_DELETE}." >&2
  exit 1
fi

ROLE_A_AFTER="$(read_user_role "$ASSOC_A_EMAIL")"
if [[ "$ROLE_A_AFTER" != "usuario" ]]; then
  echo "Perfil do associado A deveria retornar para usuario (atual: ${ROLE_A_AFTER})." >&2
  exit 1
fi

echo "[it 8/8] Verificando persistência final no banco..."
php <<'PHP'
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM board_memberships bm INNER JOIN board_terms bt ON bt.id = bm.term_id WHERE bt.nome = ?");
$stmt->execute([getenv('TERM_NAME')]);
$remaining = (int) $stmt->fetchColumn();
if ($remaining !== 1) {
    fwrite(STDERR, "Esperado 1 registro de diretoria remanescente, encontrado {$remaining}." . PHP_EOL);
    exit(1);
}

echo "OK integração de diretoria\n";
PHP
