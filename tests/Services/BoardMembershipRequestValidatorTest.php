<?php

namespace Tests\Services;

use App\Core\Request;
use App\Models\BoardMembershipModel;
use App\Models\FuncaoModel;
use App\Models\PessoalModel;
use App\Services\BoardMembershipRequestValidator;
use PHPUnit\Framework\TestCase;

class BoardMembershipRequestValidatorTest extends TestCase
{
    protected function tearDown(): void
    {
        $_POST = [];
        $_GET = [];
        $_SERVER = [];
    }

    public function testValidateReturnsNormalizedPayloadWhenInputIsValid(): void
    {
        $_POST = [
            'funcao_id' => '10',
            'term_id' => '3',
            'grupo' => '  Diretoria Executiva ',
            'ordem' => '-2',
            'is_active' => '1',
            'observacoes' => '  ok ',
            'access_role' => '',
        ];

        $validator = new BoardMembershipRequestValidator();
        $memberships = new FakeBoardMembershipModelForValidator();
        $funcoes = new FakeFuncaoModelForValidator([
            10 => ['id' => 10, 'nome' => 'Diretor Administrativo'],
        ]);
        $pessoal = new FakePessoalModelForValidator();

        $result = $validator->validate(new Request(), $memberships, $funcoes, $pessoal);

        $this->assertTrue($result['ok']);
        $this->assertSame(10, $result['data']['funcao_id']);
        $this->assertSame(3, $result['data']['term_id']);
        $this->assertSame('Diretor Administrativo', $result['data']['cargo']);
        $this->assertSame('Diretoria Executiva', $result['data']['grupo']);
        $this->assertSame(0, $result['data']['ordem']);
        $this->assertSame(1, $result['data']['is_active']);
        $this->assertNull($result['data']['access_role']);
        $this->assertSame('ok', $result['data']['observacoes']);
    }

    public function testValidateRejectsAdministrativeRoleWithoutAssociate(): void
    {
        $_POST = [
            'funcao_id' => '10',
            'access_role' => 'gerente',
        ];

        $validator = new BoardMembershipRequestValidator();
        $result = $validator->validate(
            new Request(),
            new FakeBoardMembershipModelForValidator(),
            new FakeFuncaoModelForValidator([10 => ['id' => 10, 'nome' => 'Presidente']]),
            new FakePessoalModelForValidator()
        );

        $this->assertFalse($result['ok']);
        $this->assertSame('danger', $result['toast']['type']);
        $this->assertStringContainsString('Selecione um associado', $result['toast']['message']);
    }

    public function testValidateRejectsAssociateWithoutCredentialForAdministrativeRole(): void
    {
        $_POST = [
            'funcao_id' => '10',
            'pessoal_id' => '7',
            'access_role' => 'operador',
        ];

        $validator = new BoardMembershipRequestValidator();
        $pessoal = new FakePessoalModelForValidator([
            7 => ['id' => 7, 'nome' => 'Associado Teste', 'user_id' => null],
        ]);
        $result = $validator->validate(
            new Request(),
            new FakeBoardMembershipModelForValidator(),
            new FakeFuncaoModelForValidator([10 => ['id' => 10, 'nome' => 'Diretor']]),
            $pessoal
        );

        $this->assertFalse($result['ok']);
        $this->assertSame('danger', $result['toast']['type']);
        $this->assertStringContainsString('ainda não possui credencial', $result['toast']['message']);
    }

    public function testValidateRejectsDuplicatedFunctionWithinTerm(): void
    {
        $_POST = [
            'funcao_id' => '10',
            'term_id' => '4',
        ];

        $validator = new BoardMembershipRequestValidator();
        $memberships = new FakeBoardMembershipModelForValidator();
        $memberships->hasDuplicate = true;

        $result = $validator->validate(
            new Request(),
            $memberships,
            new FakeFuncaoModelForValidator([10 => ['id' => 10, 'nome' => 'Diretor']]),
            new FakePessoalModelForValidator()
        );

        $this->assertFalse($result['ok']);
        $this->assertSame('danger', $result['toast']['type']);
        $this->assertStringContainsString('já está vinculada ao mandato', $result['toast']['message']);
    }
}

class FakeBoardMembershipModelForValidator extends BoardMembershipModel
{
    public bool $hasDuplicate = false;

    public function __construct()
    {
    }

    public function existeFuncaoNoMandato(int $termId, int $funcaoId, ?int $excludeId = null): bool
    {
        return $this->hasDuplicate;
    }
}

class FakeFuncaoModelForValidator extends FuncaoModel
{
    /**
     * @var array<int,array<string,mixed>>
     */
    private array $records;

    /**
     * @param array<int,array<string,mixed>> $records
     */
    public function __construct(array $records = [])
    {
        $this->records = $records;
    }

    public function buscar(int $id): ?array
    {
        return $this->records[$id] ?? null;
    }
}

class FakePessoalModelForValidator extends PessoalModel
{
    /**
     * @var array<int,array<string,mixed>>
     */
    private array $records;

    /**
     * @param array<int,array<string,mixed>> $records
     */
    public function __construct(array $records = [])
    {
        $this->records = $records;
    }

    public function buscar(int $id): ?array
    {
        return $this->records[$id] ?? null;
    }
}
