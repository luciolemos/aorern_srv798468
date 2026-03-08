#!/usr/bin/env bash
set -euo pipefail

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/lib_test.sh"
testlib_init

BASE_URL="${1:-https://127.0.0.1/aorern}"

TMP_AVATAR="/tmp/aorern-smoke-avatar.png"
TMP_DOC="/tmp/aorern-smoke-doc.pdf"
COOKIE_ASSOC="/tmp/aorern-smoke-assoc.cookie"
COOKIE_ADMIN="/tmp/aorern-smoke-admin.cookie"

cp "$ROOT_DIR/public/assets/images/aore1.png" "$TMP_AVATAR"
cp "$ROOT_DIR/public/assets/docs/cancao_oficial_aore.pdf" "$TMP_DOC"

STAMP="$(date +%s)"
SUFFIX4="${STAMP: -4}"
PUB_USER="smoke${SUFFIX4}"
PUB_EMAIL="${PUB_USER}@aorern.local"
TEMP_ADMIN_EMAIL="smoke.admin.${STAMP}@aorern.local"
TEMP_ASSOC_EMAIL="smoke.associado.${STAMP}@aorern.local"
TEMP_ADMIN_USER="smokeadmin${STAMP}"
TEMP_ASSOC_USER="smokeassoc${STAMP}"
TEMP_ASSOC_CPF="$((20000000000 + (STAMP % 1000000000)))"

export PUB_EMAIL TEMP_ADMIN_EMAIL TEMP_ASSOC_EMAIL TEMP_ADMIN_USER TEMP_ASSOC_USER TEMP_ASSOC_CPF STAMP

cleanup() {
php <<'PHP' >/dev/null
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();
$pdo->prepare("DELETE FROM membership_applications WHERE email IN (?, ?, ?)")->execute([
    getenv('PUB_EMAIL'),
    getenv('TEMP_ASSOC_EMAIL'),
    getenv('TEMP_ADMIN_EMAIL'),
]);
$pdo->prepare("DELETE FROM users WHERE email IN (?, ?)")->execute([
    getenv('TEMP_ADMIN_EMAIL'),
    getenv('TEMP_ASSOC_EMAIL'),
]);
$pdo->prepare("DELETE FROM pessoal WHERE cpf = ?")->execute([
    getenv('TEMP_ASSOC_CPF'),
]);
PHP
}

trap cleanup EXIT

