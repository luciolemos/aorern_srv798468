<?php

namespace Tests\Services;

use App\Models\FuncaoModel;
use App\Models\MembershipApplicationModel;
use App\Models\PessoalModel;
use App\Services\MembershipApplicationWorkflowService;
use PDO;
use PHPUnit\Framework\TestCase;

class MembershipApplicationWorkflowServiceTest extends TestCase
{
    public function testApproveUsesExistingAssociateAndUpdatesApplication(): void
    {
        $applications = new FakeMembershipApplicationModel();
        $pessoal = new FakePessoalModel();
        $funcoes = new FakeFuncaoModel();
        $db = new PDO('sqlite::memory:');

        $pessoal->cpfRecord = [
            'id' => 42,
            'user_id' => 10,
            'foto' => null,
        ];

        $solicitacao = [
            'cpf' => '12345678901',
            'avatar' => 'uploads/users/avatar.png',
            'telefone' => '(84) 99999-0000',
            'nome_completo' => 'Associado Teste',
            'data_nascimento' => '1990-01-01',
            'ano_npor' => '2020',
        ];

        $service = new MembershipApplicationWorkflowService($applications, $pessoal, $funcoes, $db);
        $result = $service->approve(99, $solicitacao, 'efetivo', 'ok');

        $this->assertSame(42, $result['pessoal_id']);
        $this->assertSame(10, $result['user_id']);
        $this->assertSame(42, $pessoal->updatedStatusId);
        $this->assertSame('efetivo', $pessoal->updatedStatusValue);
        $this->assertSame(42, $pessoal->updatedFotoId);
        $this->assertSame('uploads/users/avatar.png', $pessoal->updatedFotoPath);
        $this->assertSame(99, $applications->lastUpdateId);
        $this->assertSame('aprovada', $applications->lastUpdateData['status'] ?? null);
        $this->assertSame(42, $applications->lastUpdateData['pessoal_id'] ?? null);
    }

    public function testRejectSetsRejeitadaStatusAndOptionalNote(): void
    {
        $applications = new FakeMembershipApplicationModel();
        $service = new MembershipApplicationWorkflowService(
            $applications,
            new FakePessoalModel(),
            new FakeFuncaoModel(),
            new PDO('sqlite::memory:')
        );

        $service->reject(7, '   ');

        $this->assertSame(7, $applications->lastUpdateId);
        $this->assertSame('rejeitada', $applications->lastUpdateData['status'] ?? null);
        $this->assertArrayHasKey('observacoes_admin', $applications->lastUpdateData);
        $this->assertArrayHasKey('aprovado_em', $applications->lastUpdateData);
        $this->assertNull($applications->lastUpdateData['observacoes_admin']);
        $this->assertNull($applications->lastUpdateData['aprovado_em']);
        $this->assertNotEmpty($applications->lastUpdateData['rejeitado_em'] ?? '');
    }

    public function testRequestComplementationRequiresNote(): void
    {
        $service = new MembershipApplicationWorkflowService(
            new FakeMembershipApplicationModel(),
            new FakePessoalModel(),
            new FakeFuncaoModel(),
            new PDO('sqlite::memory:')
        );

        $this->expectException(\InvalidArgumentException::class);
        $service->requestComplementation(5, '   ');
    }

    public function testRequestComplementationUpdatesStatus(): void
    {
        $applications = new FakeMembershipApplicationModel();
        $service = new MembershipApplicationWorkflowService(
            $applications,
            new FakePessoalModel(),
            new FakeFuncaoModel(),
            new PDO('sqlite::memory:')
        );

        $service->requestComplementation(8, '  Reenviar RG legível ');

        $this->assertSame(8, $applications->lastUpdateId);
        $this->assertSame('complementacao', $applications->lastUpdateData['status'] ?? null);
        $this->assertSame('Reenviar RG legível', $applications->lastUpdateData['observacoes_admin'] ?? null);
        $this->assertArrayHasKey('aprovado_em', $applications->lastUpdateData);
        $this->assertArrayHasKey('rejeitado_em', $applications->lastUpdateData);
        $this->assertNull($applications->lastUpdateData['aprovado_em']);
        $this->assertNull($applications->lastUpdateData['rejeitado_em']);
    }
}

class FakeMembershipApplicationModel extends MembershipApplicationModel
{
    public ?int $lastUpdateId = null;
    public array $lastUpdateData = [];

    public function __construct()
    {
    }

    public function atualizar(int $id, array $dados): bool
    {
        $this->lastUpdateId = $id;
        $this->lastUpdateData = $dados;
        return true;
    }
}

class FakePessoalModel extends PessoalModel
{
    public ?array $cpfRecord = null;
    public ?int $updatedStatusId = null;
    public ?string $updatedStatusValue = null;
    public ?int $updatedFotoId = null;
    public ?string $updatedFotoPath = null;

    public function __construct()
    {
    }

    public function buscarPorCpf(string $cpf): ?array
    {
        return $this->cpfRecord;
    }

    public function atualizarStatusAssociativo(int $id, string $statusAssociativo): bool
    {
        $this->updatedStatusId = $id;
        $this->updatedStatusValue = $statusAssociativo;
        return true;
    }

    public function atualizarFoto(int $id, ?string $foto): bool
    {
        $this->updatedFotoId = $id;
        $this->updatedFotoPath = $foto;
        return true;
    }
}

class FakeFuncaoModel extends FuncaoModel
{
    public function __construct()
    {
    }
}
