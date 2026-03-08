<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$pdo = Database::connect();

$pdo->beginTransaction();

try {
    $admin = $pdo->prepare('SELECT id, username, email FROM users WHERE username = :username LIMIT 1');
    $admin->execute([':username' => 'admin']);
    $adminRow = $admin->fetch(PDO::FETCH_ASSOC);

    if (!$adminRow) {
        throw new RuntimeException('Usuário admin não encontrado.');
    }

    $adminId = (int) $adminRow['id'];

    $pdo->prepare("UPDATE users SET email = 'admin@example.com', role = 'admin', ativo = 1, status = 'ativo' WHERE id = :id")
        ->execute([':id' => $adminId]);

    $pdo->exec('DELETE FROM membership_applications');
    $pdo->exec('DELETE FROM board_memberships');
    $pdo->exec("DELETE FROM users WHERE id <> {$adminId}");
    $pdo->exec('DELETE FROM pessoal');

    $pdo->commit();

    echo "Base saneada com sucesso.\n";
    echo "- admin preservado como admin@example.com\n";
    echo "- demais users removidos\n";
    echo "- membership_applications limpo\n";
    echo "- board_memberships limpo\n";
    echo "- pessoal limpo\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, 'Falha no reset: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
