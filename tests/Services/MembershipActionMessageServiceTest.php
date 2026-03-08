<?php

namespace Tests\Services;

use App\Services\MembershipActionMessageService;
use PHPUnit\Framework\TestCase;

class MembershipActionMessageServiceTest extends TestCase
{
    public function testApprovalMessagesRespectEmailDeliveryStatus(): void
    {
        $service = new MembershipActionMessageService();

        $success = $service->approvalResult(true);
        $warning = $service->approvalResult(false);

        $this->assertSame('success', $success['type']);
        $this->assertStringContainsString('foi enviado', $success['message']);
        $this->assertSame('warning', $warning['type']);
        $this->assertStringContainsString('não pôde ser enviado', $warning['message']);
    }

    public function testComplementationMissingNoteMessageIsDanger(): void
    {
        $service = new MembershipActionMessageService();
        $toast = $service->missingComplementationNote();

        $this->assertSame('danger', $toast['type']);
        $this->assertStringContainsString('Informe a orientação', $toast['message']);
    }

    public function testRequestNotFoundMessageIsDanger(): void
    {
        $service = new MembershipActionMessageService();
        $toast = $service->requestNotFoundOrProcessed();

        $this->assertSame('danger', $toast['type']);
        $this->assertStringContainsString('não encontrada', $toast['message']);
    }

    public function testInvalidAssociativeStatusMessageIsDanger(): void
    {
        $service = new MembershipActionMessageService();
        $toast = $service->invalidAssociativeStatus();

        $this->assertSame('danger', $toast['type']);
        $this->assertStringContainsString('Status associativo inválido', $toast['message']);
    }
}
