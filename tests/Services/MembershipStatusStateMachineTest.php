<?php

namespace Tests\Services;

use App\Services\MembershipStatusStateMachine;
use PHPUnit\Framework\TestCase;

class MembershipStatusStateMachineTest extends TestCase
{
    public function testAllowsExpectedTransitions(): void
    {
        $machine = new MembershipStatusStateMachine();

        $this->assertTrue($machine->canTransition('pendente', 'aprovada'));
        $this->assertTrue($machine->canTransition('pendente', 'rejeitada'));
        $this->assertTrue($machine->canTransition('pendente', 'complementacao'));
        $this->assertTrue($machine->canTransition('complementacao', 'aprovada'));
    }

    public function testBlocksTransitionsFromFinalStates(): void
    {
        $machine = new MembershipStatusStateMachine();

        $this->assertFalse($machine->canTransition('aprovada', 'rejeitada'));
        $this->assertFalse($machine->canTransition('rejeitada', 'aprovada'));
        $this->assertFalse($machine->canTransition('aprovada', 'complementacao'));
    }

    public function testAssertTransitionThrowsForInvalidTransition(): void
    {
        $machine = new MembershipStatusStateMachine();

        $this->expectException(\DomainException::class);
        $machine->assertTransition('aprovada', 'rejeitada', 'rejeição');
    }
}

