<?php

namespace Tests\Services;

use App\Models\BoardMembershipModel;
use App\Models\PessoalModel;
use App\Models\User;
use App\Services\BoardMembershipWorkflowService;
use PHPUnit\Framework\TestCase;

class BoardMembershipWorkflowServiceTest extends TestCase
{
    public function testCreatePromotesAssociateToHighestAdministrativeRole(): void
    {
        $memberships = new FakeBoardMembershipModelForWorkflow();
        $pessoal = new FakePessoalModelForWorkflow([
            5 => ['id' => 5, 'user_id' => 91],
        ]);
        $memberships->rolesByPessoal = [
            5 => ['operador', 'gerente'],
        ];
        $users = new FakeUserModelForWorkflow();

        $service = new BoardMembershipWorkflowService($memberships, $pessoal, $users);
        $service->create([
            'pessoal_id' => 5,
            'funcao_id' => 10,
            'cargo' => 'Presidente',
        ]);

        $this->assertSame(5, $memberships->lastRolesLookupPessoalId);
        $this->assertCount(1, $users->updates);
        $this->assertSame(91, $users->updates[0]['id']);
        $this->assertSame('gerente', $users->updates[0]['data']['role']);
        $this->assertSame(1, $users->updates[0]['data']['ativo']);
        $this->assertSame('ativo', $users->updates[0]['data']['status']);
    }

    public function testUpdateResyncsPreviousAndCurrentAssociates(): void
    {
        $memberships = new FakeBoardMembershipModelForWorkflow();
        $memberships->recordsById = [
            11 => ['id' => 11, 'pessoal_id' => 5],
        ];
        $memberships->rolesByPessoal = [
            5 => [],
            6 => ['operador'],
        ];

        $pessoal = new FakePessoalModelForWorkflow([
            5 => ['id' => 5, 'user_id' => 50],
            6 => ['id' => 6, 'user_id' => 60],
        ]);
        $users = new FakeUserModelForWorkflow();

        $service = new BoardMembershipWorkflowService($memberships, $pessoal, $users);
        $service->update(11, [
            'pessoal_id' => 6,
            'funcao_id' => 20,
            'cargo' => 'Diretor',
            'is_active' => 1,
        ]);

        $this->assertSame(11, $memberships->lastUpdateId);
        $this->assertSame(6, $memberships->lastUpdatePayload['pessoal_id']);
        $this->assertCount(2, $users->updates);
        $this->assertSame(50, $users->updates[0]['id']);
        $this->assertSame('usuario', $users->updates[0]['data']['role']);
        $this->assertSame(60, $users->updates[1]['id']);
        $this->assertSame('operador', $users->updates[1]['data']['role']);
    }

    public function testDeleteDoesNotUpdateUserWhenAssociateHasNoCredential(): void
    {
        $memberships = new FakeBoardMembershipModelForWorkflow();
        $pessoal = new FakePessoalModelForWorkflow([
            7 => ['id' => 7, 'user_id' => null],
        ]);
        $users = new FakeUserModelForWorkflow();

        $service = new BoardMembershipWorkflowService($memberships, $pessoal, $users);
        $service->delete([
            'id' => 44,
            'pessoal_id' => 7,
        ]);

        $this->assertSame(44, $memberships->lastDeleteId);
        $this->assertCount(0, $users->updates);
    }
}

class FakeBoardMembershipModelForWorkflow extends BoardMembershipModel
{
    /**
     * @var array<int,array<string,mixed>>
     */
    public array $recordsById = [];

    /**
     * @var array<int,array<int,string>>
     */
    public array $rolesByPessoal = [];

    public ?int $lastUpdateId = null;

    /**
     * @var array<string,mixed>
     */
    public array $lastUpdatePayload = [];

    public ?int $lastDeleteId = null;
    public ?int $lastRolesLookupPessoalId = null;

    public function __construct()
    {
    }

    public function salvar(array $dados): bool
    {
        return true;
    }

    public function buscar(int $id): ?array
    {
        return $this->recordsById[$id] ?? null;
    }

    public function atualizar(int $id, array $dados): bool
    {
        $this->lastUpdateId = $id;
        $this->lastUpdatePayload = $dados;
        return true;
    }

    public function deletar(int $id): bool
    {
        $this->lastDeleteId = $id;
        return true;
    }

    public function listarPerfisAtivosPorPessoal(int $pessoalId): array
    {
        $this->lastRolesLookupPessoalId = $pessoalId;
        return $this->rolesByPessoal[$pessoalId] ?? [];
    }
}

class FakePessoalModelForWorkflow extends PessoalModel
{
    /**
     * @var array<int,array<string,mixed>>
     */
    private array $recordsById;

    /**
     * @param array<int,array<string,mixed>> $recordsById
     */
    public function __construct(array $recordsById)
    {
        $this->recordsById = $recordsById;
    }

    public function buscar(int $id): ?array
    {
        return $this->recordsById[$id] ?? null;
    }
}

class FakeUserModelForWorkflow extends User
{
    /**
     * @var array<int,array{id:int,data:array<string,mixed>}>
     */
    public array $updates = [];

    public function __construct()
    {
    }

    public function atualizar(int $id, array $dados): bool
    {
        $this->updates[] = [
            'id' => $id,
            'data' => $dados,
        ];
        return true;
    }
}
