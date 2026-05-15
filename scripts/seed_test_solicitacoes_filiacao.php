<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$pdo = Database::connect();

$totalRequests = 50;
$firstNames = [
    'Augusto', 'Bruno', 'Caio', 'Diego', 'Eduardo',
    'Fabio', 'Geraldo', 'Helio', 'Igor', 'Julio',
    'Kleber', 'Leandro', 'Marcelo', 'Nelson', 'Otavio',
    'Paulo', 'Rafael', 'Sergio', 'Thiago', 'Walter',
];
$middleNames = [
    'Cesar', 'Ricardo', 'Henrique', 'Alexandre', 'Luiz',
    'Roberto', 'Vinicius', 'Marcio', 'Andre', 'Nunes',
];
$lastNames = [
    'de Oliveira', 'Tavares', 'Medeiros', 'Fernandes', 'de Carvalho',
    'Paiva', 'Bezerra', 'Freitas', 'de Melo', 'Dantas',
    'Barreto', 'Rocha', 'Monteiro', 'Barros', 'Almeida',
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

$randomUserPhotos = fetchRandomUserPhotoUrls($totalRequests);

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

    for ($index = 0; $index < $totalRequests; $index++) {
        $name = sprintf(
            '%s %s %s',
            $firstNames[$index % count($firstNames)],
            $middleNames[$index % count($middleNames)],
            $lastNames[$index % count($lastNames)]
        );
        $sequence = $index + 1;
        $username = sprintf('solpend%04d', $sequence);
        $email = sprintf('%s@aorern.local', $username);
        $cpf = str_pad((string) (41000000000 + $sequence), 11, '0', STR_PAD_LEFT);
        $birthYear = 1972 + ($index % 15);
        $birthMonth = (($index % 12) + 1);
        $birthDay = (($index % 28) + 1);
        $anoNpor = (string) (2009 + intdiv($index, 5));
        $numeroMilitar = sprintf('%02d', $sequence);
        $nomeGuerra = strtoupper((string) explode(' ', $name)[0]);
        if ($index < 30) {
            $status = 'pendente';
        } elseif ($index < 40) {
            $status = 'complementacao';
        } else {
            $status = 'rejeitada';
        }

        $seedAvatar = $avatar;
        if (isset($randomUserPhotos[$index])) {
            $remotePhoto = trim((string) $randomUserPhotos[$index]);
            if ($remotePhoto !== '' && preg_match('#^https?://#i', $remotePhoto)) {
                $seedAvatar = $remotePhoto;
            }
        }

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
            ':avatar' => $seedAvatar,
            ':documentos_json' => $documentos,
            ':observacoes' => 'Solicitação de teste gerada automaticamente para validação da fila de análise.',
            ':aceite_termo' => 1,
            ':status' => $status,
            ':status_associativo' => 'efetivo',
            ':observacoes_admin' => null,
        ]);
    }

    $pdo->commit();
    echo "{$totalRequests} solicitações geradas com sucesso (30 pendente, 10 complementacao, 10 rejeitada).\n";
    echo count($randomUserPhotos) . " foto(s) recebida(s) do RandomUser; fallback automático aplicado quando necessário.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Falha ao gerar solicitações pendentes: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
