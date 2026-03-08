#!/usr/bin/env bash
set -euo pipefail

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/lib_test.sh"
testlib_init

BASE_URL="${1:-https://127.0.0.1/aorern}"
COOKIE_ADMIN="/tmp/aorern-smoke-admin-membership.cookie"
STAMP="$(date +%s)"

TMP_ADMIN_USER="smkadmin${STAMP}"
TMP_ADMIN_EMAIL="smk.admin.${STAMP}@aorern.local"
TMP_ADMIN_PASS="Temp#12345"

APP1_EMAIL="smk.app1.${STAMP}@aorern.local"
APP1_USER="smkapp1${STAMP}"
APP1_CPF="$((41000000000 + (STAMP % 1000000000)))"
APP2_EMAIL="smk.app2.${STAMP}@aorern.local"
APP2_USER="smkapp2${STAMP}"
APP2_CPF="$((51000000000 + (STAMP % 1000000000)))"

export TMP_ADMIN_USER TMP_ADMIN_EMAIL TMP_ADMIN_PASS APP1_EMAIL APP1_USER APP1_CPF APP2_EMAIL APP2_USER APP2_CPF

cleanup() {
php <<'PHP' >/dev/null
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();

$stmt = $pdo->prepare("DELETE FROM membership_applications WHERE email IN (?, ?)");
$stmt->execute([getenv('APP1_EMAIL'), getenv('APP2_EMAIL')]);

$stmt = $pdo->prepare("DELETE FROM pessoal WHERE cpf IN (?, ?)");
$stmt->execute([getenv('APP1_CPF'), getenv('APP2_CPF')]);

$stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
$stmt->execute([getenv('TMP_ADMIN_EMAIL')]);
PHP
rm -f "$COOKIE_ADMIN" /tmp/aorern-smoke-membership-*.html
}

trap cleanup EXIT

