#!/usr/bin/env bash
set -euo pipefail

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/lib_test.sh"
testlib_init

BASE_URL="${1:-https://127.0.0.1/aorern}"
COOKIE_ADMIN="/tmp/aorern-it-admin-membership.cookie"
STAMP="$(date +%s)"

TMP_ADMIN_USER="itadmin${STAMP}"
TMP_ADMIN_EMAIL="it.admin.${STAMP}@aorern.local"
TMP_ADMIN_PASS="Temp#12345"

APP_A_EMAIL="it.app.a.${STAMP}@aorern.local"
APP_A_USER="itappa${STAMP}"
APP_A_CPF="$((61000000000 + (STAMP % 1000000000)))"
APP_B_EMAIL="it.app.b.${STAMP}@aorern.local"
APP_B_USER="itappb${STAMP}"
APP_B_CPF="$((71000000000 + (STAMP % 1000000000)))"
APP_C_EMAIL="it.app.c.${STAMP}@aorern.local"
APP_C_USER="itappc${STAMP}"
APP_C_CPF="$((81000000000 + (STAMP % 1000000000)))"

export TMP_ADMIN_USER TMP_ADMIN_EMAIL TMP_ADMIN_PASS
export APP_A_EMAIL APP_A_USER APP_A_CPF APP_B_EMAIL APP_B_USER APP_B_CPF APP_C_EMAIL APP_C_USER APP_C_CPF

cleanup() {
php <<'PHP' >/dev/null
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();

$stmt = $pdo->prepare("DELETE FROM membership_applications WHERE email IN (?, ?, ?)");
$stmt->execute([getenv('APP_A_EMAIL'), getenv('APP_B_EMAIL'), getenv('APP_C_EMAIL')]);

$stmt = $pdo->prepare("DELETE FROM pessoal WHERE cpf IN (?, ?, ?)");
$stmt->execute([getenv('APP_A_CPF'), getenv('APP_B_CPF'), getenv('APP_C_CPF')]);

$stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
$stmt->execute([getenv('TMP_ADMIN_EMAIL')]);
PHP
rm -f "$COOKIE_ADMIN" /tmp/aorern-it-membership-*.html
}

trap cleanup EXIT

