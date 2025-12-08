<?php

namespace App\Helpers;

use PDO;
use PDOStatement;

class Paginator
{
    public static function paginate(
        PDO $connection,
        string $select,
        string $from,
        string $where = '',
        string $orderBy = '',
        array $params = [],
        int $page = 1,
        int $perPage = 15
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $whereClause = $where ? " WHERE {$where}" : '';

        $countSql = "SELECT COUNT(*) AS total {$from}{$whereClause}";
        $countStmt = $connection->prepare($countSql);
        self::bindParams($countStmt, $params);
        $countStmt->execute();
        $total = (int) ($countStmt->fetchColumn() ?? 0);

        $lastPage = max(1, (int) ceil($total / $perPage));
        if ($page > $lastPage) {
            $page = $lastPage;
        }
        $offset = ($page - 1) * $perPage;

        $dataSql = "SELECT {$select} {$from}{$whereClause}";
        if ($orderBy) {
            $dataSql .= " ORDER BY {$orderBy}";
        }
        $dataSql .= " LIMIT {$perPage} OFFSET {$offset}";

        $dataStmt = $connection->prepare($dataSql);
        self::bindParams($dataStmt, $params);
        $dataStmt->execute();
        $items = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $items,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
                'from' => $total ? $offset + 1 : 0,
                'to' => $total ? $offset + count($items) : 0,
                'has_prev' => $page > 1,
                'has_next' => $page < $lastPage,
            ],
        ];
    }

    private static function bindParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            if (is_int($key)) {
                // Positional parameters
                $stmt->bindValue($key + 1, $value);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
    }
}

