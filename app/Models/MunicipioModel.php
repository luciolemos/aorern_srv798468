<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

class MunicipioModel extends Database
{
    protected string $table = 'municipios_ibge';
    private const IBGE_ENDPOINT = 'https://servicodados.ibge.gov.br/api/v1/localidades/municipios';
    private const EXPECTED_TOTAL = 5570;

    public function buscarPorCodigo(int $codigo): ?array
    {
        $stmt = $this->connect()->prepare("SELECT * FROM {$this->table} WHERE codigo = :codigo LIMIT 1");
        $stmt->execute([':codigo' => $codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function garantirPorCodigo(int $codigo): ?array
    {
        $local = $this->buscarPorCodigo($codigo);
        if ($local) {
            return $local;
        }

        $remoto = $this->buscarRemotoPorCodigo($codigo);
        if (!$remoto) {
            return null;
        }

        $this->upsertMunicipio($remoto);
        return $remoto;
    }

    public function buscarPorTermo(string $termo, ?string $uf = null, int $limit = 10): array
    {
        $this->ensureCatalogLoaded();

        $sql = "SELECT codigo, nome, uf, uf_nome FROM {$this->table} WHERE nome LIKE :termo";
        $params = [':termo' => '%' . $termo . '%'];

        if ($uf) {
            $sql .= " AND uf = :uf";
            $params[':uf'] = strtoupper($uf);
        }

        $sql .= " ORDER BY nome ASC LIMIT :limit";

        $stmt = $this->connect()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sugerirMunicipios(string $termo, int $limit = 10, ?string $uf = null): array
    {
        $this->ensureCatalogLoaded();

        $termo = trim($termo);
        if ($termo === '') {
            return [];
        }

        $limit = max(1, min($limit, 50));
        $resultado = $this->buscarPorTermo($termo, $uf, $limit);

        if (count($resultado) > 0 || $this->isCatalogComplete()) {
            return $resultado;
        }

        $faltantes = $limit - count($resultado);
        if ($faltantes <= 0 || mb_strlen($termo) < 3) {
            return $resultado;
        }

        $remotos = $this->buscarRemotoPorTermo($termo, $faltantes, $uf);
        foreach ($remotos as $municipio) {
            $this->upsertMunicipio($municipio);
        }

        return array_slice(array_merge($resultado, $remotos), 0, $limit);
    }

    public function listarPorUf(string $uf, int $limit = 150): array
    {
        $this->ensureCatalogLoaded();

        $stmt = $this->connect()->prepare("SELECT codigo, nome, uf FROM {$this->table} WHERE uf = :uf ORDER BY nome ASC LIMIT :limit");
        $stmt->bindValue(':uf', strtoupper($uf));
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existe(int $codigo): bool
    {
        $stmt = $this->connect()->prepare("SELECT 1 FROM {$this->table} WHERE codigo = :codigo LIMIT 1");
        $stmt->execute([':codigo' => $codigo]);
        return (bool) $stmt->fetchColumn();
    }

    private function buscarRemotoPorCodigo(int $codigo): ?array
    {
        if ($codigo <= 0) {
            return null;
        }

        $url = self::IBGE_ENDPOINT . '/' . $codigo;
        $payload = $this->httpGetJson($url);

        if (!$payload) {
            return null;
        }

        if (isset($payload['id'])) {
            return $this->normalizarMunicipio($payload);
        }

        if (isset($payload[0]) && is_array($payload[0])) {
            return $this->normalizarMunicipio($payload[0]);
        }

        return null;
    }

    private function buscarRemotoPorTermo(string $termo, int $limit = 10, ?string $uf = null): array
    {
        $url = self::IBGE_ENDPOINT . '?nome=' . rawurlencode($termo);
        $payload = $this->httpGetJson($url);
        if (!$payload) {
            return [];
        }

        $resultado = [];
        foreach ($payload as $item) {
            $normalizado = $this->normalizarMunicipio($item);
            if (!$normalizado) {
                continue;
            }

            if ($uf && strtoupper($normalizado['uf']) !== strtoupper($uf)) {
                continue;
            }

            $resultado[$normalizado['codigo']] = $normalizado;
            if (count($resultado) >= $limit) {
                break;
            }
        }

        return array_values($resultado);
    }

    private function normalizarMunicipio(array $dados): ?array
    {
        $ufData = $dados['microrregiao']['mesorregiao']['UF'] ?? null;
        $regiaoData = $ufData['regiao'] ?? null;

        if (!$ufData || !$regiaoData || empty($dados['id'])) {
            return null;
        }

        return [
            'codigo' => (int) $dados['id'],
            'nome' => $dados['nome'] ?? '',
            'uf' => $ufData['sigla'] ?? '',
            'uf_nome' => $ufData['nome'] ?? '',
            'regiao' => $regiaoData['nome'] ?? '',
        ];
    }

    private function upsertMunicipio(array $municipio): void
    {
        $sql = "INSERT INTO {$this->table} (codigo, nome, uf, uf_nome, regiao) VALUES (:codigo, :nome, :uf, :uf_nome, :regiao)
                ON DUPLICATE KEY UPDATE nome = VALUES(nome), uf = VALUES(uf), uf_nome = VALUES(uf_nome), regiao = VALUES(regiao)";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([
            ':codigo' => $municipio['codigo'],
            ':nome' => $municipio['nome'],
            ':uf' => $municipio['uf'],
            ':uf_nome' => $municipio['uf_nome'],
            ':regiao' => $municipio['regiao'],
        ]);
    }

    private function httpGetJson(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 8,
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\n",
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return [];
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function ensureCatalogLoaded(): void
    {
        if ($this->isCatalogComplete()) {
            return;
        }

        $payload = $this->httpGetJson(self::IBGE_ENDPOINT);
        if (!$payload) {
            return;
        }

        $pdo = $this->connect();
        $sql = "INSERT INTO {$this->table} (codigo, nome, uf, uf_nome, regiao) VALUES (:codigo, :nome, :uf, :uf_nome, :regiao)
                ON DUPLICATE KEY UPDATE nome = VALUES(nome), uf = VALUES(uf), uf_nome = VALUES(uf_nome), regiao = VALUES(regiao)";

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare($sql);
            foreach ($payload as $municipio) {
                $normalizado = $this->normalizarMunicipio($municipio);
                if (!$normalizado) {
                    continue;
                }

                $stmt->execute([
                    ':codigo' => $normalizado['codigo'],
                    ':nome' => $normalizado['nome'],
                    ':uf' => $normalizado['uf'],
                    ':uf_nome' => $normalizado['uf_nome'],
                    ':regiao' => $normalizado['regiao'],
                ]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Erro ao sincronizar municípios do IBGE: ' . $e->getMessage());
        }
    }

    private function isCatalogComplete(): bool
    {
        $stmt = $this->connect()->query("SELECT COUNT(*) FROM {$this->table}");
        $total = (int) $stmt->fetchColumn();
        return $total >= self::EXPECTED_TOTAL;
    }
}
