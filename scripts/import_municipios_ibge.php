#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Database;

require __DIR__ . '/../vendor/autoload.php';

const IBGE_ENDPOINT = 'https://servicodados.ibge.gov.br/api/v1/localidades/municipios';

function logMessage(string $message): void
{
    fwrite(STDOUT, '[' . date('Y-m-d H:i:s') . "] $message" . PHP_EOL);
}

try {
    logMessage('Iniciando importação de municípios do IBGE...');

    $response = @file_get_contents(IBGE_ENDPOINT);
    if ($response === false) {
        throw new RuntimeException('Não foi possível acessar o endpoint do IBGE.');
    }

    $payload = json_decode($response, true);
    if (!is_array($payload)) {
        throw new RuntimeException('Resposta do IBGE inválida.');
    }

    $pdo = Database::connect();

    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    $pdo->exec('TRUNCATE TABLE municipios_ibge');
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO municipios_ibge (codigo, nome, uf, uf_nome, regiao) VALUES (:codigo, :nome, :uf, :uf_nome, :regiao)'
    );

    $total = 0;
    foreach ($payload as $municipio) {
        $ufData = $municipio['microrregiao']['mesorregiao']['UF'] ?? null;
        $regiaoData = $ufData['regiao'] ?? null;

        if (!$ufData || !$regiaoData) {
            continue;
        }

        $stmt->execute([
            ':codigo' => (int) ($municipio['id'] ?? 0),
            ':nome' => $municipio['nome'] ?? '',
            ':uf' => $ufData['sigla'] ?? '',
            ':uf_nome' => $ufData['nome'] ?? '',
            ':regiao' => $regiaoData['nome'] ?? '',
        ]);

        $total++;
    }

    $pdo->commit();
    logMessage("Importação concluída com sucesso ({$total} municípios).");
    exit(0);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Erro durante importação: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