echo "[it 1/8] Provisionando admin + 3 solicitações pendentes..."
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
    $stmtUser->execute([getenv('TMP_ADMIN_USER'), getenv('TMP_ADMIN_EMAIL'), $hash, null, 'admin', 'ativo', 1]);

    $stmtApp = $pdo->prepare(
        "INSERT INTO membership_applications
         (nome_completo,nome_mae,nome_pai,username_desejado,email,password_hash,cpf,cam,rg,data_nascimento,telefone,cidade,uf,ano_npor,posto_graduacao,numero_militar,nome_guerra,turma_npor,arma_quadro,avatar,documentos_json,observacoes,aceite_termo,status,status_associativo,criado_em,atualizado_em)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())"
    );

    $rows = [
        [getenv('APP_A_USER'), getenv('APP_A_EMAIL'), getenv('APP_A_CPF'), '20', 'IT A'],
        [getenv('APP_B_USER'), getenv('APP_B_EMAIL'), getenv('APP_B_CPF'), '21', 'IT B'],
        [getenv('APP_C_USER'), getenv('APP_C_EMAIL'), getenv('APP_C_CPF'), '22', 'IT C'],
    ];

    foreach ($rows as [$user, $email, $cpf, $numero, $guerra]) {
        $stmtApp->execute([
            "Solicitante {$guerra}",
            "Mãe {$guerra}",
            "Pai {$guerra}",
            $user,
            $email,
            $hash,
            $cpf,
            'CAM123',
            '10102976455/SSP-RN',
            '1990-04-22',
            '84981000111',
            'Natal',
            'RN',
            '2020',
            'Aspirante a Oficial',
            $numero,
            $guerra,
            'Turma Teste',
            'Infantaria',
            'assets/images/conscrito.png',
            json_encode([['name' => 'doc.pdf', 'path' => 'assets/images/conscrito.png', 'mime_type' => 'application/pdf', 'size' => 1000]], JSON_UNESCAPED_SLASHES),
            'Teste integração moderação',
            1,
            'pendente',
            'provisorio'
        ]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
PHP

get_id_by_email() {
  local email="$1"
  db_value "SELECT id FROM membership_applications WHERE email = ? LIMIT 1" "[\"${email}\"]"
}

get_status_by_email() {
  local email="$1"
  db_value "SELECT status FROM membership_applications WHERE email = ? LIMIT 1" "[\"${email}\"]"
}

echo "[it 2/8] Login admin..."
curl -k -s -c "$COOKIE_ADMIN" -b "$COOKIE_ADMIN" \
  --data-urlencode "username=${TMP_ADMIN_USER}" \
  --data-urlencode "password=${TMP_ADMIN_PASS}" \
  "${BASE_URL}/login/authenticate-admin" > /tmp/aorern-it-membership-login.html

APP_A_ID="$(get_id_by_email "$APP_A_EMAIL")"
APP_B_ID="$(get_id_by_email "$APP_B_EMAIL")"
APP_C_ID="$(get_id_by_email "$APP_C_EMAIL")"

assert_not_empty "$APP_A_ID" "Falha ao localizar ID da solicitação A."
assert_not_empty "$APP_B_ID" "Falha ao localizar ID da solicitação B."
assert_not_empty "$APP_C_ID" "Falha ao localizar ID da solicitação C."

echo "[it 3/8] Complementação com observação obrigatória..."
curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  --data-urlencode "observacoes_admin=Complementar documentação comprobatória." \
  "${BASE_URL}/admin/solicitacoes-filiacao/solicitar-complementacao/${APP_A_ID}" \
  > /tmp/aorern-it-membership-complementacao-ok.html

STATUS_A="$(get_status_by_email "$APP_A_EMAIL")"
if [[ "$STATUS_A" != "complementacao" ]]; then
  echo "Status inesperado após complementação de APP_A: ${STATUS_A}" >&2
  exit 1
fi

echo "[it 4/8] Complementação sem observação (deve falhar em validação)..."
curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  --data-urlencode "observacoes_admin=   " \
  "${BASE_URL}/admin/solicitacoes-filiacao/solicitar-complementacao/${APP_B_ID}" \
  > /tmp/aorern-it-membership-complementacao-bad.html

STATUS_B_AFTER_BAD_COMPLEMENT="$(get_status_by_email "$APP_B_EMAIL")"
if [[ "$STATUS_B_AFTER_BAD_COMPLEMENT" != "pendente" ]]; then
  echo "Status de APP_B deveria permanecer pendente após complementação inválida: ${STATUS_B_AFTER_BAD_COMPLEMENT}" >&2
  exit 1
fi

echo "[it 5/8] Aprovação com status associativo inválido (deve falhar)..."
curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  --data-urlencode "status_associativo=invalido" \
  --data-urlencode "observacoes_admin=Teste inválido" \
  "${BASE_URL}/admin/solicitacoes-filiacao/aprovar/${APP_B_ID}" \
  > /tmp/aorern-it-membership-aprovar-bad.html

STATUS_B_AFTER_BAD_APPROVAL="$(get_status_by_email "$APP_B_EMAIL")"
if [[ "$STATUS_B_AFTER_BAD_APPROVAL" != "pendente" ]]; then
  echo "Status de APP_B deveria permanecer pendente após aprovação inválida: ${STATUS_B_AFTER_BAD_APPROVAL}" >&2
  exit 1
fi

echo "[it 6/8] Aprovação válida..."
curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  --data-urlencode "status_associativo=efetivo" \
  --data-urlencode "observacoes_admin=Aprovado na integração" \
  "${BASE_URL}/admin/solicitacoes-filiacao/aprovar/${APP_B_ID}" \
  > /tmp/aorern-it-membership-aprovar-ok.html

STATUS_B="$(get_status_by_email "$APP_B_EMAIL")"
if [[ "$STATUS_B" != "aprovada" ]]; then
  echo "Status inesperado após aprovação de APP_B: ${STATUS_B}" >&2
  exit 1
fi

echo "[it 7/8] Rejeição..."
curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  --data-urlencode "observacoes_admin=Rejeitado na integração" \
  "${BASE_URL}/admin/solicitacoes-filiacao/rejeitar/${APP_C_ID}" \
  > /tmp/aorern-it-membership-rejeitar-ok.html

STATUS_C="$(get_status_by_email "$APP_C_EMAIL")"
if [[ "$STATUS_C" != "rejeitada" ]]; then
  echo "Status inesperado após rejeição de APP_C: ${STATUS_C}" >&2
  exit 1
fi

echo "[it 8/8] Verificando persistência no banco..."
php <<'PHP'
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();

$checks = [
    [getenv('APP_A_EMAIL'), 'complementacao'],
    [getenv('APP_B_EMAIL'), 'aprovada'],
    [getenv('APP_C_EMAIL'), 'rejeitada'],
];

foreach ($checks as [$email, $expected]) {
    $stmt = $pdo->prepare("SELECT status FROM membership_applications WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $status = (string) $stmt->fetchColumn();
    if ($status !== $expected) {
        fwrite(STDERR, "Status inesperado para {$email}. Esperado {$expected}, obtido {$status}" . PHP_EOL);
        exit(1);
    }
}

$stmt = $pdo->prepare("SELECT pessoal_id, status_associativo FROM membership_applications WHERE email = ? LIMIT 1");
$stmt->execute([getenv('APP_B_EMAIL')]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row || (int) ($row['pessoal_id'] ?? 0) <= 0 || (string) ($row['status_associativo'] ?? '') !== 'efetivo') {
    fwrite(STDERR, "Aprovação não persistiu vínculo/estado esperado para APP_B." . PHP_EOL);
    exit(1);
}

echo "OK integração de moderação admin\n";
PHP
