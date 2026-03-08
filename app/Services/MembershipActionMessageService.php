<?php

namespace App\Services;

class MembershipActionMessageService
{
    public function requestNotFoundOrProcessed(): array
    {
        return [
            'type' => 'danger',
            'message' => 'Solicitação não encontrada ou já processada.',
        ];
    }

    public function requestNotAvailableForComplementation(): array
    {
        return [
            'type' => 'danger',
            'message' => 'Solicitação não encontrada ou indisponível para complementação.',
        ];
    }

    public function missingComplementationNote(): array
    {
        return [
            'type' => 'danger',
            'message' => 'Informe a orientação para complementação documental.',
        ];
    }

    public function invalidAssociativeStatus(): array
    {
        return [
            'type' => 'danger',
            'message' => 'Status associativo inválido para aprovação da solicitação.',
        ];
    }

    public function approvalResult(bool $emailEnviado): array
    {
        return [
            'type' => $emailEnviado ? 'success' : 'warning',
            'message' => $emailEnviado
                ? 'Solicitação aprovada. O vínculo associativo foi reconhecido e o e-mail foi enviado ao solicitante.'
                : 'Solicitação aprovada, mas o e-mail de notificação não pôde ser enviado.',
        ];
    }

    public function approvalFailure(): array
    {
        return [
            'type' => 'danger',
            'message' => 'Não foi possível aprovar a solicitação.',
        ];
    }

    public function rejectionResult(bool $emailEnviado): array
    {
        return [
            'type' => $emailEnviado ? 'success' : 'warning',
            'message' => $emailEnviado
                ? 'Solicitação marcada como rejeitada e e-mail de notificação enviado ao solicitante.'
                : 'Solicitação marcada como rejeitada, mas o e-mail de notificação não pôde ser enviado.',
        ];
    }

    public function complementationResult(bool $emailEnviado): array
    {
        return [
            'type' => $emailEnviado ? 'success' : 'warning',
            'message' => $emailEnviado
                ? 'Solicitação marcada como pendente de complementação documental e e-mail enviado ao solicitante.'
                : 'Solicitação marcada como pendente de complementação documental, mas o e-mail não pôde ser enviado.',
        ];
    }
}
