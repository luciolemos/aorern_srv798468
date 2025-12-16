<?php

namespace App\Database;

use App\Core\Database as Connection;
use PDO;

abstract class Migration
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Connection::connect();
    }

    abstract public function up(): void;

    abstract public function down(): void;

    public function shouldRun(string $direction): bool
    {
        return true;
    }

    protected function execute(string $sql): void
    {
        $this->db->exec($sql);
    }

    protected function tableExists(string $table): bool
    {
        $query = "SELECT COUNT(1) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['table' => $table]);

        return (bool) $stmt->fetchColumn();
    }

    protected function columnExists(string $table, string $column): bool
    {
        $query = "SELECT COUNT(1) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'table' => $table,
            'column' => $column,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    protected function indexExists(string $table, string $indexName): bool
    {
        $query = "SELECT COUNT(1) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND INDEX_NAME = :index";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'table' => $table,
            'index' => $indexName,
        ]);

        return (bool) $stmt->fetchColumn();
    }
}