echo "[0/4] Provisionando usuários temporários do smoke test..."
php <<'PHP'
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();
$pdo->beginTransaction();
try {
    $funcaoId = (int) $pdo->query("SELECT id FROM funcoes WHERE nome='Associado AORE/RN' LIMIT 1")->fetchColumn();
    if (!$funcaoId) {
        $funcaoLegadaId = (int) $pdo->query("SELECT id FROM funcoes WHERE nome='Associado' LIMIT 1")->fetchColumn();
        if ($funcaoLegadaId) {
            $stmt = $pdo->prepare("UPDATE funcoes SET nome = ?, staff_id = ? WHERE id = ?");
            $stmt->execute(['Associado AORE/RN', 'FUNC-AORE-011', $funcaoLegadaId]);
            $funcaoId = $funcaoLegadaId;
        } else {
            $stmt = $pdo->prepare("INSERT INTO funcoes (staff_id,nome) VALUES (?,?)");
            $stmt->execute(['FUNC-AORE-011', 'Associado AORE/RN']);
            $funcaoId = (int) $pdo->lastInsertId();
        }
    }

    $hash = password_hash('Temp#12345', PASSWORD_BCRYPT);
    $stmtUser = $pdo->prepare("INSERT INTO users (username,email,password,avatar,role,status,ativo,created_at,updated_at) VALUES (?,?,?,?,?,?,?,NOW(),NOW())");
    $stmtUser->execute([getenv('TEMP_ADMIN_USER'), getenv('TEMP_ADMIN_EMAIL'), $hash, null, 'admin', 'ativo', 1]);
    $stmtUser->execute([getenv('TEMP_ASSOC_USER'), getenv('TEMP_ASSOC_EMAIL'), $hash, null, 'usuario', 'ativo', 1]);
    $tempAssocUserId = (int) $pdo->lastInsertId();

    $stmtPessoal = $pdo->prepare("INSERT INTO pessoal (staff_id,nome,cpf,nascimento,telefone,foto,user_id,funcao_id,obra_id,data_admissao,status,status_associativo,jornada,observacoes,criado_em) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
    $stmtPessoal->execute([
        'ASSOC-SMOKE-' . getenv('STAMP'),
        'Associado Smoke',
        getenv('TEMP_ASSOC_CPF'),
        '1990-01-02',
        '84999990000',
        null,
        $tempAssocUserId,
        $funcaoId,
        null,
        date('Y-m-d'),
        'Ativo',
        'provisorio',
        null,
        'Registro temporário para smoke test',
    ]);
    $pessoalId = (int) $pdo->lastInsertId();

    $docs = json_encode([[
        'name' => 'certificado.pdf',
        'path' => 'assets/docs/cancao_oficial_aore.pdf',
        'mime_type' => 'application/pdf',
        'size' => 3607,
    ]], JSON_UNESCAPED_SLASHES);

    $stmtApp = $pdo->prepare("INSERT INTO membership_applications (nome_completo,username_desejado,email,password_hash,cpf,data_nascimento,telefone,cidade,ano_npor,turma_npor,arma_quadro,situacao_militar,avatar,documentos_json,observacoes,aceite_termo,status,status_associativo,user_id,pessoal_id,observacoes_admin,aprovado_em,criado_em,atualizado_em) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())");
    $stmtApp->execute([
        'Associado Smoke',
        getenv('TEMP_ASSOC_USER'),
        getenv('TEMP_ASSOC_EMAIL'),
        $hash,
        getenv('TEMP_ASSOC_CPF'),
        '1990-01-02',
        '84999990000',
        'Natal',
        '2012',
        'Turma Smoke',
        'Infantaria',
        'Oficial R/2',
        null,
        $docs,
        'Solicitação temporária para smoke test',
        1,
        'aprovada',
        'provisorio',
        $tempAssocUserId,
        $pessoalId,
        'Sem pendências',
        date('Y-m-d H:i:s'),
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

echo "[1/4] Enviando solicitação pública de filiação..."
curl -k -s \
  -F "username=${PUB_USER}" \
  -F "nome=Smoke Teste Associado" \
  -F "email=${PUB_EMAIL}" \
  -F "cpf=12345678901" \
  -F "cam=CAM123456" \
  -F "rg=10102976455/SSP-RN" \
  -F "telefone=84999990000" \
  -F "posto_graduacao=Aspirante a Oficial" \
  -F "numero_militar=10" \
  -F "nome_guerra=SMOKE" \
  -F "ano_npor=2014" \
  -F "turma_npor=Turma Smoke" \
  -F "arma_quadro=Infantaria" \
  -F "situacao_militar=Reservista" \
  -F "uf=RN" \
  -F "cidade=Natal" \
  -F "data_nascimento=1990-01-01" \
  -F "avatar=@${TMP_AVATAR};type=image/png" \
  -F "documentos[]=@${TMP_DOC};type=application/pdf" \
  -F "observacoes=Smoke test automatizado" \
  -F "password=Temp#12345" \
  -F "password_confirm=Temp#12345" \
  -F "aceite_termo=1" \
  "${BASE_URL}/register/store" > /tmp/aorern-smoke-register.html

php <<'PHP'
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();
$stmt = $pdo->prepare("SELECT avatar, documentos_json, status FROM membership_applications WHERE email = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([getenv('PUB_EMAIL')]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row || empty($row['avatar']) || empty($row['documentos_json']) || $row['status'] !== 'pendente') {
    fwrite(STDERR, "Falha no cadastro público de filiação.\n");
    exit(1);
}
echo "OK cadastro público\n";
PHP

echo "[2/4] Validando área do associado..."
curl -k -s -D /tmp/aorern-smoke-assoc-login.headers -o /tmp/aorern-smoke-assoc.html \
  -c "$COOKIE_ASSOC" -b "$COOKIE_ASSOC" \
  --data-urlencode "username=${TEMP_ASSOC_USER}" \
  --data-urlencode "password=Temp#12345" \
  "${BASE_URL}/login/authenticate-admin"

if ! rg -q "Location: .*/associado" /tmp/aorern-smoke-assoc-login.headers; then
  echo "Falha no login do associado: redirecionamento para /associado não detectado." >&2
  exit 1
fi
echo "OK área do associado"

echo "[3/4] Validando edição admin de associado..."
curl -k -s -c "$COOKIE_ADMIN" -b "$COOKIE_ADMIN" \
  --data-urlencode "username=${TEMP_ADMIN_USER}" \
  --data-urlencode "password=Temp#12345" \
  "${BASE_URL}/login/authenticate-admin" > /tmp/aorern-smoke-admin-login.html

PESSOAL_ID="$(db_value "SELECT id FROM pessoal WHERE cpf = ? LIMIT 1" "[\"${TEMP_ASSOC_CPF}\"]")"
FUNCAO_ID="$(db_value "SELECT id FROM funcoes WHERE nome='Associado AORE/RN' LIMIT 1")"
assert_not_empty "$PESSOAL_ID" "Falha ao localizar associado temporário no smoke test."
assert_not_empty "$FUNCAO_ID" "Falha ao localizar função 'Associado AORE/RN' no smoke test."

curl -k -s -b "$COOKIE_ADMIN" "${BASE_URL}/admin/pessoal/editar/${PESSOAL_ID}" > /tmp/aorern-smoke-admin-edit.html
ADMIN_TOKEN="$(extract_csrf /tmp/aorern-smoke-admin-edit.html)"
assert_not_empty "$ADMIN_TOKEN" "Falha ao extrair token CSRF de edição do associado."

curl -k -s -b "$COOKIE_ADMIN" -c "$COOKIE_ADMIN" \
  -F "csrf_token=${ADMIN_TOKEN}" \
  -F "staff_id=ASSOC-SMOKE-${STAMP}" \
  -F "nome=Associado Smoke Admin" \
  -F "cpf=${TEMP_ASSOC_CPF}" \
  -F "nascimento=1990-01-02" \
  -F "telefone=84999990000" \
  -F "funcao_id=${FUNCAO_ID}" \
  -F "data_admissao=2026-03-03" \
  -F "status=Ativo" \
  -F "observacoes=Smoke admin" \
  -F "foto=@${TMP_AVATAR};type=image/png" \
  "${BASE_URL}/admin/pessoal/atualizar/${PESSOAL_ID}" > /tmp/aorern-smoke-admin-save.html

echo "OK edição admin"

echo "[4/4] Verificação final..."
php <<'PHP'
<?php
require getenv('AORERN_ROOT') . '/vendor/autoload.php';
$pdo = \App\Core\Database::connect();
$stmt = $pdo->prepare("SELECT foto, nome FROM pessoal WHERE cpf = ?");
$stmt->execute([getenv('TEMP_ASSOC_CPF')]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row || empty($row['foto']) || $row['nome'] !== 'Associado Smoke Admin') {
    fwrite(STDERR, "Falha na atualização final do associado de teste.\n");
    exit(1);
}
echo "OK verificação final\n";
PHP

echo "Smoke test concluído com sucesso para ${BASE_URL}"
