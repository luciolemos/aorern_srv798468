<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class DigitalCardModel
{
    private PDO $db;
    private string $table = 'digital_cards';
    private bool $tableExists;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->tableExists = $this->detectTable();
    }

    public function isAvailable(): bool
    {
        return $this->tableExists;
    }

    public function buscarAtivaPorPessoal(int $pessoalId): ?array
    {
        if (!$this->tableExists || $pessoalId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE pessoal_id = :pessoal_id AND status = 'ativa'
             ORDER BY id DESC
             LIMIT 1"
        );
        $stmt->execute([':pessoal_id' => $pessoalId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarUltimaPorPessoal(int $pessoalId): ?array
    {
        if (!$this->tableExists || $pessoalId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE pessoal_id = :pessoal_id
             ORDER BY id DESC
             LIMIT 1"
        );
        $stmt->execute([':pessoal_id' => $pessoalId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function revogarAtivaPorPessoal(int $pessoalId, string $motivo = 'Revogada pela administração'): bool
    {
        if (!$this->tableExists || $pessoalId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET status = 'revogada',
                 revogada_em = NOW(),
                 motivo_revogacao = :motivo,
                 atualizado_em = NOW()
             WHERE pessoal_id = :pessoal_id AND status = 'ativa'"
        );

        return $stmt->execute([
            ':pessoal_id' => $pessoalId,
            ':motivo' => trim($motivo) !== '' ? $motivo : 'Revogada pela administração',
        ]);
    }

    public function emitir(int $pessoalId, string $cardCode, string $token, array $snapshot, ?string $validadeAte = null): bool
    {
        if (!$this->tableExists || $pessoalId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (
                pessoal_id, card_code, token, status, emitida_em, validade_ate, snapshot_json, criado_em, atualizado_em
            ) VALUES (
                :pessoal_id, :card_code, :token, 'ativa', NOW(), :validade_ate, :snapshot_json, NOW(), NOW()
            )"
        );

        return $stmt->execute([
            ':pessoal_id' => $pessoalId,
            ':card_code' => $cardCode,
            ':token' => $token,
            ':validade_ate' => $validadeAte,
            ':snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function buscarPorToken(string $token): ?array
    {
        if (!$this->tableExists || trim($token) === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT dc.*, p.nome AS associado_nome, p.staff_id AS associado_staff_id, p.foto AS associado_foto
             FROM {$this->table} dc
             LEFT JOIN pessoal p ON p.id = dc.pessoal_id
             WHERE dc.token = :token
             LIMIT 1"
        );
        $stmt->execute([':token' => trim($token)]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function detectTable(): bool
    {
        $stmt = $this->db->prepare('SHOW TABLES LIKE :table_name');
        $stmt->execute([':table_name' => $this->table]);
        return (bool) $stmt->fetchColumn();
    }
}

