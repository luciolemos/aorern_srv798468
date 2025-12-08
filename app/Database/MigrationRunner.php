<?php

namespace App\Database;

use App\Core\Database;
use PDO;

class MigrationRunner
{
    private PDO $db;
    private string $migrationsPath;

    public function __construct()
    {
        $db = new Database();
        $this->db = $db->pdo ?? Database::connect();
        $this->migrationsPath = dirname(__DIR__, 2) . '/database/migrations';
        $this->createMigrationsTable();
    }

    /**
     * Cria a tabela de controle de migrations
     */
    private function createMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->exec($sql);
    }

    /**
     * Executa todas as migrations pendentes
     */
    public function migrate(): array
    {
        $executed = [];
        $pending = $this->getPendingMigrations();

        if (empty($pending)) {
            echo "✅ Nenhuma migration pendente.\n";
            return $executed;
        }

        $batch = $this->getNextBatchNumber();

        foreach ($pending as $migration) {
            try {
                echo "⏳ Executando: {$migration}\n";
                
                $instance = $this->loadMigration($migration);
                $instance->up();
                
                $this->markAsExecuted($migration, $batch);
                
                echo "✅ Concluída: {$migration}\n";
                $executed[] = $migration;
            } catch (\Exception $e) {
                echo "❌ Erro em {$migration}: " . $e->getMessage() . "\n";
                break;
            }
        }

        return $executed;
    }

    /**
     * Reverte o último batch de migrations
     */
    public function rollback(): array
    {
        $rolledBack = [];
        $lastBatch = $this->getLastBatch();

        if (empty($lastBatch)) {
            echo "✅ Nenhuma migration para reverter.\n";
            return $rolledBack;
        }

        foreach (array_reverse($lastBatch) as $migration) {
            try {
                echo "⏳ Revertendo: {$migration}\n";
                
                $instance = $this->loadMigration($migration);
                $instance->down();
                
                $this->markAsReverted($migration);
                
                echo "✅ Revertida: {$migration}\n";
                $rolledBack[] = $migration;
            } catch (\Exception $e) {
                echo "❌ Erro ao reverter {$migration}: " . $e->getMessage() . "\n";
                break;
            }
        }

        return $rolledBack;
    }

    /**
     * Lista migrations executadas
     */
    public function status(): void
    {
        $all = $this->getAllMigrationFiles();
        $executed = $this->getExecutedMigrations();

        echo "\n📋 Status das Migrations:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-60s %s\n", "Migration", "Status");
        echo str_repeat("-", 80) . "\n";

        foreach ($all as $migration) {
            $status = in_array($migration, $executed) ? "✅ Executada" : "⏸️  Pendente";
            printf("%-60s %s\n", $migration, $status);
        }

        echo str_repeat("-", 80) . "\n";
        echo "Total: " . count($all) . " | Executadas: " . count($executed) . " | Pendentes: " . (count($all) - count($executed)) . "\n\n";
    }

    /**
     * Obtém migrations pendentes
     */
    private function getPendingMigrations(): array
    {
        $all = $this->getAllMigrationFiles();
        $executed = $this->getExecutedMigrations();
        
        return array_diff($all, $executed);
    }

    /**
     * Obtém todas as migrations do diretório
     */
    private function getAllMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = scandir($this->migrationsPath);
        $migrations = [];

        foreach ($files as $file) {
            if (preg_match('/^Migration_.*\.php$/', $file)) {
                $migrations[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        sort($migrations);
        return $migrations;
    }

    /**
     * Obtém migrations já executadas
     */
    private function getExecutedMigrations(): array
    {
        $stmt = $this->db->query("SELECT migration FROM migrations ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Obtém último batch
     */
    private function getLastBatch(): array
    {
        $stmt = $this->db->query("SELECT MAX(batch) as last_batch FROM migrations");
        $lastBatch = $stmt->fetchColumn();

        if (!$lastBatch) {
            return [];
        }

        $stmt = $this->db->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY id");
        $stmt->execute([$lastBatch]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Próximo número de batch
     */
    private function getNextBatchNumber(): int
    {
        $stmt = $this->db->query("SELECT MAX(batch) as last_batch FROM migrations");
        $lastBatch = $stmt->fetchColumn();
        return ($lastBatch ?? 0) + 1;
    }

    /**
     * Marca migration como executada
     */
    private function markAsExecuted(string $migration, int $batch): void
    {
        $stmt = $this->db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migration, $batch]);
    }

    /**
     * Marca migration como revertida (remove do registro)
     */
    private function markAsReverted(string $migration): void
    {
        $stmt = $this->db->prepare("DELETE FROM migrations WHERE migration = ?");
        $stmt->execute([$migration]);
    }

    /**
     * Carrega uma migration
     */
    private function loadMigration(string $name): Migration
    {
        $file = $this->migrationsPath . '/' . $name . '.php';
        
        if (!file_exists($file)) {
            throw new \Exception("Migration file not found: {$file}");
        }

        require_once $file;
        
        $className = 'App\\Database\\Migrations\\' . $name;
        
        if (!class_exists($className)) {
            throw new \Exception("Migration class not found: {$className}");
        }

        return new $className();
    }
}
