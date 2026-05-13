<?php

namespace Tests\Services;

use App\Services\MembershipAssociativeStatusStateMachine;
use PHPUnit\Framework\TestCase;

class MembershipAssociativeStatusStateMachineTest extends TestCase
{
    public function testAllowsPromotionFromProvisorio(): void
    {
        $machine = new MembershipAssociativeStatusStateMachine();

        $this->assertTrue($machine->canTransition('provisorio', 'efetivo'));
        $this->assertTrue($machine->canTransition('provisorio', 'fundador'));
        $this->assertTrue($machine->canTransition('aluno', 'efetivo'));
    }

    public function testBlocksDowngradeTransitions(): void
    {
        $machine = new MembershipAssociativeStatusStateMachine();

        $this->assertFalse($machine->canTransition('efetivo', 'aluno'));
        $this->assertFalse($machine->canTransition('fundador', 'provisorio'));
        $this->assertFalse($machine->canTransition('honorario', 'efetivo'));
    }

    public function testAssertTransitionThrowsForInvalidAssociativeTransition(): void
    {
        $machine = new MembershipAssociativeStatusStateMachine();

        $this->expectException(\DomainException::class);
        $machine->assertTransition('efetivo', 'aluno');
    }
}

