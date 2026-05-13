<?php

namespace App\Services;

class MembershipStatusStateMachine
{
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_COMPLEMENTACAO = 'complementacao';
    public const STATUS_APROVADA = 'aprovada';
    public const STATUS_REJEITADA = 'rejeitada';

    /**
     * @var array<string,array<int,string>>
     */
    private const TRANSITIONS = [
        self::STATUS_PENDENTE => [
            self::STATUS_APROVADA,
            self::STATUS_REJEITADA,
            self::STATUS_COMPLEMENTACAO,
        ],
        self::STATUS_COMPLEMENTACAO => [
            self::STATUS_APROVADA,
            self::STATUS_REJEITADA,
            self::STATUS_COMPLEMENTACAO,
        ],
        self::STATUS_APROVADA => [],
        self::STATUS_REJEITADA => [],
    ];

    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        $from = trim($fromStatus);
        $to = trim($toStatus);

        if ($from === '' || $to === '') {
            return false;
        }

        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public function assertTransition(string $fromStatus, string $toStatus, string $action): void
    {
        if ($this->canTransition($fromStatus, $toStatus)) {
            return;
        }

        throw new \DomainException(sprintf(
            'Transição de status inválida para %s: %s -> %s',
            $action,
            $fromStatus,
            $toStatus
        ));
    }
}

