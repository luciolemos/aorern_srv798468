<?php

namespace App\Database;

use App\Core\Database as Connection;
use PDO;
use RuntimeException;

class MigrationRunner
{
    private PDO $db;
    private string $migrationsPath;

    public function __construct(?string $migrationsPath = null)
    {
        $this->db = Connection::connect();
        $rootPath = dirname(__DIR__, 2);
        $this->migrationsPath = $migrationsPath ?? $rootPath . '/database/migrations';

        if (!is_dir($this->migrationsPath)) {
            throw new RuntimeException("Diretório de migrations não encontrado: {$this->migrationsPath}");
        }

        $this->ensureMigrationsTable();
    }

    public function migrate(): array
    {
        $files = $this->getMigrationFiles();
        $executed = $this->getExecutedMigrationsMap();
        $pending = array_filter($files, fn(array $migration) => !isset($executed[$migration['name']]));

        if (empty($pending)) {
            echo "Nenhuma migration pendente.\n";
            return [];
        }

        $batch = $this->getNextBatchNumber();
        $ran = [];

        foreach ($pending as $migration) {
            $this->requireMigrationFile($migration['path']);
            $instance = $this->instantiateMigration($migration['class']);

            if (!$instance->shouldRun('up')) {
                $this->logMigration($migration['name'], $batch);
                echo "⏭ Pendente ignorada (já aplicada): {$migration['name']}\n";
                continue;
            }

            $this->runMigrationInstance($instance, 'up');
            $this->logMigration($migration['name'], $batch);
            $ran[] = $migration['name'];
            echo "✔ Executada: {$migration['name']}\n";
        }

        return $ran;
    }

    public function rollback(): array
    {
        $batch = $this->getLastBatchNumber();
        if ($batch === 0) {
            echo "Nenhum batch disponível para rollback.\n";
            return [];
        }

        $files = $this->getMigrationFiles();
        $batchMigrations = $this->getMigrationsByBatch($batch);
        $rolledBack = [];

        foreach ($batchMigrations as $migrationName) {
            if (!isset($files[$migrationName])) {
                echo "⚠ Arquivo da migration {$migrationName} não encontrado.\n";
                continue;
            }

            $migration = $files[$migrationName];
            $this->requireMigrationFile($migration['path']);
            $instance = $this->instantiateMigration($migration['class']);
            $this->runMigrationInstance($instance, 'down');
            $this->removeMigrationLog($migrationName);
            $rolledBack[] = $migrationName;
            echo "↩ Rollback: {$migrationName}\n";
        }

        return $rolledBack;
    }

    public function status(): void
    {
        $files = $this->getMigrationFiles();
        $executed = $this->getExecutedMigrationsMap();

        printf("%-70s %-10s %-5s %-20s\n", 'Migration', 'Status', 'Batch', 'Executada em');
        foreach ($files as $migration) {
            if (isset($executed[$migration['name']])) {
                $meta = $executed[$migration['name']];
                printf(
                    "%-70s %-10s %-5d %-20s\n",
                    $migration['name'],
                    'OK',
                    $meta['batch'],
                    $meta['executed_at']
                );
            } else {
                printf("%-70s %-10s %-5s %-20s\n", $migration['name'], 'PENDENTE', '-', '-');
            }
        }
    }

    private function ensureMigrationsTable(): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT UNSIGNED NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $this->db->exec($sql);
    }

    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php');
        if ($files === false) {
            return [];
        }

        sort($files);
        $list = [];

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $list[$name] = [
                'name' => $name,
                'class' => 'App\\Database\\Migrations\\' . $name,
                'path' => $file,
            ];
        }

        return $list;
    }

    private function getExecutedMigrationsMap(): array
    {
        $stmt = $this->db->query("SELECT migration, batch, executed_at FROM migrations ORDER BY id ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];

        foreach ($rows as $row) {
            $map[$row['migration']] = [
                'batch' => (int) $row['batch'],
                'executed_at' => $row['executed_at'],
            ];
        }

        return $map;
    }

    private function getNextBatchNumber(): int
    {
        $stmt = $this->db->query("SELECT MAX(batch) FROM migrations");
        $current = (int) ($stmt->fetchColumn() ?: 0);
        return $current + 1;
    }

    private function getLastBatchNumber(): int
    {
        $stmt = $this->db->query("SELECT MAX(batch) FROM migrations");
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function getMigrationsByBatch(int $batch): array
    {
        $stmt = $this->db->prepare("SELECT migration FROM migrations WHERE batch = :batch ORDER BY id DESC");
        $stmt->execute(['batch' => $batch]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function logMigration(string $migration, int $batch): void
    {
        $stmt = $this->db->prepare("INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)");
        $stmt->execute([
            'migration' => $migration,
            'batch' => $batch,
        ]);
    }

    private function removeMigrationLog(string $migration): void
    {
        $stmt = $this->db->prepare("DELETE FROM migrations WHERE migration = :migration");
        $stmt->execute(['migration' => $migration]);
    }

    private function requireMigrationFile(string $path): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException("Arquivo de migration não encontrado: {$path}");
        }

        require_once $path;
    }

    private function instantiateMigration(string $className): Migration
    {
        if (!class_exists($className)) {
            throw new RuntimeException("Classe {$className} não encontrada após carregar o arquivo da migration.");
        }

        $migration = new $className();

        if (!$migration instanceof Migration) {
            throw new RuntimeException("Classe {$className} não estende App\\Database\\Migration.");
        }

        return $migration;
    }

    private function runMigrationInstance(Migration $migration, string $direction): void
    {
        if (!method_exists($migration, $direction)) {
            $class = get_class($migration);
            throw new RuntimeException("Método {$direction} não existe na migration {$class}.");
        }

        $migration->{$direction}();
    }
}
