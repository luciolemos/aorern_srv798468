<?php

namespace App\Database\Migrations;

use App\Database\Migration;
use PDO;

class Migration_2026_03_05_000014_normalize_funcoes_staff_id_pattern extends Migration
{
    public function up(): void
    {
        if (!$this->tableExists('funcoes') || !$this->columnExists('funcoes', 'staff_id')) {
            return;
        }

        $stmt = $this->db->query("SELECT id, staff_id FROM funcoes ORDER BY id ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $usedNumbers = [];
        foreach ($rows as $row) {
            $staffId = (string) ($row['staff_id'] ?? '');
            if (preg_match('/^FUNC-AORE-(\d+)$/', $staffId, $matches) === 1) {
                $usedNumbers[(int) $matches[1]] = true;
            }
        }

        $nextNumber = empty($usedNumbers) ? 1 : (max(array_keys($usedNumbers)) + 1);
        $update = $this->db->prepare("UPDATE funcoes SET staff_id = :staff_id WHERE id = :id");

        foreach ($rows as $row) {
            $staffId = (string) ($row['staff_id'] ?? '');
            if (preg_match('/^FUNC-AORE-(\d+)$/', $staffId) === 1) {
                continue;
            }

            while (isset($usedNumbers[$nextNumber])) {
                $nextNumber++;
            }

            $newStaffId = 'FUNC-AORE-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            $update->execute([
                'staff_id' => $newStaffId,
                'id' => (int) $row['id'],
            ]);

            $usedNumbers[$nextNumber] = true;
            $nextNumber++;
        }
    }

    public function down(): void
    {
        // Sem rollback automático: a operação substitui valores legados por padrão institucional.
    }
}
