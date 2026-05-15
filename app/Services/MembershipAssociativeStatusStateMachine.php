<?php

namespace App\Services;

class MembershipAssociativeStatusStateMachine
{
    public const PROVISORIO = 'provisorio';
    public const EFETIVO = 'efetivo';
    public const HONORARIO = 'honorario';
    public const FUNDADOR = 'fundador';
    public const BENEMERITO = 'benemerito';
    public const VETERANO = 'veterano';
    public const ALUNO = 'aluno';

    /**
     * @var array<string,array<int,string>>
     */
    private const TRANSITIONS = [
        self::PROVISORIO => [
            self::PROVISORIO,
            self::EFETIVO,
            self::HONORARIO,
            self::FUNDADOR,
            self::BENEMERITO,
            self::VETERANO,
            self::ALUNO,
        ],
        self::EFETIVO => [self::EFETIVO, self::HONORARIO, self::FUNDADOR, self::BENEMERITO, self::VETERANO],
        self::HONORARIO => [self::HONORARIO],
        self::FUNDADOR => [self::FUNDADOR],
        self::BENEMERITO => [self::BENEMERITO],
        self::VETERANO => [self::VETERANO],
        self::ALUNO => [self::ALUNO, self::EFETIVO],
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

    public function assertTransition(string $fromStatus, string $toStatus): void
    {
        if ($this->canTransition($fromStatus, $toStatus)) {
            return;
        }

        throw new \DomainException(sprintf(
            'Transição de status associativo inválida: %s -> %s',
            $fromStatus,
            $toStatus
        ));
    }
}

