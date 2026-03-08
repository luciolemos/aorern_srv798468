<?php

namespace Tests\Services;

use App\Core\Request;
use App\Services\MembershipActionMessageService;
use App\Services\MembershipModerationRequestValidator;
use PHPUnit\Framework\TestCase;

class MembershipModerationRequestValidatorTest extends TestCase
{
    protected function tearDown(): void
    {
        $_POST = [];
        $_GET = [];
        $_SERVER = [];
    }

    public function testValidateApprovalRejectsInvalidAssociativeStatus(): void
    {
        $_POST = [
            'status_associativo' => 'invalido',
            'observacoes_admin' => 'ok',
        ];

        $validator = new MembershipModerationRequestValidator();
        $messages = new MembershipActionMessageService();
        $result = $validator->validateApproval(new Request(), $messages);

        $this->assertFalse($result['ok']);
        $this->assertSame('danger', $result['toast']['type']);
        $this->assertStringContainsString('Status associativo inválido', $result['toast']['message']);
    }

    public function testValidateApprovalReturnsSanitizedPayload(): void
    {
        $_POST = [
            'status_associativo' => 'efetivo',
            'observacoes_admin' => '  observação interna  ',
        ];

        $validator = new MembershipModerationRequestValidator();
        $messages = new MembershipActionMessageService();
        $result = $validator->validateApproval(new Request(), $messages);

        $this->assertTrue($result['ok']);
        $this->assertSame('efetivo', $result['data']['status_associativo']);
        $this->assertSame('observação interna', $result['data']['observacoes_admin']);
    }

    public function testValidateComplementationRequiresNote(): void
    {
        $_POST = ['observacoes_admin' => '   '];

        $validator = new MembershipModerationRequestValidator();
        $messages = new MembershipActionMessageService();
        $result = $validator->validateComplementation(new Request(), $messages);

        $this->assertFalse($result['ok']);
        $this->assertSame('danger', $result['toast']['type']);
    }

    public function testValidateRejectionAllowsEmptyNote(): void
    {
        $_POST = ['observacoes_admin' => '   '];

        $validator = new MembershipModerationRequestValidator();
        $result = $validator->validateRejection(new Request());

        $this->assertTrue($result['ok']);
        $this->assertNull($result['data']['observacoes_admin']);
    }
}

