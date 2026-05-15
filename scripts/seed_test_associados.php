<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$pdo = Database::connect();
$totalAssociados = 50;

$firstNames = [
    'Carlos', 'Henrique', 'Marcos', 'Joao', 'Andre',
    'Roberto', 'Thiago', 'Fernando', 'Gustavo', 'Leonardo',
    'Mateus', 'Ricardo', 'Paulo', 'Daniel', 'Bruno',
    'Alexandre', 'Rafael', 'Victor', 'Luciano', 'Eduardo',
];
$middleNames = [
    'Eduardo', 'de Medeiros', 'Vinicius', 'Felipe', 'Luiz',
    'Cesar', 'de Araujo', 'Tarcisio', 'Henrique', 'Campos',
    'Augusto', 'Alexandre', 'Sergio', 'Rocha', 'Henrique',
];
$lastNames = [
    'Nascimento', 'Paiva', 'Albuquerque', 'Montenegro', 'Furtado',
    'de Brito', 'Lima', 'Peixoto', 'Bezerra', 'Dantas',
    'Tavares', 'Nogueira', 'Cavalcanti', 'de Souza', 'de Melo',
    'de Lima', 'Marinho', 'Fernandes', 'Barreto', 'de Queiroz',
];

$cities = ['Natal', 'Parnamirim', 'Macaiba', 'Ceara-Mirim', 'Sao Goncalo do Amarante', 'Mossoro'];
$assocStatuses = ['efetivo'];
$militaryStatuses = ['Oficial R/2', 'Reservista', 'Ex-aluno do NPOR'];

/**
 * @return string[]
 */
function fetchRandomUserPhotoUrls(int $count): array
{
    if ($count <= 0) {
        return [];
    }

    $photos = [];
    $attempts = 0;
    $maxAttempts = 5;

    while (count($photos) < $count && $attempts < $maxAttempts) {
        $attempts++;
        $remaining = $count - count($photos);
        $batch = max(10, min(100, $remaining * 2));
        $url = sprintf('https://randomuser.me/api/?results=%d&gender=male', $batch);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 8,
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\nUser-Agent: AORE-RN-Seed/1.0\r\n",
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if (!is_string($raw) || $raw === '') {
            continue;
        }

        $json = json_decode($raw, true);
        if (!is_array($json) || !isset($json['results']) || !is_array($json['results'])) {
            continue;
        }

        foreach ($json['results'] as $item) {
            $photo = trim((string) (($item['picture']['large'] ?? '')));
            if ($photo !== '' && str_contains($photo, '/portraits/men/')) {
                $photos[] = $photo;
                if (count($photos) >= $count) {
                    break;
                }
            }
        }
    }

    return array_slice($photos, 0, $count);
}

$randomUserPhotos = fetchRandomUserPhotoUrls($totalAssociados);

$pdo->beginTransaction();

