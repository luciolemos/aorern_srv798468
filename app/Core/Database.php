<?php

namespace App\Core;

use PDO;
use PDOException;
use Dotenv\Dotenv;
use RuntimeException;

class Database {
    private static $instance;
    protected $pdo;

    public function __construct() {
        $this->pdo = self::connect();
    }

    public static function connect(): PDO {
        if (!self::$instance) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->safeLoad(); // safeLoad para evitar conflito em CLI

            $host = $_ENV['DB_HOST'];
            $dbname = $_ENV['DB_NAME'];
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];

            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $user, $pass);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                self::logConnectionError($e, $host, $dbname);
                throw new RuntimeException('Falha ao conectar ao banco de dados.', 0, $e);
            }
        }

        return self::$instance;
    }

    private static function logConnectionError(PDOException $e, string $host, string $dbname): void
    {
        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/' . date('Y-m-d') . '-errors.log';
        $message = sprintf(
            "[%s] Database connection failed [%s/%s]: %s\n",
            date('Y-m-d H:i:s'),
            $host,
            $dbname,
            $e->getMessage()
        );

        error_log($message, 3, $logFile);
    }

    // 🔁 Reutilizáveis CRUD

    /**
     * Retorna todos os registros de uma tabela
     * 
     * @param string $table Nome da tabela
     * @param array $columns Colunas a retornar (padrão: todas)
     * @param string $orderBy Ordenação (ex: "created_at DESC")
     * @return array
     */
    public function all(string $table, array $columns = ['*'], string $orderBy = ''): array {
        $table = $this->sanitizeTableName($table);
        $cols = implode(', ', array_map(fn($c) => $c === '*' ? '*' : "`$c`", $columns));
        
        $sql = "SELECT $cols FROM `$table`";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um registro por ID
     * 
     * @param string $table Nome da tabela
     * @param int $id ID do registro
     * @return array|null
     */
    public function find(string $table, int $id): ?array {
        $table = $this->sanitizeTableName($table);
        $stmt = $this->pdo->prepare("SELECT * FROM `$table` WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca um registro por condição customizada
     * 
     * @param string $table Nome da tabela
     * @param array $where Condições ['column' => 'value']
     * @return array|null
     */
    public function findWhere(string $table, array $where): ?array {
        $table = $this->sanitizeTableName($table);
        $conditions = implode(' AND ', array_map(fn($k) => "`$k` = :$k", array_keys($where)));
        
        $stmt = $this->pdo->prepare("SELECT * FROM `$table` WHERE $conditions LIMIT 1");
        $stmt->execute($where);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Retorna múltiplos registros com condições
     * 
     * @param string $table Nome da tabela
     * @param array $where Condições ['column' => 'value']
     * @param string $orderBy Ordenação
     * @param int|null $limit Limite de registros
     * @return array
     */
    public function where(string $table, array $where, string $orderBy = '', ?int $limit = null): array {
        $table = $this->sanitizeTableName($table);
        $conditions = implode(' AND ', array_map(fn($k) => "`$k` = :$k", array_keys($where)));
        
        $sql = "SELECT * FROM `$table` WHERE $conditions";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($where);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insere um novo registro
     * 
     * @param string $table Nome da tabela
     * @param array $data Dados a inserir ['column' => 'value']
     * @return int|false ID inserido ou false em caso de erro
     */
    public function insert(string $table, array $data) {
        $table = $this->sanitizeTableName($table);
        $columns = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $placeholders = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
        
        $stmt = $this->pdo->prepare("INSERT INTO `$table` ($columns) VALUES ($placeholders)");
        
        if ($stmt->execute($data)) {
            return (int) $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Atualiza um registro por ID
     * 
     * @param string $table Nome da tabela
     * @param int $id ID do registro
     * @param array $data Dados a atualizar
     * @return bool
     */
    public function update(string $table, int $id, array $data): bool {
        $table = $this->sanitizeTableName($table);
        $fields = implode(', ', array_map(fn($k) => "`$k` = :$k", array_keys($data)));
        $data['id'] = $id;
        
        $stmt = $this->pdo->prepare("UPDATE `$table` SET $fields WHERE id = :id");
        return $stmt->execute($data);
    }

    /**
     * Atualiza registros com condição customizada
     * 
     * @param string $table Nome da tabela
     * @param array $where Condições
     * @param array $data Dados a atualizar
     * @return bool
     */
    public function updateWhere(string $table, array $where, array $data): bool {
        $table = $this->sanitizeTableName($table);
        $setFields = implode(', ', array_map(fn($k) => "`$k` = :set_$k", array_keys($data)));
        $whereFields = implode(' AND ', array_map(fn($k) => "`$k` = :where_$k", array_keys($where)));
        
        // Prefixo para evitar conflito de nomes
        $params = [];
        foreach ($data as $k => $v) {
            $params["set_$k"] = $v;
        }
        foreach ($where as $k => $v) {
            $params["where_$k"] = $v;
        }
        
        $stmt = $this->pdo->prepare("UPDATE `$table` SET $setFields WHERE $whereFields");
        return $stmt->execute($params);
    }

    /**
     * Deleta um registro por ID
     * 
     * @param string $table Nome da tabela
     * @param int $id ID do registro
     * @return bool
     */
    public function delete(string $table, int $id): bool {
        $table = $this->sanitizeTableName($table);
        $stmt = $this->pdo->prepare("DELETE FROM `$table` WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Deleta registros com condição
     * 
     * @param string $table Nome da tabela
     * @param array $where Condições
     * @return bool
     */
    public function deleteWhere(string $table, array $where): bool {
        $table = $this->sanitizeTableName($table);
        $conditions = implode(' AND ', array_map(fn($k) => "`$k` = :$k", array_keys($where)));
        
        $stmt = $this->pdo->prepare("DELETE FROM `$table` WHERE $conditions");
        return $stmt->execute($where);
    }

    /**
     * Busca com LIKE
     * 
     * @param string $table Nome da tabela
     * @param string $column Coluna para buscar
     * @param string $term Termo de busca
     * @return array
     */
    public function search(string $table, string $column, string $term): array {
        $table = $this->sanitizeTableName($table);
        $column = $this->sanitizeColumnName($column);
        
        $stmt = $this->pdo->prepare("SELECT * FROM `$table` WHERE `$column` LIKE :term");
        $stmt->execute(['term' => "%$term%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Conta registros
     * 
     * @param string $table Nome da tabela
     * @param array $where Condições opcionais
     * @return int
     */
    public function count(string $table, array $where = []): int {
        $table = $this->sanitizeTableName($table);
        
        if (empty($where)) {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM `$table`");
            return (int) $stmt->fetchColumn();
        }
        
        $conditions = implode(' AND ', array_map(fn($k) => "`$k` = :$k", array_keys($where)));
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE $conditions");
        $stmt->execute($where);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Verifica se registro existe
     * 
     * @param string $table Nome da tabela
     * @param array $where Condições
     * @return bool
     */
    public function exists(string $table, array $where): bool {
        return $this->count($table, $where) > 0;
    }

    /**
     * Executa query customizada com prepared statement
     * 
     * @param string $sql SQL query
     * @param array $params Parâmetros
     * @return \PDOStatement
     */
    public function query(string $sql, array $params = []): \PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Inicia transação
     */
    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirma transação
     */
    public function commit(): bool {
        return $this->pdo->commit();
    }

    /**
     * Reverte transação
     */
    public function rollback(): bool {
        return $this->pdo->rollBack();
    }

    /**
     * Sanitiza nome de tabela
     */
    private function sanitizeTableName(string $table): string {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    }

    /**
     * Sanitiza nome de coluna
     */
    private function sanitizeColumnName(string $column): string {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $column);
    }

    /**
     * Retorna instância PDO para uso avançado
     */
    public function getPdo(): PDO {
        return $this->pdo;
    }
}