echo "[1/7] Provisionando admin temporário e 2 solicitações pendentes..."
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
    $stmtUser->execute([
        getenv('TMP_ADMIN_USER'),
        getenv('TMP_ADMIN_EMAIL'),
        $hash,
        null,
        'admin',
        'ativo',
        1
    ]);

    $stmtApp = $pdo->prepare(
        "INSERT INTO membership_applications
         (nome_completo,nome_mae,nome_pai,username_desejado,email,password_hash,cpf,cam,rg,data_nascimento,telefone,cidade,uf,ano_npor,posto_graduacao,numero_militar,nome_guerra,turma_npor,arma_quadro,avatar,documentos_json,observacoes,aceite_termo,status,status_associativo,criado_em,atualizado_em)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())"
    );

    $stmtApp->execute([
        'Solicitante Smoke 1',
        'Mãe Smoke 1',
        'Pai Smoke 1',
        getenv('APP1_USER'),
        getenv('APP1_EMAIL'),
        $hash,
        getenv('APP1_CPF'),
        'CAM123456',
        '10102976455/SSP-RN',
        '1990-04-22',
        '84981000111',
        'Natal',
        'RN',
        '2020',
        'Aspirante a Oficial',
        '20',
        'SMOKE UM',
        'Turma Teste 2020',
        'Infantaria',
        'assets/images/conscrito.png',
        json_encode([['name' => 'doc.pdf', 'path' => 'assets/images/conscrito.png', 'mime_type' => 'application/pdf', 'size' => 1000]], JSON_UNESCAPED_SLASHES),
        'Seed temporária do smoke test',
        1,
        'pendente',
        'provisorio'
    ]);

    $stmtApp->execute([
        'Solicitante Smoke 2',
        'Mãe Smoke 2',
        'Pai Smoke 2',
        getenv('APP2_USER'),
        getenv('APP2_EMAIL'),
        $hash,
        getenv('APP2_CPF'),
        'CAM654321',
        '20102976455/SSP-RN',
        '1991-05-23',
        '84981000222',
        'Natal',
        'RN',
        '2021',
        'Aspirante a Oficial',
        '21',
        'SMOKE DOIS',
        'Turma Teste 2021',
        'Infantaria',
        'assets/images/conscrito.png',
        json_encode([['name' => 'doc.pdf', 'path' => 'assets/images/conscrito.png', 'mime_type' => 'application/pdf', 'size' => 1000]], JSON_UNESCAPED_SLASHES),
        'Seed temporária do smoke test',
        1,
        'pendente',
        'provisorio'
    ]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
PHP

echo "[2/7] Login admin..."
curl -k -s -c "$COOKIE_ADMIN" -b "$COOKIE_ADMIN" \
  --data-urlencode "username=${TMP_ADMIN_USER}" \
  --data-urlencode "password=${TMP_ADMIN_PASS}" \
  "${BASE_URL}/login/authenticate-admin" > /tmp/aorern-smoke-membership-login.html

APP1_ID="$(db_value "SELECT id FROM membership_applications WHERE email = ? LIMIT 1" "[\"${APP1_EMAIL}\"]")"
APP2_ID="$(db_value "SELECT id FROM membership_applications WHERE email = ? LIMIT 1" "[\"${APP2_EMAIL}\"]")"

assert_not_empty "$APP1_ID" "Falha ao localizar solicitação temporária APP1."
assert_not_empty "$APP2_ID" "Falha ao localizar solicitação temporária APP2."

echo "[3/7] Solicitar complementação da primeira solicitação..."
curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  --data-urlencode "observacoes_admin=Favor reenviar documentação legível." \
  "${BASE_URL}/admin/solicitacoes-filiacao/solicitar-complementacao/${APP1_ID}" \
  > /tmp/aorern-smoke-membership-complementacao.html

php <<'PHP'
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();
$stmt = $pdo->prepare("SELECT status FROM membership_applications WHERE email = ? LIMIT 1");
$stmt->execute([getenv('APP1_EMAIL')]);
$status = (string) $stmt->fetchColumn();
if ($status !== 'complementacao') {
    fwrite(STDERR, "Status esperado 'complementacao' e recebido '{$status}'." . PHP_EOL);
    exit(1);
}
echo "OK complementacao\n";
PHP

echo "[4/7] Aprovar primeira solicitação..."
curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  --data-urlencode "status_associativo=efetivo" \
  --data-urlencode "observacoes_admin=Aprovado no smoke test." \
  "${BASE_URL}/admin/solicitacoes-filiacao/aprovar/${APP1_ID}" \
  > /tmp/aorern-smoke-membership-aprovar.html

php <<'PHP'
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();
$stmt = $pdo->prepare("SELECT status, pessoal_id FROM membership_applications WHERE email = ? LIMIT 1");
$stmt->execute([getenv('APP1_EMAIL')]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row || (string) $row['status'] !== 'aprovada' || (int) ($row['pessoal_id'] ?? 0) <= 0) {
    fwrite(STDERR, "Falha na aprovação da solicitação 1." . PHP_EOL);
    exit(1);
}
$stmtPessoal = $pdo->prepare("SELECT COUNT(*) FROM pessoal WHERE id = ?");
$stmtPessoal->execute([(int) $row['pessoal_id']]);
if ((int) $stmtPessoal->fetchColumn() !== 1) {
    fwrite(STDERR, "pessoal_id criado na aprovação não encontrado." . PHP_EOL);
    exit(1);
}
echo "OK aprovacao\n";
PHP

echo "[5/7] Rejeitar segunda solicitação..."
curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  --data-urlencode "observacoes_admin=Rejeitado no smoke test." \
  "${BASE_URL}/admin/solicitacoes-filiacao/rejeitar/${APP2_ID}" \
  > /tmp/aorern-smoke-membership-rejeitar.html

php <<'PHP'
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();
$stmt = $pdo->prepare("SELECT status FROM membership_applications WHERE email = ? LIMIT 1");
$stmt->execute([getenv('APP2_EMAIL')]);
$status = (string) $stmt->fetchColumn();
if ($status !== 'rejeitada') {
    fwrite(STDERR, "Status esperado 'rejeitada' e recebido '{$status}'." . PHP_EOL);
    exit(1);
}
echo "OK rejeicao\n";
PHP

echo "[6/7] Verificando integridade dos vínculos..."
php <<'PHP'
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();
$stmt = $pdo->prepare("SELECT status_associativo FROM membership_applications WHERE email = ? LIMIT 1");
$stmt->execute([getenv('APP1_EMAIL')]);
$statusAssociativo = (string) $stmt->fetchColumn();
if ($statusAssociativo !== 'efetivo') {
    fwrite(STDERR, "Status associativo da solicitação aprovada não foi persistido como 'efetivo'." . PHP_EOL);
    exit(1);
}
echo "OK integridade\n";
PHP

echo "[7/7] Fluxo crítico de filiação validado com sucesso em ${BASE_URL}"
