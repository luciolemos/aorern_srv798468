<?php

namespace App\Database;

use App\Core\Database;
use PDO;

abstract class Migration
{
    protected PDO $db;

    public function __construct()
    {
        $db = new Database();
        $this->db = $db->pdo ?? Database::connect();
    }

    /**
     * Executa a migration (adiciona/modifica estrutura)
     */
    abstract public function up(): void;

    /**
     * Reverte a migration (desfaz alterações)
     */
    abstract public function down(): void;

    /**
     * Retorna o nome da migration
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * Executa uma query SQL
     */
    protected function execute(string $sql): void
    {
        $this->db->exec($sql);
    }

    /**
     * Verifica se uma tabela existe
     */
    protected function tableExists(string $table): bool
    {
        $stmt = $this->db->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Verifica se uma coluna existe em uma tabela
     */
    protected function columnExists(string $table, string $column): bool
    {
        $stmt = $this->db->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
        $stmt->execute([$column]);
        return $stmt->rowCount() > 0;
    }
}
