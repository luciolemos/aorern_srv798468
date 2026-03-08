<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$pdo = Database::connect();

$names = [
    'Augusto Cesar de Oliveira',
    'Bruno Ricardo Tavares',
    'Caio Henrique Medeiros',
    'Diego Alexandre Nunes',
    'Eduardo Luiz Fernandes',
    'Fabio Roberto de Carvalho',
    'Geraldo Henrique Paiva',
    'Helio Marcio Bezerra',
    'Igor Vinicius Freitas',
    'Julio Cesar de Melo',
    'Kleber Augusto Dantas',
    'Leandro Jose Barreto',
];

$cities = ['Natal'];
$ufs = ['RN'];
$turmas = ['Turma Guararapes', 'Turma Potengi', 'Turma Felipe Camarao', 'Turma Serido'];
$armas = ['Infantaria'];
$militaryStatuses = ['Ex-aluno do NPOR'];
$motherNames = [
    'Maria de Oliveira',
    'Ana Tavares',
    'Lucia Medeiros',
    'Claudia Nunes',
    'Helena Fernandes',
    'Patricia Carvalho',
    'Teresa Paiva',
    'Sonia Bezerra',
    'Lidia Freitas',
    'Marta de Melo',
    'Renata Dantas',
    'Carla Barreto',
];
$fatherNames = [
    'Jose de Oliveira',
    'Paulo Tavares',
    'Ricardo Medeiros',
    'Alberto Nunes',
    'Marcos Fernandes',
    'Roberto Carvalho',
    'Gilson Paiva',
    'Henrique Bezerra',
    'Sergio Freitas',
    'Carlos de Melo',
    'Joao Dantas',
    'Fernando Barreto',
];

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
            aceite_termo, status, status_associativo, observacoes_admin, criado_em, atualizado_em
        ) VALUES (
            :nome_completo, :nome_mae, :nome_pai, :username_desejado, :email, :password_hash, :cpf, :cam, :rg,
            :data_nascimento, :telefone, :cidade, :uf, :ano_npor, :posto_graduacao, :numero_militar, :nome_guerra, :turma_npor,
            :arma_quadro, :situacao_militar, :avatar, :documentos_json, :observacoes,
            :aceite_termo, :status, :status_associativo, :observacoes_admin, NOW(), NOW()
        )"
    );

    foreach ($names as $index => $name) {
        $sequence = $index + 1;
        $username = sprintf('solpend%04d', $sequence);
        $email = sprintf('%s@aorern.local', $username);
        $cpf = str_pad((string) (41000000000 + $sequence), 11, '0', STR_PAD_LEFT);
        $birthYear = 1972 + ($index % 15);
        $birthMonth = (($index % 12) + 1);
        $birthDay = (($index % 28) + 1);
        $anoNpor = (string) (2005 + $index);
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
            ':rg' => sprintf('10%08d/SSP-RN', $sequence),
            ':data_nascimento' => sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay),
            ':telefone' => '8498' . str_pad((string) (100000 + $sequence), 6, '0', STR_PAD_LEFT),
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
            ':observacoes' => 'Solicitação de teste gerada automaticamente para validação da fila de análise.',
            ':aceite_termo' => 1,
            ':status' => 'pendente',
            ':status_associativo' => 'efetivo',
            ':observacoes_admin' => null,
        ]);
    }

    $pdo->commit();
    echo count($names) . " solicitações pendentes geradas com sucesso.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Falha ao gerar solicitações pendentes: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