try {
    $funcaoId = (int) $pdo->query("SELECT id FROM funcoes WHERE nome='Associado AORE/RN' LIMIT 1")->fetchColumn();
    if ($funcaoId <= 0) {
        $funcaoLegadaId = (int) $pdo->query("SELECT id FROM funcoes WHERE nome='Associado' LIMIT 1")->fetchColumn();
        if ($funcaoLegadaId > 0) {
            $stmtFunc = $pdo->prepare("UPDATE funcoes SET nome = ?, staff_id = ? WHERE id = ?");
            $stmtFunc->execute(['Associado AORE/RN', 'FUNC-AORE-011', $funcaoLegadaId]);
            $funcaoId = $funcaoLegadaId;
        } else {
            $stmtFunc = $pdo->prepare("INSERT INTO funcoes (staff_id, nome) VALUES (?, ?)");
            $stmtFunc->execute(['FUNC-AORE-011', 'Associado AORE/RN']);
            $funcaoId = (int) $pdo->lastInsertId();
        }
    }

    $pdo->exec("DELETE FROM pessoal");

    $stmt = $pdo->prepare(
        "INSERT INTO pessoal (
            staff_id, nome, cpf, nascimento, telefone, foto,
            funcao_id, obra_id, data_admissao, status, status_associativo, jornada, observacoes, criado_em
        ) VALUES (
            :staff_id, :nome, :cpf, :nascimento, :telefone, :foto,
            :funcao_id, :obra_id, :data_admissao, :status, :status_associativo, :jornada, :observacoes, NOW()
        )"
    );
    $stmtMembership = $pdo->prepare(
        "INSERT INTO membership_applications (
            nome_completo, username_desejado, email, password_hash, cpf,
            cam, rg, data_nascimento, telefone, cidade, uf, ano_npor, posto_graduacao,
            numero_militar, nome_guerra, turma_npor, arma_quadro, situacao_militar, avatar,
            documentos_json, observacoes, aceite_termo, status, status_associativo, pessoal_id,
            observacoes_admin, aprovado_em, criado_em, atualizado_em
        ) VALUES (
            :nome_completo, :username_desejado, :email, :password_hash, :cpf,
            :cam, :rg, :data_nascimento, :telefone, :cidade, :uf, :ano_npor, :posto_graduacao,
            :numero_militar, :nome_guerra, :turma_npor, :arma_quadro, :situacao_militar, :avatar,
            :documentos_json, :observacoes, :aceite_termo, :status, :status_associativo, :pessoal_id,
            :observacoes_admin, :aprovado_em, NOW(), NOW()
        )"
    );

    for ($index = 0; $index < $totalAssociados; $index++) {
        $name = sprintf(
            '%s %s %s',
            $firstNames[$index % count($firstNames)],
            $middleNames[$index % count($middleNames)],
            $lastNames[$index % count($lastNames)]
        );
        $sequence = $index + 1;
        $username = sprintf('assocseed%04d', $sequence);
        $email = $username . '@aorern.local';
        $cpf = str_pad((string) (31000000000 + $sequence), 11, '0', STR_PAD_LEFT);
        $birthYear = 1968 + ($index % 20);
        $birthMonth = (($index % 12) + 1);
        $birthDay = (($index % 28) + 1);
        $admissionDay = (($index % 28) + 1);
        $anoNpor = (string) (2009 + ($index % 10));
        $numeroMilitar = sprintf('%02d', ($sequence % 99) ?: 1);
        $staffId = sprintf('SE-%s%s', $anoNpor, $numeroMilitar);
        $nomeGuerra = strtoupper((string) explode(' ', $name)[0]);
        $turmaNpor = sprintf('Turma %s', 2009 + ($index % 10));
        $city = $cities[$index % count($cities)];
        $assocStatus = $assocStatuses[$index % count($assocStatuses)];
        $militaryStatus = $militaryStatuses[$index % count($militaryStatuses)];

        $seedAvatar = 'assets/images/conscrito.png';
        if (isset($randomUserPhotos[$index])) {
            $remotePhoto = trim((string) $randomUserPhotos[$index]);
            if ($remotePhoto !== '' && preg_match('#^https?://#i', $remotePhoto)) {
                $seedAvatar = $remotePhoto;
            }
        }

        $stmt->execute([
            ':staff_id' => $staffId,
            ':nome' => $name,
            ':cpf' => $cpf,
            ':nascimento' => sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay),
            ':telefone' => '8499' . str_pad((string) (100000 + $sequence), 6, '0', STR_PAD_LEFT),
            ':foto' => $seedAvatar,
            ':funcao_id' => $funcaoId,
            ':obra_id' => null,
            ':data_admissao' => sprintf('2026-03-%02d', $admissionDay),
            ':status' => 'Ativo',
            ':status_associativo' => $assocStatus,
            ':jornada' => null,
            ':observacoes' => sprintf(
                'Registro de teste gerado automaticamente. Cidade-base: %s. Condicao militar de referencia: %s.',
                $city,
                $militaryStatus
            ),
        ]);
        $pessoalId = (int) $pdo->lastInsertId();

        $stmtMembership->execute([
            ':nome_completo' => $name,
            ':username_desejado' => $username,
            ':email' => $email,
            ':password_hash' => password_hash('Temp#12345', PASSWORD_BCRYPT),
            ':cpf' => $cpf,
            ':cam' => sprintf('CAM-%s-%03d', date('Y'), $sequence),
            ':rg' => sprintf('30%08d/SSP-RN', $sequence),
            ':data_nascimento' => sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay),
            ':telefone' => '8499' . str_pad((string) (100000 + $sequence), 6, '0', STR_PAD_LEFT),
            ':cidade' => $city,
            ':uf' => 'RN',
            ':ano_npor' => $anoNpor,
            ':posto_graduacao' => 'Asp Of',
            ':numero_militar' => $numeroMilitar,
            ':nome_guerra' => $nomeGuerra,
            ':turma_npor' => $turmaNpor,
            ':arma_quadro' => 'Infantaria',
            ':situacao_militar' => $militaryStatus,
            ':avatar' => $seedAvatar,
            ':documentos_json' => null,
            ':observacoes' => 'Solicitação de referência gerada automaticamente para exposição no carômetro.',
            ':aceite_termo' => 1,
            ':status' => 'aprovada',
            ':status_associativo' => 'efetivo',
            ':pessoal_id' => $pessoalId,
            ':observacoes_admin' => 'Aprovada automaticamente via seed de associados.',
            ':aprovado_em' => date('Y-m-d H:i:s'),
        ]);
    }

    $pdo->commit();
    echo "{$totalAssociados} associados de teste gerados com sucesso.\n";
    echo "{$totalAssociados} solicitações aprovadas de referência vinculadas aos associados.\n";
    echo count($randomUserPhotos) . " foto(s) recebida(s) do RandomUser; fallback automático aplicado quando necessário.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Falha ao gerar associados de teste: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
