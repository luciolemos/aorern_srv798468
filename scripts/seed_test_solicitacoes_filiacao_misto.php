<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$pdo = Database::connect();

$names = [
    'Alberto Cesar Monteiro',
    'Breno Augusto Figueiredo',
    'Claudio Henrique Maia',
    'Douglas Rafael Paiva',
    'Ernani Jose Tavares',
    'Flavio Cesar de Lucena',
    'Gilberto Nunes do Carmo',
    'Heitor Augusto Lopes',
    'Ivan de Araujo Nobre',
    'Jose Leonardo Barreto',
    'Kleiton Nascimento de Lima',
    'Luiz Roberto Dantas',
];

$cities = ['Natal'];
$ufs = ['RN'];
$turmas = ['Turma Potiguar', 'Turma Guararapes', 'Turma Serido', 'Turma Forte dos Reis'];
$armas = ['Infantaria'];
$militaryStatuses = ['Ex-aluno do NPOR'];
$motherNames = [
    'Maria Monteiro',
    'Ana Figueiredo',
    'Lucia Maia',
    'Claudia Paiva',
    'Helena Tavares',
    'Patricia Lucena',
    'Teresa do Carmo',
    'Sonia Lopes',
    'Lidia Nobre',
    'Marta Barreto',
    'Renata de Lima',
    'Carla Dantas',
];
$fatherNames = [
    'Jose Monteiro',
    'Paulo Figueiredo',
    'Ricardo Maia',
    'Alberto Paiva',
    'Marcos Tavares',
    'Roberto Lucena',
    'Gilson do Carmo',
    'Henrique Lopes',
    'Sergio Nobre',
    'Carlos Barreto',
    'Joao de Lima',
    'Fernando Dantas',
];

$stamp = date('YmdHis');
$avatar = 'assets/images/conscrito.png';
$documentos = json_encode([
    [
        'name' => 'certificado_npor.pdf',
        'path' => 'assets/docs/cancao_oficial_aore.pdf',
        'mime_type' => 'application/pdf',
        'size' => 3607,
    ]
], JSON_UNESCAPED_SLASHES);

$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare(
        "INSERT INTO membership_applications (
            nome_completo, nome_mae, nome_pai, username_desejado, email, password_hash, cpf, cam, rg,
            data_nascimento, telefone, cidade, uf, ano_npor, posto_graduacao, numero_militar, nome_guerra, turma_npor,
            arma_quadro, situacao_militar, avatar, documentos_json, observacoes,
            aceite_termo, status, status_associativo, observacoes_admin,
            aprovado_em, rejeitado_em, criado_em, atualizado_em
        ) VALUES (
            :nome_completo, :nome_mae, :nome_pai, :username_desejado, :email, :password_hash, :cpf, :cam, :rg,
            :data_nascimento, :telefone, :cidade, :uf, :ano_npor, :posto_graduacao, :numero_militar, :nome_guerra, :turma_npor,
            :arma_quadro, :situacao_militar, :avatar, :documentos_json, :observacoes,
            :aceite_termo, :status, :status_associativo, :observacoes_admin,
            :aprovado_em, :rejeitado_em, NOW(), NOW()
        )"
    );

    foreach ($names as $index => $name) {
        $sequence = $index + 1;
        $username = sprintf('solmix%04d', $sequence);
        $email = sprintf('%s@aorern.local', $username);
        $cpf = str_pad((string) (51000000000 + $sequence), 11, '0', STR_PAD_LEFT);
        $birthYear = 1970 + ($index % 18);
        $birthMonth = (($index % 12) + 1);
        $birthDay = (($index % 28) + 1);
        $anoNpor = (string) (2004 + $index);
        $numeroMilitar = sprintf('%02d', $sequence);
        $nomeGuerra = strtoupper((string) explode(' ', $name)[0]);
        $stmt->execute([
            ':nome_completo' => $name,
            ':nome_mae' => $motherNames[$index % count($motherNames)],
            ':nome_pai' => $fatherNames[$index % count($fatherNames)],
            ':username_desejado' => $username,
            ':email' => $email,
            ':password_hash' => password_hash('Temp#12345', PASSWORD_BCRYPT),
            ':cpf' => $cpf,
            ':cam' => sprintf('CAM-%s-%03d', date('Y'), $sequence),
            ':rg' => sprintf('20%08d/SSP-RN', $sequence),
            ':data_nascimento' => sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay),
            ':telefone' => '8497' . str_pad((string) (200000 + $sequence), 6, '0', STR_PAD_LEFT),
            ':cidade' => $cities[$index % count($cities)],
            ':uf' => $ufs[$index % count($ufs)],
            ':ano_npor' => $anoNpor,
            ':posto_graduacao' => 'Aspirante a Oficial',
            ':numero_militar' => $numeroMilitar,
            ':nome_guerra' => $nomeGuerra,
            ':turma_npor' => $turmas[$index % count($turmas)],
            ':arma_quadro' => $armas[$index % count($armas)],
            ':situacao_militar' => $militaryStatuses[$index % count($militaryStatuses)],
            ':avatar' => $avatar,
            ':documentos_json' => $documentos,
            ':observacoes' => 'Solicitação de teste gerada automaticamente para validação da fila mista de análise.',
            ':aceite_termo' => 1,
            ':status' => 'pendente',
            ':status_associativo' => 'efetivo',
            ':observacoes_admin' => null,
            ':aprovado_em' => null,
            ':rejeitado_em' => null,
        ]);
    }

    $pdo->commit();
    echo count($names) . " solicitações mistas geradas com sucesso.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Falha ao gerar solicitações mistas: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
