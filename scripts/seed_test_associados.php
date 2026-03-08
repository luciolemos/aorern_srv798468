<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$pdo = Database::connect();

$names = [
    'Carlos Eduardo Nascimento',
    'Henrique de Medeiros Paiva',
    'Marcos Vinicius Albuquerque',
    'Joao Felipe Montenegro',
    'Andre Luiz Furtado',
    'Roberto Cesar de Brito',
    'Thiago de Araujo Lima',
    'Fernando Tarcisio Peixoto',
    'Gustavo Henrique Bezerra',
    'Leonardo Campos Dantas',
    'Mateus Augusto Tavares',
    'Ricardo Alexandre Nogueira',
    'Paulo Sergio Cavalcanti',
    'Daniel Rocha de Souza',
    'Bruno Henrique de Melo',
    'Alexandre Freire de Lima',
    'Rafael Teixeira Marinho',
    'Victor Hugo Fernandes',
    'Luciano Cesar Barreto',
    'Eduardo Jorge de Queiroz',
];

$cities = ['Natal', 'Parnamirim', 'Macaiba', 'Ceara-Mirim', 'Sao Goncalo do Amarante', 'Mossoro'];
$assocStatuses = ['provisorio'];
$militaryStatuses = ['Oficial R/2', 'Reservista', 'Ex-aluno do NPOR'];

$pdo->beginTransaction();

try {
    $funcaoId = (int) $pdo->query("SELECT id FROM funcoes WHERE nome='Associado AORE/RN' LIMIT 1")->fetchColumn();
    if ($funcaoId <= 0) {
        $funcaoLegadaId = (int) $pdo->query("SELECT id FROM funcoes WHERE nome='Associado' LIMIT 1")->fetchColumn();
        if ($funcaoLegadaId > 0) {
            $stmtFunc = $pdo->prepare("UPDATE funcoes SET nome = ?, staff_id = ? WHERE id = ?");
            $stmtFunc->execute(['Associado AORE/RN', 'FUNC-AORE-011', $funcaoLegadaId]);
            $funcaoId = $funcaoLegadaId;
        } else {
            $stmtFunc = $pdo->prepare("INSERT INTO funcoes (staff_id, nome) VALUES (?, ?)");
            $stmtFunc->execute(['FUNC-AORE-011', 'Associado AORE/RN']);
            $funcaoId = (int) $pdo->lastInsertId();
        }
    }

    $pdo->exec("DELETE FROM pessoal");

    $stmt = $pdo->prepare(
        "INSERT INTO pessoal (
            staff_id, nome, cpf, nascimento, telefone, foto,
            funcao_id, obra_id, data_admissao, status, status_associativo, jornada, observacoes, criado_em
        ) VALUES (
            :staff_id, :nome, :cpf, :nascimento, :telefone, :foto,
            :funcao_id, :obra_id, :data_admissao, :status, :status_associativo, :jornada, :observacoes, NOW()
        )"
    );

    $baseDate = date('Ymd');
    foreach ($names as $index => $name) {
        $sequence = $index + 1;
        $staffId = sprintf('ASSOC-%s-%03d', $baseDate, $sequence);
        $cpf = str_pad((string) (31000000000 + $sequence), 11, '0', STR_PAD_LEFT);
        $birthYear = 1968 + ($index % 20);
        $birthMonth = (($index % 12) + 1);
        $birthDay = (($index % 28) + 1);
        $admissionDay = (($index % 28) + 1);
        $city = $cities[$index % count($cities)];
        $assocStatus = $assocStatuses[$index % count($assocStatuses)];
        $militaryStatus = $militaryStatuses[$index % count($militaryStatuses)];

        $stmt->execute([
            ':staff_id' => $staffId,
            ':nome' => $name,
            ':cpf' => $cpf,
            ':nascimento' => sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay),
            ':telefone' => '8499' . str_pad((string) (100000 + $sequence), 6, '0', STR_PAD_LEFT),
            ':foto' => 'assets/images/aore1.png',
            ':funcao_id' => $funcaoId,
            ':obra_id' => null,
            ':data_admissao' => sprintf('2026-03-%02d', $admissionDay),
            ':status' => 'Ativo',
            ':status_associativo' => $assocStatus,
            ':jornada' => null,
            ':observacoes' => sprintf(
                'Registro de teste gerado automaticamente. Cidade-base: %s. Condicao militar de referencia: %s.',
                $city,
                $militaryStatus
            ),
        ]);
    }

    $pdo->commit();
    echo "20 associados de teste gerados com sucesso.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Falha ao gerar associados de teste: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
